<div>
    {{-- Existing categories --}}
    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-ink-600">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
            <thead>
                <tr class="table-head">
                    <th class="px-4 py-2.5">Category</th>
                    <th class="px-4 py-2.5 text-center">Expenses</th>
                    <th class="px-4 py-2.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                @forelse ($categories as $category)
                    <tr wire:key="cat-{{ $category->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                        <td class="px-4 py-2.5">
                            <span class="inline-flex items-center gap-2 text-sm font-medium text-gray-900 dark:text-white">
                                <span class="h-3 w-3 rounded-full" style="background-color: {{ $category->color }}"></span>
                                {{ $category->name }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-center text-sm text-gray-500 dark:text-gray-400">{{ $category->expenses_count }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" wire:click="edit({{ $category->id }})" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Edit">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <button type="button" wire:click="delete({{ $category->id }})"
                                        @if ($category->expenses_count == 0) wire:confirm="Delete this category?" @endif
                                        class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-gray-400">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add / edit form --}}
    <div class="mt-4 rounded-lg border border-gray-200 p-4 dark:border-ink-600">
        <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $editingId ? 'Edit Category' : 'Add Category' }}</p>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label class="form-label">Name</label>
                <input wire:model="name" type="text" class="form-input-base" placeholder="Category name">
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Color</label>
                <input wire:model="color" type="color" class="h-[42px] w-16 cursor-pointer rounded-lg border border-gray-300 bg-white p-1 dark:border-ink-600 dark:bg-ink-800">
                @error('color') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <button type="button" wire:click="save" class="btn-primary">{{ $editingId ? 'Update' : 'Add' }}</button>
                @if ($editingId)
                    <button type="button" wire:click="cancelEdit" class="btn-secondary">Cancel</button>
                @endif
            </div>
        </div>
    </div>
</div>
