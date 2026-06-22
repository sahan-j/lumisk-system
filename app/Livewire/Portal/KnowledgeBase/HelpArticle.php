<?php

namespace App\Livewire\Portal\KnowledgeBase;

use App\Models\KbArticle;
use App\Models\KbArticleFeedback;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('Help Article')]
class HelpArticle extends Component
{
    public KbArticle $article;
    public bool $gaveFeedback = false;

    public function mount(KbArticle $article): void
    {
        abort_unless($article->status === 'published', 404);

        $this->article = $article;
        $article->incrementViews();

        $this->gaveFeedback = KbArticleFeedback::where('article_id', $article->id)
            ->where('client_id', auth('client')->id())
            ->exists();
    }

    public function submitFeedback(bool $helpful): void
    {
        if ($this->gaveFeedback) {
            return;
        }

        KbArticleFeedback::create([
            'article_id' => $this->article->id,
            'client_id' => auth('client')->id(),
            'is_helpful' => $helpful,
            'ip_address' => request()->ip(),
        ]);

        $this->article->increment($helpful ? 'helpful_count' : 'not_helpful_count');
        $this->article->refresh();
        $this->gaveFeedback = true;

        $this->dispatch('toast', type: 'success', message: 'Thank you for your feedback!');
    }

    public function render()
    {
        $related = KbArticle::published()
            ->where('category_id', $this->article->category_id)
            ->where('id', '!=', $this->article->id)
            ->take(4)->get();

        return view('livewire.portal.knowledge-base.help-article', [
            'related' => $related,
        ]);
    }
}
