<div>
    <form wire:submit="save">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('admin.products.index') }}" wire:navigate class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    Products
                </a>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $product ? 'Edit ' . $product->name : 'New Product' }}</h2>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.products.index') }}" wire:navigate class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <span wire:loading.remove wire:target="save">Save Product</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Left --}}
            <div class="space-y-6 lg:col-span-2">
                <div class="card p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="form-label">Name <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" class="form-input-base" placeholder="e.g. Website Development">
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">SKU</label>
                            <div class="flex gap-2">
                                <input wire:model="sku" type="text" class="form-input-base" placeholder="Optional stock code">
                                <button type="button" wire:click="generateSku" class="btn-secondary shrink-0 !py-1.5 text-xs">Generate</button>
                            </div>
                            @error('sku') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Description</label>
                            <textarea wire:model="description" rows="2" class="form-input-base"></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Type</label>
                            <div class="flex gap-2">
                                <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border-2 p-3 text-sm {{ $type === 'product' ? 'border-brand-purple bg-brand-purple/5' : 'border-gray-200 dark:border-ink-600' }}">
                                    <input wire:model.live="type" type="radio" value="product" class="text-gold focus:ring-gold"> Physical Product
                                </label>
                                <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border-2 p-3 text-sm {{ $type === 'service' ? 'border-brand-purple bg-brand-purple/5' : 'border-gray-200 dark:border-ink-600' }}">
                                    <input wire:model.live="type" type="radio" value="service" class="text-gold focus:ring-gold"> Service
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="card p-6">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Pricing</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">Sale Price <span class="text-red-500">*</span></label>
                            <input wire:model="sale_price" type="number" step="0.01" min="0" class="form-input-base">
                            @error('sale_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Purchase Cost</label>
                            <input wire:model="purchase_cost" type="number" step="0.01" min="0" class="form-input-base" placeholder="For margin tracking">
                        </div>
                        <div>
                            <label class="form-label">Unit</label>
                            <select wire:model="unit" class="form-input-base">
                                @foreach ($units as $u)<option value="{{ $u }}">{{ $u }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Tax Rate %</label>
                            <input wire:model="tax_rate" type="number" step="0.01" min="0" max="100" class="form-input-base">
                        </div>
                        <div>
                            <label class="form-label">Currency</label>
                            <select wire:model="currency_code" class="form-input-base">
                                @foreach ($currencies as $currency)<option value="{{ $currency->code }}">{{ $currency->symbol }} {{ $currency->code }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Inventory --}}
                @if ($type !== 'service')
                    <div class="card p-6">
                        <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Inventory</h3>
                        <label class="flex cursor-pointer items-start gap-3 py-2">
                            <input wire:model.live="track_inventory" type="checkbox" class="mt-0.5 rounded border-gray-300 text-gold focus:ring-gold dark:border-ink-600 dark:bg-ink-800">
                            <span>
                                <span class="block text-sm font-medium text-gray-700 dark:text-gray-200">Track inventory</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Deduct stock when this product is sold on an invoice.</span>
                            </span>
                        </label>
                        @if ($track_inventory)
                            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="form-label">{{ $product ? 'Current Stock' : 'Opening Stock' }}</label>
                                    <input wire:model="stock_quantity" type="number" step="0.01" class="form-input-base" @disabled($product)>
                                    @if ($product)<p class="mt-1 text-xs text-gray-400">Use "Adjust Stock" on the product page to change stock.</p>@endif
                                </div>
                                <div>
                                    <label class="form-label">Low Stock Threshold</label>
                                    <input wire:model="low_stock_threshold" type="number" min="0" class="form-input-base" placeholder="Alert below this level">
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Right --}}
            <div class="space-y-6">
                <div class="card p-6">
                    <label class="form-label">Category</label>
                    <select wire:model="category_id" class="form-input-base">
                        <option value="">Uncategorised</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="card p-6">
                    <label class="flex cursor-pointer items-center gap-3">
                        <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-gold focus:ring-gold dark:border-ink-600 dark:bg-ink-800">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Active</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-400">Inactive products don't appear in the invoice product picker.</p>
                </div>
                <div class="card p-6">
                    <label class="form-label">Image</label>
                    @if ($product?->image)
                        <img src="{{ Storage::url($product->image) }}" alt="" class="mb-2 h-24 w-24 rounded-lg object-cover">
                    @endif
                    <input wire:model="image" type="file" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-sm dark:file:bg-ink-700 dark:file:text-gray-200">
                    @error('image') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="image" class="mt-1 text-xs text-brand-purple">Uploading…</div>
                </div>
                <div class="card p-6">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" rows="3" class="form-input-base" placeholder="Internal notes…"></textarea>
                </div>
            </div>
        </div>
    </form>
</div>
