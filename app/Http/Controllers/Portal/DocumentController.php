<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ClientDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
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
