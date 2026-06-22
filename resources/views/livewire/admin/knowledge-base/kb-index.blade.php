<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Knowledge Base</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Help articles for your client portal.</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="create" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                New Category
            </button>
            <a href="{{ route('admin.kb.articles.create') }}" wire:navigate class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                New Article
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Articles</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_articles'] }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Published</span><p class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">{{ $stats['published'] }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Views</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total_views']) }}</p></div>
        <div class="card p-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Top Article</span>
            @if ($stats['top_article'])
                <p class="mt-2 truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $stats['top_article']->title }}</p>
                <p class="text-xs text-gray-400">{{ number_format($stats['top_article']->view_count) }} views</p>
            @else
                <p class="mt-2 text-2xl font-semibold text-gray-400">—</p>
            @endif
        </div>
    </div>

    {{-- Manage articles link --}}
    <div class="mb-4">
        <a href="{{ route('admin.kb.articles.index') }}" wire:navigate class="text-sm font-medium text-brand-purple hover:underline">Manage all articles →</a>
    </div>

    {{-- Categories grid --}}
    @if ($categories->count() > 0)
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($categories as $category)
                <div wire:key="cat-{{ $category->id }}" class="card p-4" style="border-top:3px solid {{ $category->color }};">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg" style="background-color: {{ $category->color }}1a;">
                                <svg class="h-5 w-5" style="color: {{ $category->color }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $category->icon_path }}" /></svg>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $category->name }}</p>
                                <p class="text-xs text-gray-400">{{ $category->published_count }} published · {{ $category->articles_count }} total</p>
                            </div>
                        </div>
                        @unless ($category->is_active)
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500 dark:bg-ink-700">Inactive</span>
                        @endunless
                    </div>
                    @if ($category->description)
                        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($category->description, 90) }}</p>
                    @endif
                    <div class="flex gap-2">
                        <a href="{{ route('admin.kb.articles.index', ['category' => $category->id]) }}" wire:navigate class="btn-secondary flex-1 justify-center !py-1.5 text-xs">View Articles</a>
                        <button wire:click="edit({{ $category->id }})" class="rounded-md border border-gray-200 px-3 py-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:border-ink-600 dark:hover:bg-ink-700" title="Edit">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </button>
                        <button wire:click="confirmDelete({{ $category->id }})" class="rounded-md border border-red-200 px-3 py-1.5 text-red-500 hover:bg-red-50 dark:border-red-900/40 dark:hover:bg-red-900/20" title="Delete">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card p-12 text-center text-sm text-gray-400">No categories yet. Create one to start adding articles.</div>
    @endif

    {{-- Category modal --}}
    @if ($showForm)
        <x-app-modal :title="$editingId ? 'Edit Category' : 'New Category'" close="$set('showForm', false)">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="form-label">Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="form-input-base">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea wire:model="description" rows="2" class="form-input-base"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Color</label>
                        <input wire:model="color" type="color" class="h-10 w-full rounded-lg border border-gray-200 dark:border-ink-600">
                    </div>
                    <div>
                        <label class="form-label">Sort Order</label>
                        <input wire:model="sort_order" type="number" min="0" class="form-input-base">
                    </div>
                </div>
                <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-brand-purple focus:ring-brand-purple dark:border-ink-600 dark:bg-ink-800">
                    Active
                </label>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">{{ $editingId ? 'Update' : 'Create' }}</button>
                </div>
            </form>
        </x-app-modal>
    @endif

    @if ($confirmingDelete)
        <x-app-modal title="Delete category?" close="$set('confirmingDelete', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">This deletes the category <strong>and all its articles</strong>. This cannot be undone.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </x-app-modal>
    @endif
</div>
