<?php

namespace App\Livewire\Public\KnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.public-help')]
#[Title('Help Center')]
class PublicHelpCenter extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    public function render()
    {
        $searching = strlen(trim($this->search)) >= 2;

        $results = collect();
        if ($searching) {
            $term = trim($this->search);
            $results = KbArticle::public()->with('category')
                ->where(fn ($q) => $q->where('title', 'like', "%{$term}%")
                    ->orWhere('content', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%"))
                ->orderByDesc('view_count')
                ->take(20)->get();
        }

        // Only categories that have at least one public published article.
        $categories = $searching ? collect() : KbCategory::where('is_active', true)
            ->withCount(['articles as public_count' => fn ($q) => $q->where('status', 'published')->where('visibility', 'public')])
            ->having('public_count', '>', 0)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.public.knowledge-base.public-help-center', [
            'searching' => $searching,
            'results' => $results,
            'categories' => $categories,
            'popularArticles' => $searching ? collect() : KbArticle::public()->with('category')->orderByDesc('view_count')->take(6)->get(),
        ]);
    }
}
