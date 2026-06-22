<?php

namespace App\Livewire\Public\KnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbArticleFeedback;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.public-help')]
#[Title('Help Article')]
class PublicHelpArticle extends Component
{
    public KbArticle $article;
    public bool $gaveFeedback = false;

    public function mount(KbArticle $article): void
    {
        // Public surface only serves published, publicly-visible articles.
        abort_unless($article->status === 'published' && $article->visibility === 'public', 404);

        $this->article = $article;
        $article->incrementViews();

        $this->gaveFeedback = KbArticleFeedback::where('article_id', $article->id)
            ->where('ip_address', request()->ip())
            ->exists();
    }

    public function submitFeedback(bool $helpful): void
    {
        if ($this->gaveFeedback) {
            return;
        }

        // Guard against duplicates from the same IP (no client account here).
        $exists = KbArticleFeedback::where('article_id', $this->article->id)
            ->where('ip_address', request()->ip())
            ->exists();

        if (! $exists) {
            KbArticleFeedback::create([
                'article_id' => $this->article->id,
                'client_id' => null,
                'is_helpful' => $helpful,
                'ip_address' => request()->ip(),
            ]);
            $this->article->increment($helpful ? 'helpful_count' : 'not_helpful_count');
            $this->article->refresh();
        }

        $this->gaveFeedback = true;
        $this->dispatch('toast', type: 'success', message: 'Thank you for your feedback!');
    }

    public function render()
    {
        $related = KbArticle::public()
            ->where('category_id', $this->article->category_id)
            ->where('id', '!=', $this->article->id)
            ->take(4)->get();

        return view('livewire.public.knowledge-base.public-help-article', [
            'related' => $related,
        ]);
    }
}
