<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Saved Items</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Reusable line items for invoices and estimates.</p>
        </div>
        <button wire:click="create" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            Add Item
        </button>
    </div>

    {{-- Search --}}
    <div class="relative mb-4 max-w-md">
        <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search items…" class="form-input-base pl-10">
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3">Unit</th>
                        <th class="px-5 py-3 text-right">Unit Price</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $item->name }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($item->description, 60) ?: '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $item->unit ?: '—' }}</td>
                            <td class="px-5 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ money($item->unit_price) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="edit({{ $item->id }})" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400">No saved items yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>

    {{-- Create / Edit modal --}}
    @if ($showForm)
        <x-app-modal :title="$editingId ? 'Edit Saved Item' : 'Add Saved Item'" close="$set('showForm', false)">
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
                        <label class="form-label">Unit Price <span class="text-red-500">*</span></label>
                        <input wire:model="unit_price" type="number" step="0.01" min="0" class="form-input-base">
                        @error('unit_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Unit</label>
                        <input wire:model="unit" type="text" placeholder="hour, project…" class="form-input-base">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">{{ $editingId ? 'Update' : 'Create' }}</button>
                </div>
            </form>
        </x-app-modal>
    @endif

    @if ($confirmingDelete)
        <x-app-modal title="Delete saved item?" close="$set('confirmingDelete', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">This permanently removes the saved item. Existing invoices and estimates are not affected.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </x-app-modal>
    @endif
</div>
