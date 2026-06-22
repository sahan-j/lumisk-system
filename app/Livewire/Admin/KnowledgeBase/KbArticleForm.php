<?php

namespace App\Livewire\Admin\KnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Article')]
class KbArticleForm extends Component
{
    public ?KbArticle $article = null;

    public ?int $category_id = null;
    public string $title = '';
    public string $content = '';
    public ?string $excerpt = null;
    public string $status = 'draft';
    public string $visibility = 'portal_only';
    public ?string $author_name = null;
    public ?int $sort_order = 0;

    public function mount(?KbArticle $article = null): void
    {
        if ($article && $article->exists) {
            $this->article = $article;
            $this->category_id = $article->category_id;
            $this->title = $article->title;
            $this->content = $article->content;
            $this->excerpt = $article->excerpt;
            $this->status = $article->status;
            $this->visibility = $article->visibility;
            $this->author_name = $article->author_name;
            $this->sort_order = $article->sort_order;
        } else {
            $this->category_id = (int) request('category') ?: KbCategory::orderBy('sort_order')->value('id');
            $this->author_name = auth()->user()->name;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:kb_categories,id'],
            'content' => ['required', 'string', 'min:10'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:draft,published'],
            'visibility' => ['required', 'in:portal_only,public'],
            'author_name' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'excerpt' => $this->excerpt,
            'status' => $validated['status'],
            'visibility' => $validated['visibility'],
            'author_name' => $this->author_name ?: auth()->user()->name,
            'sort_order' => $this->sort_order ?? 0,
        ];

        if ($this->article) {
            $this->article->update($data);
        } else {
            KbArticle::create($data);
        }

        $this->dispatch('toast', type: 'success', message: 'Article saved.');

        return $this->redirect(route('admin.kb.articles.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.knowledge-base.kb-article-form', [
            'categories' => KbCategory::orderBy('sort_order')->get(),
        ]);
    }
}
