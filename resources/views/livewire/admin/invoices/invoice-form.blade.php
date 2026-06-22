<div>
    <form wire:submit="save">
        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('admin.invoices.index') }}" class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    Invoices
                </a>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $invoice ? 'Edit ' . $invoice->invoice_number : 'New Invoice' }}
                </h2>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.invoices.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <span wire:loading.remove wire:target="save">Save Invoice</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>

        {{-- Load from template (new invoices only) --}}
        @if ($templates->isNotEmpty())
            <div class="card mb-6 flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label class="form-label flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-brand-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        Start from a template
                    </label>
                    <select wire:model="selectedTemplate" class="form-input-base">
                        <option value="">Select a template…</option>
                        @foreach ($templates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->items->count() }} items · {{ $tpl->currency_code }} {{ number_format($tpl->total, 0) }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" wire:click="loadTemplate" wire:confirm="Load this template? Current line items will be replaced." class="btn-primary">
                    Load Template
                </button>
            </div>
        @endif

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
                            <label class="form-label">Due Date</label>
                            <input wire:model="due_date" type="date" class="form-input-base">
                        </div>
                        <div>
                            <label class="form-label">Currency</label>
                            <select wire:model.live="currencyCode" class="form-input-base">
                                @foreach (\App\Helpers\CurrencyHelper::getActiveCurrencies() as $currency)
                                    <option value="{{ $currency->code }}">{{ $currency->symbol }} {{ $currency->code }} — {{ $currency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($currencyCode !== 'LKR')
                            <div>
                                <label class="form-label">Exchange Rate <span class="text-xs font-normal text-gray-400">(1 {{ $currencyCode }} = ? LKR)</span></label>
                                <input wire:model.live.debounce.400ms="exchangeRate" type="number" step="0.0001" min="0" class="form-input-base">
                                <p class="mt-1 text-xs text-gray-400">≈ {{ money($this->totalLkr) }} total</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Line items --}}
                <div class="card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Line Items</h3>
                        <div class="flex gap-2">
                            <button type="button" wire:click="$set('showProducts', true)" class="btn-secondary !py-1.5 text-xs">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                Add from Products
                            </button>
                            <button type="button" wire:click="$set('showSavedItems', true)" class="btn-secondary !py-1.5 text-xs">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" /></svg>
                                Add from Saved Items
                            </button>
                        </div>
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

                {{-- Recurring Settings --}}
                <div class="card p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" wire:model.live="isRecurring"
                                   class="rounded border-gray-300 text-brand-purple focus:ring-brand-purple dark:border-ink-600 dark:bg-ink-800"
                                   style="width:16px;height:16px;">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-brand-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Recurring Invoice
                            </span>
                        </label>
                        @if($isRecurring)
                            <span class="text-xs bg-brand-purple/10 text-brand-purple px-2 py-0.5 rounded-full font-medium">
                                Auto-generates new invoices on schedule
                            </span>
                        @endif
                    </div>

                    @if($isRecurring)
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="form-label">Billing Cycle</label>
                                <select wire:model="recurringCycle" class="form-input-base">
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly (3 months)</option>
                                    <option value="semi_annual">Semi-Annual (6 months)</option>
                                    <option value="annual">Annual</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">First / Next Invoice Date</label>
                                <input wire:model="recurringNextDate" type="date" class="form-input-base">
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">New invoice auto-generates on this date</p>
                            </div>
                            <div>
                                <label class="form-label">End Date <span class="font-normal text-gray-400">(optional)</span></label>
                                <input wire:model="recurringEndDate" type="date" class="form-input-base">
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Leave blank to repeat forever</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: totals & status --}}
            <div class="space-y-6">
                <div class="card p-5">
                    <label class="form-label">Status</label>
                    <select wire:model="status" class="form-input-base capitalize">
                        @foreach (\App\Models\Invoice::STATUSES as $s)
                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="card p-5">
                    <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Summary</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $currencySymbol }} {{ number_format($this->subtotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 dark:text-gray-400">Tax %</span>
                            <input wire:model.live.debounce.400ms="tax_rate" type="number" step="0.01" min="0" max="100" class="form-input-base !w-24 !py-1 text-right text-sm">
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Tax amount</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $currencySymbol }} {{ number_format($this->taxAmount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 dark:text-gray-400">Discount</span>
                            <input wire:model.live.debounce.400ms="discount_amount" type="number" step="0.01" min="0" class="form-input-base !w-24 !py-1 text-right text-sm">
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-3 dark:border-ink-600">
                            <span class="font-semibold text-gray-900 dark:text-white">Total</span>
                            <span class="text-lg font-semibold text-gold">{{ $currencySymbol }} {{ number_format($this->total, 2) }}</span>
                        </div>
                        @if ($currencyCode !== 'LKR')
                            <div class="flex justify-between text-xs text-gray-400">
                                <span>LKR equivalent</span>
                                <span>≈ {{ money($this->totalLkr) }}</span>
                            </div>
                        @endif
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

    {{-- Product picker --}}
    @if ($showProducts)
        <x-app-modal title="Add from Products" close="$set('showProducts', false)" max-width="sm:max-w-lg">
            <div class="relative mb-3">
                <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input wire:model.live.debounce.300ms="productSearch" type="text" placeholder="Search products by name or SKU…" class="form-input-base pl-10" autofocus>
            </div>
            <div class="max-h-96 divide-y divide-gray-100 overflow-y-auto dark:divide-ink-700">
                @forelse ($products as $product)
                    <button type="button" wire:click="addProduct({{ $product->id }})" @class(['flex w-full items-center justify-between gap-3 px-1 py-3 text-left hover:bg-gray-50 dark:hover:bg-ink-800', 'opacity-50' => $product->is_out_of_stock])>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $product->sku ? 'SKU: ' . $product->sku : $product->description }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="text-sm font-medium text-gold">{{ \App\Helpers\CurrencyHelper::format($product->sale_price, $product->currency_code) }}</span>
                            @if ($product->track_inventory)
                                <span class="block text-[10px] font-medium" style="color: {{ $product->stock_status_color }}">{{ rtrim(rtrim(number_format($product->stock_quantity, 2), '0'), '.') }} in stock</span>
                            @endif
                        </div>
                    </button>
                @empty
                    <p class="py-8 text-center text-sm text-gray-400">{{ $productSearch ? 'No products found.' : 'No products yet.' }}</p>
                @endforelse
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" wire:click="$set('showProducts', false)" class="btn-secondary">Done</button>
            </div>
        </x-app-modal>
    @endif
</div>
