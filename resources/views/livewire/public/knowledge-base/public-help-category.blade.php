<div>
    <nav class="mb-4 flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('public.kb.index') }}" wire:navigate class="hover:text-brand-purple">Help</a>
        <span>/</span>
        <span class="text-gray-700 dark:text-gray-200">{{ $category->name }}</span>
    </nav>

    <div class="mb-6 flex items-center gap-3">
        <span class="flex h-11 w-11 items-center justify-center rounded-lg" style="background-color: {{ $category->color }}1a;">
            <svg class="h-6 w-6" style="color: {{ $category->color }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $category->icon_path }}" /></svg>
        </span>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $category->name }}</h1>
            @if ($category->description)<p class="text-sm text-gray-500 dark:text-gray-400">{{ $category->description }}</p>@endif
        </div>
    </div>

    <div class="space-y-2">
        @foreach ($articles as $article)
            <a href="{{ route('public.kb.article', $article->slug) }}" wire:navigate class="block rounded-lg border border-gray-200 bg-white p-4 hover:border-brand-purple/40 dark:border-ink-600 dark:bg-ink-850">
                <div class="flex items-center justify-between gap-3">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $article->title }}</p>
                    <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500 dark:bg-ink-700 dark:text-gray-400">{{ $article->read_time }}</span>
                </div>
                @if ($article->excerpt)<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($article->excerpt, 140) }}</p>@endif
            </a>
        @endforeach
    </div>

    <div class="mt-4">{{ $articles->links() }}</div>
</div>
