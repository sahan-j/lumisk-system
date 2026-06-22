<?php

namespace App\Livewire\Public\KnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public-help')]
#[Title('Help Center')]
class PublicHelpCategory extends Component
{
    use WithPagination;

    public KbCategory $category;

    public function mount(KbCategory $category): void
    {
        abort_unless($category->is_active, 404);
        $this->category = $category;
    }

    public function render()
    {
        $articles = KbArticle::public()
            ->where('category_id', $this->category->id)
            ->orderBy('sort_order')
            ->paginate(15);

        abort_if($articles->total() === 0, 404);

        return view('livewire.public.knowledge-base.public-help-category', [
            'articles' => $articles,
        ]);
    }
}
