<div>
    <form wire:submit="save">
        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('admin.credit-notes.index') }}" wire:navigate class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    Credit Notes
                </a>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $creditNote ? 'Edit ' . $creditNote->credit_note_number : 'New Credit Note' }}
                </h2>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.credit-notes.index') }}" wire:navigate class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <span wire:loading.remove wire:target="save">Save Credit Note</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Left: details --}}
            <div class="space-y-6 lg:col-span-2">
                <div class="card p-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
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
                            <label class="form-label">Reference Invoice <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                            <div class="flex gap-2">
                                <select wire:model.live="invoice_id" class="form-input-base">
                                    <option value="">Standalone (no invoice)</option>
                                    @foreach ($invoices as $inv)
                                        <option value="{{ $inv->id }}">{{ $inv->invoice_number }} — {{ $inv->client?->name }} ({{ money($inv->total) }})</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="importFromInvoice" @disabled(! $invoice_id) class="btn-secondary shrink-0 whitespace-nowrap !py-1.5 text-xs disabled:opacity-50" title="Copy the invoice's line items">Import items</button>
                            </div>
                            @error('invoice_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Issue Date <span class="text-red-500">*</span></label>
                            <input wire:model="issue_date" type="date" class="form-input-base">
                            @error('issue_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Reason <span class="text-red-500">*</span></label>
                            <input wire:model="reason" type="text" list="cn-reasons" placeholder="Reason for credit note" class="form-input-base">
                            <datalist id="cn-reasons">
                                @foreach ($reasons as $r)<option value="{{ $r }}">@endforeach
                            </datalist>
                            @error('reason') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Line items --}}
                <div class="card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Line Items</h3>
                    </div>

                    @error('items') <p class="mb-2 text-xs text-red-500">{{ $message }}</p> @enderror

                    <div class="space-y-3">
                        @foreach ($items as $index => $item)
                            <div wire:key="cn-item-{{ $index }}" class="rounded-lg border border-gray-200 p-3 dark:border-ink-600">
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

                {{-- Notes --}}
                <div class="card p-5">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" rows="3" class="form-input-base text-sm" placeholder="Internal notes (optional)"></textarea>
                </div>
            </div>

            {{-- Right: totals --}}
            <div class="space-y-6">
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
                        <div class="flex justify-between border-t border-gray-200 pt-3 dark:border-ink-600">
                            <span class="font-semibold text-gray-900 dark:text-white">Credit Total</span>
                            <span class="text-lg font-semibold text-red-500">{{ money($this->total) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
