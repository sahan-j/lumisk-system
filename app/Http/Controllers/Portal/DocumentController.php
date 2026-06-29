<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ClientDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function upload(Request $request): RedirectResponse
    {
        $client = Auth::guard('client')->user();

        $request->validate([
            'files'       => ['required', 'array', 'min:1', 'max:10'],
            'files.*'     => ['file', 'max:20480'],
            'category'    => ['required', 'in:' . implode(',', ClientDocument::CATEGORIES)],
            'project_id'  => ['nullable', 'integer'],
            'client_note' => ['nullable', 'string', 'max:500'],
        ]);

        // Verify the chosen project belongs to this client.
        $projectId = $request->integer('project_id') ?: null;
        if ($projectId && ! $client->projects()->whereKey($projectId)->exists()) {
            $projectId = null;
        }

        $count = 0;
        foreach ($request->file('files') as $file) {
            $stored = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path   = $file->storeAs('client-documents/' . $client->id, $stored, 'private');

            ClientDocument::create([
                'client_id'         => $client->id,
                'project_id'        => $projectId,
                'uploaded_by'       => 'client',
                'category'          => $request->input('category'),
                'title'             => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename'   => $stored,
                'path'              => $path,
                'mime_type'         => $file->getMimeType() ?: 'application/octet-stream',
                'size'              => $file->getSize(),
                'client_note'       => $request->input('client_note') ?: null,
                'viewed_by_admin'   => false,
            ]);

            $count++;
        }

        $client->increment('unread_documents_count', $count);

        $title = $count === 1 ? 'a document' : "{$count} documents";
        \App\Models\User::all()->each(fn ($admin) => $admin->notify(
            new \App\Notifications\Admin\ClientDocumentUploadedNotification(
                $client,
                $title,
                str_replace('_', ' ', $request->input('category'))
            )
        ));

        return redirect()->route('portal.documents.index')
            ->with('success', "{$count} file(s) uploaded successfully.");
    }

    public function download(ClientDocument $document): StreamedResponse
    {
        $this->authorizeAccess($document);
        $document->update(['viewed_by_client' => true]);

        return Storage::disk('private')->download($document->path, $document->original_filename);
    }

    public function preview(ClientDocument $document)
    {
        $this->authorizeAccess($document);
        $document->update(['viewed_by_client' => true]);

        // Only images and PDFs render inline; everything else downloads.
        if (! $document->is_image && ! $document->is_pdf) {
            return Storage::disk('private')->download($document->path, $document->original_filename);
        }

        return Storage::disk('private')->response($document->path, $document->original_filename, [
            'Content-Type' => $document->mime_type,
        ]);
    }

    /** Client may only touch their own documents, and admin uploads only if visible. */
    private function authorizeAccess(ClientDocument $document): void
    {
        abort_unless($document->client_id === Auth::guard('client')->id(), 403);
        abort_if($document->uploaded_by === 'admin' && ! $document->is_visible_to_client, 403);
    }
}
