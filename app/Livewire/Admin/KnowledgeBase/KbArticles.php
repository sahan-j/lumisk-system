<?php

namespace App\Livewire\Admin\KnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('KB Articles')]
class KbArticles extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $status = '';

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updating($name): void
    {
        if (in_array($name, ['search', 'category', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function togglePublish(int $id): void
    {
        $article = KbArticle::findOrFail($id);
        $article->update(['status' => $article->status === 'published' ? 'draft' : 'published']);
        $this->dispatch('toast', type: 'success', message: $article->status === 'published' ? 'Article published.' : 'Article unpublished.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            KbArticle::findOrFail($this->deleteId)->delete();
            $this->dispatch('toast', type: 'success', message: 'Article deleted.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $articles = KbArticle::with('category')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.knowledge-base.kb-articles', [
            'articles' => $articles,
            'categories' => KbCategory::orderBy('sort_order')->get(),
        ]);
    }
}
