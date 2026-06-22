<div>
    {{-- Search hero --}}
    <div class="mb-8 rounded-2xl border border-brand-purple/15 bg-gradient-to-br from-cyan-500/5 to-brand-purple/10 p-8 text-center">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">How can we help you?</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Search our help articles</p>
        <div class="mx-auto mt-5 flex max-w-lg gap-2">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input wire:model.live.debounce.350ms="search" type="text" placeholder="Search articles…" class="form-input-base pl-10">
            </div>
        </div>
    </div>

    @if ($searching)
        {{-- Search results --}}
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Results for “{{ $search }}”</h2>
        @forelse ($results as $article)
            <a href="{{ route('portal.kb.article', $article->slug) }}" wire:navigate class="mb-2 block rounded-lg border border-gray-200 bg-white p-4 hover:border-brand-purple/40 dark:border-ink-600 dark:bg-ink-850">
                <div class="flex items-center gap-2">
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-medium" style="background-color: {{ $article->category->color }}1a; color: {{ $article->category->color }};">{{ $article->category->name }}</span>
                    <span class="text-xs text-gray-400">{{ $article->read_time }}</span>
                </div>
                <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $article->title }}</p>
                @if ($article->excerpt)<p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($article->excerpt, 120) }}</p>@endif
            </a>
        @empty
            <div class="rounded-lg border border-gray-200 bg-white p-10 text-center dark:border-ink-600 dark:bg-ink-850">
                <p class="text-sm text-gray-500 dark:text-gray-400">No articles found for “{{ $search }}”.</p>
                <button wire:click="$set('search', '')" class="mt-2 text-sm font-medium text-brand-purple hover:underline">Browse all help topics →</button>
            </div>
        @endforelse
    @else
        {{-- Categories --}}
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Browse by topic</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <a href="{{ route('portal.kb.category', $category->slug) }}" wire:navigate class="rounded-xl border border-gray-200 bg-white p-5 transition hover:border-brand-purple/40 hover:shadow-sm dark:border-ink-600 dark:bg-ink-850">
                    <span class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg" style="background-color: {{ $category->color }}1a;">
                        <svg class="h-5 w-5" style="color: {{ $category->color }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $category->icon_path }}" /></svg>
                    </span>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $category->name }}</p>
                    <p class="text-xs text-gray-400">{{ $category->published_count }} {{ Str::plural('article', $category->published_count) }}</p>
                    @if ($category->description)<p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $category->description }}</p>@endif
                </a>
            @endforeach
        </div>

        {{-- Recent + popular --}}
        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div>
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Recently added</h3>
                <div class="space-y-2">
                    @forelse ($recentArticles as $article)
                        <a href="{{ route('portal.kb.article', $article->slug) }}" wire:navigate class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 hover:border-brand-purple/40 dark:border-ink-600 dark:bg-ink-850">
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ $article->title }}</span>
                            <span class="text-xs text-gray-400">{{ $article->read_time }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-gray-400">No articles yet.</p>
                    @endforelse
                </div>
            </div>
            <div>
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Most popular</h3>
                <div class="space-y-2">
                    @forelse ($popularArticles as $article)
                        <a href="{{ route('portal.kb.article', $article->slug) }}" wire:navigate class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 hover:border-brand-purple/40 dark:border-ink-600 dark:bg-ink-850">
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ $article->title }}</span>
                            <span class="text-xs text-gray-400">{{ number_format($article->view_count) }} views</span>
                        </a>
                    @empty
                        <p class="text-sm text-gray-400">No articles yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
