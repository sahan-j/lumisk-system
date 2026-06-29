<?php

namespace App\Livewire\Portal\Documents;

use App\Models\ClientDocument;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('My Documents')]
class DocumentsIndex extends Component
{
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
                'total'          => $documents->count(),
                'my_uploads'     => $documents->where('uploaded_by', 'client')->count(),
                'from_lumisk'    => $documents->where('uploaded_by', 'admin')->count(),
                'new_from_lumisk' => count($this->newDocIds),
            ],
            'projects' => Project::where('client_id', $client->id)->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
