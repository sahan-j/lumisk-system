<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Invoice Templates</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Reusable presets to pre-fill new invoices and estimates.</p>
        </div>
        <a href="{{ route('admin.invoice-templates.create') }}" wire:navigate class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            New Template
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card p-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Total Templates</span>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalTemplates }}</p>
        </div>
        <div class="card p-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Most Used</span>
            @if ($mostUsed && $mostUsed->usage_count > 0)
                <p class="mt-2 truncate text-lg font-semibold text-gray-900 dark:text-white">{{ $mostUsed->name }}</p>
                <p class="text-xs text-gray-400">Used {{ $mostUsed->usage_count }}×</p>
            @else
                <p class="mt-2 text-lg font-semibold text-gray-400">—</p>
            @endif
        </div>
        <div class="card p-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Total Items</span>
            <p class="mt-2 text-2xl font-semibold text-brand-purple">{{ $totalItems }}</p>
        </div>
    </div>

    {{-- Grid --}}
    @if ($templates->count() > 0)
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($templates as $template)
                <div wire:key="tpl-{{ $template->id }}" class="card flex flex-col p-4" style="border-top:3px solid #6d5cff;">
                    {{-- Header --}}
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $template->name }}</p>
                            @if ($template->description)
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($template->description, 60) }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 rounded-full bg-brand-purple/10 px-2 py-0.5 text-[10px] font-medium text-brand-purple">{{ $template->type_label }}</span>
                    </div>

                    {{-- Items preview --}}
                    <div class="mb-3 rounded-lg bg-gray-50 p-2.5 dark:bg-ink-800">
                        @foreach ($template->items->take(3) as $item)
                            <div class="flex justify-between gap-2 border-b border-gray-100 py-1 text-xs last:border-0 dark:border-ink-700">
                                <span class="truncate text-gray-700 dark:text-gray-300">{{ $item->name }}</span>
                                <span class="shrink-0 font-mono text-brand-purple">{{ $template->currency_code }} {{ number_format($item->unit_price, 0) }}</span>
                            </div>
                        @endforeach
                        @if ($template->items_count > 3)
                            <p class="pt-1 text-center text-[10px] text-gray-400">+ {{ $template->items_count - 3 }} more items</p>
                        @endif
                    </div>

                    {{-- Total + usage --}}
                    <div class="mb-3 mt-auto flex items-center justify-between">
                        <span class="font-mono text-base font-bold text-gray-900 dark:text-white">{{ $template->currency_code }} {{ number_format($template->total, 0) }}</span>
                        <span class="text-xs text-gray-400">Used {{ $template->usage_count }}×</span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <a href="{{ route('admin.invoice-templates.edit', $template) }}" wire:navigate class="btn-secondary flex-1 justify-center !py-1.5 text-xs">Edit</a>
                        <button wire:click="confirmDelete({{ $template->id }})" class="rounded-md border border-red-200 px-3 py-1.5 text-red-500 hover:bg-red-50 dark:border-red-900/40 dark:hover:bg-red-900/20" title="Delete">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $templates->links() }}</div>
    @else
        <div class="card p-12 text-center">
            <p class="text-sm text-gray-400">No templates yet. Create one, or use “Save as Template” on any invoice or estimate.</p>
        </div>
    @endif

    @if ($confirmingDelete)
        <x-app-modal title="Delete template?" close="$set('confirmingDelete', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">This removes the template. Existing invoices and estimates created from it are not affected.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </x-app-modal>
    @endif
</div>
