<?php

namespace App\Livewire\Portal\Documents;

use App\Models\ClientDocument;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.portal')]
#[Title('My Documents')]
class DocumentsIndex extends Component
{
    use WithFileUploads;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $files = [];
    public string $category = '';
    public ?int $projectId = null;
    public string $clientNote = '';

    /** Admin uploads that were unseen when the page first loaded (for the "New" badge this session). */
    public array $newDocIds = [];

    public function mount(): void
    {
        $client = Auth::guard('client')->user();

        $this->newDocIds = ClientDocument::where('client_id', $client->id)
            ->where('uploaded_by', 'admin')
            ->where('viewed_by_client', false)
            ->pluck('id')->all();

        // Opening the page counts as "seen" for admin-shared documents.
        ClientDocument::where('client_id', $client->id)
            ->where('uploaded_by', 'admin')
            ->where('viewed_by_client', false)
            ->update(['viewed_by_client' => true]);
    }

    public function upload(): void
    {
        $client = Auth::guard('client')->user();

        $this->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,csv,txt,zip,rar,mp4,mp3,wav'],
            'category' => ['required', 'in:' . implode(',', ClientDocument::CATEGORIES)],
            'projectId' => ['nullable', 'exists:projects,id'],
            'clientNote' => ['nullable', 'string', 'max:500'],
        ]);

        // A chosen project must belong to this client.
        if ($this->projectId && ! $client->projects()->whereKey($this->projectId)->exists()) {
            $this->projectId = null;
        }

        $count = 0;
        foreach ($this->files as $file) {
            $stored = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('client-documents/' . $client->id, $stored, 'private');

            ClientDocument::create([
                'client_id' => $client->id,
                'project_id' => $this->projectId,
                'uploaded_by' => 'client',
                'category' => $this->category,
                'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $stored,
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
                'client_note' => $this->clientNote ?: null,
                'viewed_by_admin' => false,
            ]);

            $count++;
        }

        $client->increment('unread_documents_count', $count);

        $title = $count === 1 ? 'a document' : "{$count} documents";
        User::all()->each(fn ($admin) => $admin->notify(
            new \App\Notifications\Admin\ClientDocumentUploadedNotification($client, $title, str_replace('_', ' ', $this->category))
        ));

        $this->reset('files', 'category', 'projectId', 'clientNote');
        $this->dispatch('toast', type: 'success', message: "{$count} file(s) uploaded.");
    }

    public function deleteDocument(int $id): void
    {
        $client = Auth::guard('client')->user();
        $document = ClientDocument::where('client_id', $client->id)->where('uploaded_by', 'client')->find($id);

        if (! $document) {
            return;
        }

        \Storage::disk('private')->delete($document->path);
        $document->delete();
        $this->dispatch('toast', type: 'success', message: 'Document deleted.');
    }

    public function render()
    {
        $client = Auth::guard('client')->user();

        $documents = ClientDocument::where('client_id', $client->id)
            ->where(fn ($q) => $q->where('uploaded_by', 'client')->orWhere('is_visible_to_client', true))
            ->with(['project', 'invoice'])
            ->latest()
            ->get();

        return view('livewire.portal.documents.documents-index', [
            'documents' => $documents,
            'stats' => [
                'total' => $documents->count(),
                'my_uploads' => $documents->where('uploaded_by', 'client')->count(),
                'from_lumisk' => $documents->where('uploaded_by', 'admin')->count(),
                'new_from_lumisk' => count($this->newDocIds),
            ],
            'projects' => Project::where('client_id', $client->id)->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
