<?php

namespace App\Livewire\Portal\KnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
#[Title('Help Center')]
class HelpCategory extends Component
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
        $articles = KbArticle::published()
            ->where('category_id', $this->category->id)
            ->orderBy('sort_order')
            ->paginate(15);

        return view('livewire.portal.knowledge-base.help-category', [
            'articles' => $articles,
        ]);
    }
}
