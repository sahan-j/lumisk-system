<div>
    <a href="{{ route('admin.products.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Products
    </a>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        @if ($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="" class="h-20 w-20 rounded-lg object-cover">
                        @else
                            <span class="flex h-20 w-20 items-center justify-center rounded-lg bg-brand-purple/10 text-brand-purple">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </span>
                        @endif
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $product->name }}</h2>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                @if ($product->sku)<span class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-500 dark:bg-ink-700">{{ $product->sku }}</span>@endif
                                <x-status-badge :color="$product->type === 'service' ? 'blue' : 'gray'" :label="ucfirst($product->type)" />
                                @if ($product->category)<span class="inline-flex items-center gap-1.5 text-xs text-gray-500"><span class="h-2 w-2 rounded-full" style="background: {{ $product->category->color }}"></span>{{ $product->category->name }}</span>@endif
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium text-white" style="background: {{ $product->stock_status_color }}">{{ $product->stock_status_label }}</span>
                            </div>
                            @if ($product->description)<p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $product->description }}</p>@endif
                        </div>
                    </div>
                    @permission('products.edit')
                    <a href="{{ route('admin.products.edit', $product) }}" wire:navigate class="btn-secondary shrink-0 !py-1.5 text-sm">Edit</a>
                    @endpermission
                </div>

                {{-- Pricing --}}
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-ink-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Sale Price</p>
                        <p class="mt-1 font-mono text-xl font-bold text-brand-purple">{{ \App\Helpers\CurrencyHelper::format($product->sale_price, $product->currency_code) }}</p>
                        <p class="text-xs text-gray-400">per {{ $product->unit }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-ink-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Cost Price</p>
                        <p class="mt-1 font-mono text-xl font-bold text-gray-900 dark:text-white">{{ $product->purchase_cost !== null ? \App\Helpers\CurrencyHelper::format($product->purchase_cost, $product->currency_code) : '—' }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-ink-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Profit Margin</p>
                        <p class="mt-1 font-mono text-xl font-bold {{ $product->profit_margin === null ? 'text-gray-400' : ($product->profit_margin > 30 ? 'text-green-600 dark:text-green-400' : ($product->profit_margin > 10 ? 'text-amber-500' : 'text-red-500')) }}">{{ $product->profit_margin !== null ? $product->profit_margin . '%' : '—' }}</p>
                        @if ($product->purchase_cost !== null)<p class="text-xs text-gray-400">{{ \App\Helpers\CurrencyHelper::format($product->profit_amount, $product->currency_code) }} / {{ $product->unit }}</p>@endif
                    </div>
                </div>
            </div>

            {{-- Stock movements --}}
            @if ($product->track_inventory)
                <div class="card p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Stock Movements</h3>
                        <span class="text-sm">Current: <span class="font-mono font-semibold" style="color: {{ $product->stock_status_color }}">{{ rtrim(rtrim(number_format($product->stock_quantity, 2), '0'), '.') }} {{ $product->unit }}</span></span>
                    </div>
                    @if ($product->movements->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-ink-600">
                                <thead><tr class="table-head"><th class="px-3 py-2">Date</th><th class="px-3 py-2">Type</th><th class="px-3 py-2 text-right">Change</th><th class="px-3 py-2 text-right">After</th><th class="px-3 py-2">Reference</th></tr></thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                                    @foreach ($product->movements as $m)
                                        <tr wire:key="mv-{{ $m->id }}">
                                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $m->created_at->format('M d, Y') }}</td>
                                            <td class="px-3 py-2">{{ $m->type_label }}</td>
                                            <td class="px-3 py-2 text-right font-mono font-medium {{ $m->is_inbound ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">{{ $m->is_inbound ? '+' : '' }}{{ rtrim(rtrim(number_format($m->quantity, 2), '0'), '.') }}</td>
                                            <td class="px-3 py-2 text-right font-mono text-gray-700 dark:text-gray-300">{{ rtrim(rtrim(number_format($m->quantity_after, 2), '0'), '.') }}</td>
                                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $m->reference_label ?: ($m->notes ?: '—') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="py-4 text-center text-sm text-gray-400">No stock movements yet.</p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Right --}}
        <div class="space-y-6">
            <div class="card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">SKU</dt><dd class="font-mono text-gray-900 dark:text-white">{{ $product->sku ?: '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Unit</dt><dd class="text-gray-900 dark:text-white">{{ $product->unit }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Tax Rate</dt><dd class="text-gray-900 dark:text-white">{{ rtrim(rtrim(number_format($product->tax_rate, 2), '0'), '.') }}%</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd>{{ $product->is_active ? 'Active' : 'Inactive' }}</dd></div>
                    @if ($product->track_inventory && $product->low_stock_threshold !== null)
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Low Stock At</dt><dd class="text-gray-900 dark:text-white">{{ $product->low_stock_threshold }} {{ $product->unit }}</dd></div>
                    @endif
                </dl>
            </div>

            {{-- Sales history --}}
            <div class="card p-6">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Sales</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div><p class="text-xs text-gray-400">Units Sold</p><p class="text-lg font-semibold text-gray-900 dark:text-white">{{ rtrim(rtrim(number_format($unitsSold, 2), '0'), '.') }}</p></div>
                    <div><p class="text-xs text-gray-400">Revenue</p><p class="text-lg font-semibold text-gray-900 dark:text-white">{{ money($revenue) }}</p></div>
                </div>
            </div>

            {{-- Adjust stock --}}
            @permission('inventory.adjust')
            @if ($product->track_inventory)
                <div class="card p-6">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Adjust Stock</h3>
                    <div class="space-y-3">
                        <select wire:model="adjustType" class="form-input-base">
                            <option value="purchase">📦 Purchase (add)</option>
                            <option value="return">↩️ Return (add)</option>
                            <option value="adjustment">✏️ Manual adjustment</option>
                        </select>
                        <div>
                            <input wire:model="adjustQuantity" type="number" step="0.01" class="form-input-base" placeholder="Quantity (negative to deduct)">
                            @error('adjustQuantity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <input wire:model="adjustNotes" type="text" class="form-input-base" placeholder="Notes (optional)">
                        <button wire:click="adjustStock" class="btn-primary w-full justify-center">Update Stock</button>
                    </div>
                </div>
            @endif
            @endpermission
        </div>
    </div>
</div>
