<?php

namespace App\Livewire\Admin\Documents;

use App\Models\ClientDocument;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Client Documents')]
class DocumentsIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'unread'; // unread | all
    #[Url]
    public string $category = '';

    public function updating($name): void
    {
        if (in_array($name, ['filter', 'category'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $documents = ClientDocument::with('client')
            ->where('uploaded_by', 'client')
            ->when($this->filter === 'unread', fn ($q) => $q->where('viewed_by_admin', false))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.documents.documents-index', [
            'documents' => $documents,
            'categories' => ClientDocument::CATEGORIES,
            'stats' => [
                'unread' => ClientDocument::where('uploaded_by', 'client')->where('viewed_by_admin', false)->count(),
                'total_today' => ClientDocument::whereDate('created_at', today())->count(),
                'clients_with_uploads' => ClientDocument::where('uploaded_by', 'client')->distinct('client_id')->count('client_id'),
            ],
        ]);
    }
}
