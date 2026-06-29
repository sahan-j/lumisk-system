<?php

namespace App\Livewire\Admin\Clients;

use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\Project;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Client Documents')]
class ClientDocuments extends Component
{
    use WithFileUploads;

    public Client $client;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $files = [];
    public string $category = 'contract';
    public ?int $projectId = null;
    public string $description = '';
    public bool $isVisibleToClient = true;

    public function mount(Client $client): void
    {
        $this->client = $client;

        // Viewing the page clears the "new client uploads" indicator.
        ClientDocument::where('client_id', $client->id)
            ->where('uploaded_by', 'client')
            ->where('viewed_by_admin', false)
            ->update(['viewed_by_admin' => true]);

        $client->update(['unread_documents_count' => 0]);
    }

    public function upload(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('clients.edit'), 403);

        $this->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'max:20480'],
            'category' => ['required', 'in:' . implode(',', ClientDocument::CATEGORIES)],
            'projectId' => ['nullable', 'exists:projects,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($this->files as $file) {
            $stored = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('client-documents/' . $this->client->id, $stored, 'private');

            $doc = ClientDocument::create([
                'client_id' => $this->client->id,
                'project_id' => $this->projectId,
                'uploaded_by' => 'admin',
                'category' => $this->category,
                'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $stored,
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
                'description' => $this->description ?: null,
                'is_visible_to_client' => $this->isVisibleToClient,
                'viewed_by_admin' => true,
            ]);

            if ($doc->is_visible_to_client) {
                $this->client->notify(new \App\Notifications\Client\AdminDocumentSharedNotification($doc));
            }
        }

        $this->reset('files', 'description');
        $this->category = 'contract';
        $this->isVisibleToClient = true;
        $this->dispatch('toast', type: 'success', message: 'Document(s) shared.');
    }

    public function toggleVisibility(int $id): void
    {
        $doc = ClientDocument::where('client_id', $this->client->id)->where('uploaded_by', 'admin')->find($id);
        if ($doc) {
            $doc->update(['is_visible_to_client' => ! $doc->is_visible_to_client]);
            $this->dispatch('toast', type: 'success', message: $doc->is_visible_to_client ? 'Now visible to client.' : 'Hidden from client.');
        }
    }

    public function deleteDocument(int $id): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('clients.edit'), 403);

        $doc = ClientDocument::where('client_id', $this->client->id)->find($id);
        if ($doc) {
            \Storage::disk('private')->delete($doc->path);
            $doc->delete();
            $this->dispatch('toast', type: 'success', message: 'Document deleted.');
        }
    }

    public function render()
    {
        $documents = ClientDocument::where('client_id', $this->client->id)
            ->with(['project', 'invoice'])
            ->latest()
            ->get();

        return view('livewire.admin.clients.client-documents', [
            'documents' => $documents,
            'stats' => [
                'total' => $documents->count(),
                'from_client' => $documents->where('uploaded_by', 'client')->count(),
                'from_admin' => $documents->where('uploaded_by', 'admin')->count(),
                'unviewed_by_client' => $documents->where('uploaded_by', 'admin')->where('viewed_by_client', false)->count(),
            ],
            'projects' => Project::where('client_id', $this->client->id)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
