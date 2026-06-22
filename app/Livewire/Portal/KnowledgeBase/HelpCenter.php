<?php

namespace App\Livewire\Portal\KnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('Help Center')]
class HelpCenter extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    public function render()
    {
        $searching = strlen(trim($this->search)) >= 2;

        $results = collect();
        if ($searching) {
            $term = trim($this->search);
            $results = KbArticle::published()->with('category')
                ->where(fn ($q) => $q->where('title', 'like', "%{$term}%")
                    ->orWhere('content', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%"))
                ->orderByDesc('view_count')
                ->take(20)->get();
        }

        $categories = $searching ? collect() : KbCategory::where('is_active', true)
            ->withCount(['articles as published_count' => fn ($q) => $q->where('status', 'published')])
            ->having('published_count', '>', 0)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.portal.knowledge-base.help-center', [
            'searching' => $searching,
            'results' => $results,
            'categories' => $categories,
            'recentArticles' => $searching ? collect() : KbArticle::published()->with('category')->latest('published_at')->take(5)->get(),
            'popularArticles' => $searching ? collect() : KbArticle::published()->with('category')->orderByDesc('view_count')->take(5)->get(),
        ]);
    }
}
