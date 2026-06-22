<div>
    <form wire:submit="save">
        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('admin.kb.articles.index') }}" wire:navigate class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    Articles
                </a>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $article ? 'Edit Article' : 'New Article' }}</h2>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.kb.articles.index') }}" wire:navigate class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <span wire:loading.remove wire:target="save">Save Article</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Content --}}
            <div class="space-y-6 lg:col-span-2">
                <div class="card p-5">
                    <label class="form-label">Title <span class="text-red-500">*</span></label>
                    <input wire:model="title" type="text" class="form-input-base text-lg font-medium" placeholder="Article title">
                    @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="card p-5">
                    <label class="form-label">Content <span class="text-red-500">*</span></label>
                    <textarea wire:model="content" rows="18" class="form-input-base font-mono text-sm" placeholder="Write your article…"></textarea>
                    <p class="mt-1 text-xs text-gray-400">Supports basic markdown: **bold**, *italic*, # Heading, ## Subheading, - list items, `code`.</p>
                    @error('content') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="card p-5">
                    <label class="form-label">Excerpt</label>
                    <textarea wire:model="excerpt" rows="2" class="form-input-base text-sm" placeholder="Short summary shown in listings (optional)"></textarea>
                    @error('excerpt') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Settings --}}
            <div class="space-y-6">
                <div class="card space-y-4 p-5">
                    <div>
                        <label class="form-label">Category <span class="text-red-500">*</span></label>
                        <select wire:model="category_id" class="form-input-base">
                            <option value="">Select…</option>
                            @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                        @error('category_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select wire:model="status" class="form-input-base">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Visibility</label>
                        <select wire:model="visibility" class="form-input-base">
                            <option value="portal_only">Portal only (logged-in clients)</option>
                            <option value="public">Public (no login)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Author Name</label>
                        <input wire:model="author_name" type="text" class="form-input-base">
                    </div>
                    <div>
                        <label class="form-label">Sort Order</label>
                        <input wire:model="sort_order" type="number" min="0" class="form-input-base">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
