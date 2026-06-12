<div>
    <form wire:submit="save">
        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('admin.estimates.index') }}" class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    Estimates
                </a>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $estimate ? 'Edit ' . $estimate->estimate_number : 'New Estimate' }}
                </h2>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.estimates.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <span wire:loading.remove wire:target="save">Save Estimate</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Left: details --}}
            <div class="space-y-6 lg:col-span-2">
                <div class="card p-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="form-label">Client <span class="text-red-500">*</span></label>
                            <select wire:model="client_id" class="form-input-base">
                                <option value="">Select a client…</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}@if($client->company_name) — {{ $client->company_name }}@endif</option>
                                @endforeach
                            </select>
                            @error('client_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Issue Date <span class="text-red-500">*</span></label>
                            <input wire:model="issue_date" type="date" class="form-input-base">
                            @error('issue_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Expiry Date</label>
                            <input wire:model="expiry_date" type="date" class="form-input-base">
                        </div>
                    </div>
                </div>

                {{-- Line items --}}
                <div class="card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Line Items</h3>
                        <button type="button" wire:click="$set('showSavedItems', true)" class="btn-secondary !py-1.5 text-xs">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" /></svg>
                            Add from Saved Items
                        </button>
                    </div>

                    @error('items') <p class="mb-2 text-xs text-red-500">{{ $message }}</p> @enderror

                    <div class="space-y-3">
                        @foreach ($items as $index => $item)
                            <div wire:key="item-{{ $index }}" class="rounded-lg border border-gray-200 p-3 dark:border-ink-600">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                                    <div class="flex-1 space-y-2">
                                        <input wire:model="items.{{ $index }}.name" type="text" placeholder="Item name" class="form-input-base !py-1.5 text-sm font-medium">
                                        @error("items.$index.name") <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                                        <input wire:model="items.{{ $index }}.description" type="text" placeholder="Description (optional)" class="form-input-base !py-1.5 text-xs text-gray-500">
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 sm:w-80">
                                        <div>
                                            <label class="mb-0.5 block text-[10px] uppercase text-gray-400">Qty</label>
                                            <input wire:model.live.debounce.400ms="items.{{ $index }}.quantity" type="number" step="0.01" min="0" class="form-input-base !py-1.5 text-sm">
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[10px] uppercase text-gray-400">Unit Price</label>
                                            <input wire:model.live.debounce.400ms="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" class="form-input-base !py-1.5 text-sm">
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[10px] uppercase text-gray-400">Total</label>
                                            <input value="{{ number_format((float)($item['quantity'] ?? 0) * (float)($item['unit_price'] ?? 0), 2) }}" type="text" readonly class="form-input-base !py-1.5 text-sm bg-gray-50 dark:bg-ink-900">
                                        </div>
                                    </div>
                                    <button type="button" wire:click="removeItem({{ $index }})" class="self-center rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" wire:click="addItem" class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-gold hover:underline">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Add line item
                    </button>
                </div>

                {{-- Notes & terms --}}
                <div class="card grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Notes</label>
                        <textarea wire:model="notes" rows="3" class="form-input-base text-sm"></textarea>
                    </div>
                    <div>
                        <label class="form-label">Terms</label>
                        <textarea wire:model="terms" rows="3" class="form-input-base text-sm"></textarea>
                    </div>
                </div>
            </div>

            {{-- Right: totals & status --}}
            <div class="space-y-6">
                <div class="card p-5">
                    <label class="form-label">Status</label>
                    <select wire:model="status" class="form-input-base capitalize">
                        @foreach (\App\Models\Estimate::STATUSES as $s)
                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="card p-5">
                    <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Summary</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ money($this->subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 dark:text-gray-400">Tax %</span>
                            <input wire:model.live.debounce.400ms="tax_rate" type="number" step="0.01" min="0" max="100" class="form-input-base !w-24 !py-1 text-right text-sm">
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Tax amount</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ money($this->taxAmount) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 dark:text-gray-400">Discount</span>
                            <input wire:model.live.debounce.400ms="discount_amount" type="number" step="0.01" min="0" class="form-input-base !w-24 !py-1 text-right text-sm">
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-3 dark:border-ink-600">
                            <span class="font-semibold text-gray-900 dark:text-white">Total</span>
                            <span class="text-lg font-semibold text-gold">{{ money($this->total) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Saved items picker --}}
    @if ($showSavedItems)
        <x-app-modal title="Add from Saved Items" close="$set('showSavedItems', false)" max-width="sm:max-w-lg">
            <div class="max-h-96 divide-y divide-gray-100 overflow-y-auto dark:divide-ink-700">
                @forelse ($savedItems as $si)
                    <button type="button" wire:click="addFromSaved({{ $si->id }})" class="flex w-full items-center justify-between px-1 py-3 text-left hover:bg-gray-50 dark:hover:bg-ink-800">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $si->name }}</p>
                            @if ($si->description)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $si->description }}</p>@endif
                        </div>
                        <span class="text-sm font-medium text-gold">{{ money($si->unit_price) }}@if($si->unit)<span class="text-xs text-gray-400">/{{ $si->unit }}</span>@endif</span>
                    </button>
                @empty
                    <p class="py-8 text-center text-sm text-gray-400">No saved items yet.</p>
                @endforelse
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" wire:click="$set('showSavedItems', false)" class="btn-secondary">Done</button>
            </div>
        </x-app-modal>
    @endif
</div>
