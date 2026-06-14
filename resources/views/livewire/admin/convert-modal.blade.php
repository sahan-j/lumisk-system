<div>
    @if ($show)
        <x-app-modal :title="$this->title()" close="$set('show', false)" max-width="sm:max-w-lg">
            @if ($sourceNumber)
                <p class="mb-5 -mt-2 text-sm text-gray-500 dark:text-gray-400">Source: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $sourceNumber }}</span></p>
            @endif

            <form wire:submit="convert" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">New Issue Date <span class="text-red-500">*</span></label>
                        <input wire:model="newIssueDate" type="date" class="form-input-base">
                        @error('newIssueDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ $this->targetType() === 'estimate' ? 'Expiry Date' : 'Due Date' }}</label>
                        <input wire:model="newDueDate" type="date" class="form-input-base">
                        @error('newDueDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if ($direction === 'estimate_to_invoice')
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" wire:model="markAccepted" class="rounded border-gray-300 text-brand-purple focus:ring-brand-purple dark:border-ink-600 dark:bg-ink-800">
                        Mark source estimate as accepted
                    </label>
                @endif

                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="partialConvert" class="rounded border-gray-300 text-brand-purple focus:ring-brand-purple dark:border-ink-600 dark:bg-ink-800">
                    Partial convert (select specific items)
                </label>

                @if ($partialConvert)
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-ink-600">
                        <div class="space-y-1">
                            @foreach ($items as $item)
                                <label wire:key="conv-item-{{ $item['id'] }}" class="flex items-center justify-between gap-3 rounded px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-ink-800">
                                    <span class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                        <input type="checkbox" value="{{ $item['id'] }}" wire:model.live="selectedItems"
                                               class="rounded border-gray-300 text-brand-purple focus:ring-brand-purple dark:border-ink-600 dark:bg-ink-800">
                                        {{ $item['name'] }}
                                    </span>
                                    <span class="font-mono text-sm text-gray-500 dark:text-gray-400">{{ money($item['total']) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-2 flex items-center justify-between border-t border-gray-200 pt-2 text-sm dark:border-ink-600">
                            <span class="font-medium text-gray-700 dark:text-gray-200">Selected total</span>
                            <span class="font-mono font-semibold text-brand-purple">{{ money($this->selectedTotal) }}</span>
                        </div>
                    </div>
                    @error('selectedItems') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                @endif

                <div class="flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-ink-600">
                    <button type="button" wire:click="$set('show', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="convert">
                        <span wire:loading.remove wire:target="convert">Convert</span>
                        <span wire:loading wire:target="convert">Converting…</span>
                    </button>
                </div>
            </form>
        </x-app-modal>
    @endif
</div>
