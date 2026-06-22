<div>
    <nav class="mb-4 flex flex-wrap items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('public.kb.index') }}" wire:navigate class="hover:text-brand-purple">Help</a>
        <span>/</span>
        <a href="{{ route('public.kb.category', $article->category->slug) }}" wire:navigate class="hover:text-brand-purple">{{ $article->category->name }}</a>
        <span>/</span>
        <span class="text-gray-700 dark:text-gray-200">{{ Str::limit($article->title, 40) }}</span>
    </nav>

    <article class="mx-auto max-w-3xl">
        <header class="mb-6 border-b border-gray-200 pb-5 dark:border-ink-600">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $article->title }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                <span class="rounded-full px-2 py-0.5 font-medium" style="background-color: {{ $article->category->color }}1a; color: {{ $article->category->color }};">{{ $article->category->name }}</span>
                <span>·</span><span>{{ $article->read_time }}</span>
                <span>·</span><span>{{ number_format($article->view_count) }} views</span>
                @if ($article->published_at)<span>·</span><span>{{ $article->published_at->format('M d, Y') }}</span>@endif
            </div>
        </header>

        <div class="kb-prose text-[15px] leading-relaxed text-gray-700 dark:text-gray-300">
            {!! Str::markdown($article->content) !!}
        </div>

        <div class="mt-10 rounded-xl border border-gray-200 bg-gray-50 p-6 text-center dark:border-ink-600 dark:bg-ink-800">
            @if ($gaveFeedback)
                <p class="text-sm font-medium text-green-600 dark:text-green-400">✓ Thanks — your feedback has been recorded.</p>
            @else
                <p class="mb-4 text-sm font-medium text-gray-900 dark:text-white">Was this article helpful?</p>
                <div class="flex justify-center gap-3">
                    <button wire:click="submitFeedback(true)" class="rounded-lg border border-green-500 px-6 py-2 text-sm font-medium text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20">👍 Yes, helpful</button>
                    <button wire:click="submitFeedback(false)" class="rounded-lg border border-red-500 px-6 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">👎 Not helpful</button>
                </div>
            @endif
            @if ($article->helpful_count + $article->not_helpful_count > 0)
                <p class="mt-3 text-xs text-gray-400">{{ $article->helpful_count + $article->not_helpful_count }} {{ Str::plural('person', $article->helpful_count + $article->not_helpful_count) }} found this {{ $article->helpful_percentage }}% helpful</p>
            @endif
        </div>

        @if ($related->isNotEmpty())
            <div class="mt-8">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Related articles</h3>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($related as $rel)
                        <a href="{{ route('public.kb.article', $rel->slug) }}" wire:navigate class="rounded-lg border border-gray-200 bg-white p-3 text-sm font-medium text-gray-700 hover:border-brand-purple/40 dark:border-ink-600 dark:bg-ink-850 dark:text-gray-200">{{ $rel->title }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </article>
</div>
