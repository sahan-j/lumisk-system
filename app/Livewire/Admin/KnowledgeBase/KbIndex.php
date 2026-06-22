<?php

namespace App\Livewire\Admin\KnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Knowledge Base')]
class KbIndex extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;

    public string $name = '';
    public ?string $description = null;
    public string $color = '#6d5cff';
    public ?int $sort_order = 0;
    public bool $is_active = true;

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function create(): void
    {
        $this->reset('editingId', 'name', 'description', 'sort_order');
        $this->color = '#6d5cff';
        $this->is_active = true;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $category = KbCategory::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->color = $category->color ?: '#6d5cff';
        $this->sort_order = $category->sort_order;
        $this->is_active = (bool) $category->is_active;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $this->description,
            'color' => $this->color,
            'sort_order' => $this->sort_order ?? 0,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            KbCategory::findOrFail($this->editingId)->update($data);
            $message = 'Category updated.';
        } else {
            KbCategory::create($data);
            $message = 'Category created.';
        }

        $this->showForm = false;
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            // cascadeOnDelete removes the category's articles too.
            KbCategory::findOrFail($this->deleteId)->delete();
            $this->dispatch('toast', type: 'success', message: 'Category deleted.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $categories = KbCategory::withCount([
            'articles',
            'articles as published_count' => fn ($q) => $q->where('status', 'published'),
        ])->orderBy('sort_order')->get();

        return view('livewire.admin.knowledge-base.kb-index', [
            'categories' => $categories,
            'stats' => [
                'total_articles' => KbArticle::count(),
                'published' => KbArticle::where('status', 'published')->count(),
                'total_views' => (int) KbArticle::sum('view_count'),
                'top_article' => KbArticle::orderByDesc('view_count')->first(),
            ],
        ]);
    }
}
