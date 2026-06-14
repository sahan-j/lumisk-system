<div>
    @if ($show)
        <x-app-modal title="Duplicate {{ ucfirst($type) }}" close="$set('show', false)" max-width="sm:max-w-lg">
            @if ($sourceNumber)
                <p class="mb-5 -mt-2 text-sm text-gray-500 dark:text-gray-400">Source: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $sourceNumber }}</span></p>
            @endif

            <form wire:submit="duplicate" class="space-y-4">
                <div>
                    <label class="form-label">Duplicate As <span class="text-red-500">*</span></label>
                    <select wire:model.live="duplicateAs" class="form-input-base">
                        <option value="invoice">Invoice</option>
                        <option value="estimate">Estimate</option>
                    </select>
                    @error('duplicateAs') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Client <span class="text-red-500">*</span></label>
                    <select wire:model="newClientId" class="form-input-base">
                        <option value="">Select client…</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
                        @endforeach
                    </select>
                    @error('newClientId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">New Issue Date <span class="text-red-500">*</span></label>
                        <input wire:model="newIssueDate" type="date" class="form-input-base">
                        @error('newIssueDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ $duplicateAs === 'estimate' ? 'Expiry Date' : 'Due Date' }}</label>
                        <input wire:model="newDueDate" type="date" class="form-input-base">
                        @error('newDueDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-ink-600">
                    <button type="button" wire:click="$set('show', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="duplicate">
                        <span wire:loading.remove wire:target="duplicate">Duplicate</span>
                        <span wire:loading wire:target="duplicate">Duplicating…</span>
                    </button>
                </div>
            </form>
        </x-app-modal>
    @endif
</div>
