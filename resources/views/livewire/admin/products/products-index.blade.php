<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Products &amp; Inventory</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Catalog of products and services with stock tracking.</p>
        </div>
        <div class="flex items-center gap-2">
            @permission('products.edit')
            <button wire:click="openCategories" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.41.59l7 7a2 2 0 010 2.82l-5 5a2 2 0 01-2.82 0l-7-7A2 2 0 014 9V4a1 1 0 011-1z" /></svg>
                Categories
            </button>
            @endpermission
            @permission('products.create')
            <a href="{{ route('admin.products.create') }}" wire:navigate class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Add Product
            </a>
            @endpermission
        </div>
    </div>

    {{-- Stock alert banner --}}
    @if ($stats['low_stock'] > 0 || $stats['out_of_stock'] > 0)
        <div class="mb-5 flex items-center gap-3 rounded-lg border border-amber-200 border-l-4 border-l-amber-500 bg-amber-50 p-4 dark:border-amber-900/40 dark:bg-amber-900/10">
            <svg class="h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.33 16a2 2 0 001.74 3z" /></svg>
            <div>
                <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Stock Alert</p>
                <p class="text-xs text-amber-700 dark:text-amber-400">{{ $stats['out_of_stock'] }} out of stock, {{ $stats['low_stock'] }} running low.</p>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card border-t-[3px] border-t-brand-purple p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Products</span>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            <span class="text-xs text-gray-400">{{ $stats['active'] }} active</span>
        </div>
        <div class="card border-t-[3px] border-t-amber-500 p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Low Stock</span>
            <p class="mt-1 text-2xl font-semibold {{ $stats['low_stock'] > 0 ? 'text-amber-500' : 'text-gray-400' }}">{{ $stats['low_stock'] }}</p>
            <span class="text-xs text-gray-400">items to reorder</span>
        </div>
        <div class="card border-t-[3px] border-t-red-500 p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Out of Stock</span>
            <p class="mt-1 text-2xl font-semibold {{ $stats['out_of_stock'] > 0 ? 'text-red-500' : 'text-gray-400' }}">{{ $stats['out_of_stock'] }}</p>
            <span class="text-xs text-gray-400">need restocking</span>
        </div>
        <div class="card border-t-[3px] border-t-green-500 p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Inventory Value</span>
            <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ money($stats['total_inventory_value']) }}</p>
            <span class="text-xs text-gray-400">at cost price</span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search name or SKU…" class="form-input-base pl-10">
        </div>
        <select wire:model.live="category" class="form-input-base">
            <option value="">All categories</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        <select wire:model.live="type" class="form-input-base">
            <option value="">All types</option>
            @foreach ($types as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
        </select>
        <select wire:model.live="stockStatus" class="form-input-base">
            <option value="">All stock</option>
            <option value="in">In stock</option>
            <option value="low">Low stock</option>
            <option value="out">Out of stock</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">SKU</th>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3 text-right">Sale Price</th>
                        <th class="px-5 py-3 text-right">Stock</th>
                        <th class="px-5 py-3 text-right">Margin</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($products as $product)
                        <tr wire:key="prod-{{ $product->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ $product->sku ?: '—' }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.products.show', $product) }}" wire:navigate class="text-sm font-medium text-brand-purple hover:underline">{{ $product->name }}</a>
                                @unless ($product->is_active)<span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-500 dark:bg-ink-700">inactive</span>@endunless
                            </td>
                            <td class="px-5 py-3 text-sm">
                                @if ($product->category)
                                    <span class="inline-flex items-center gap-1.5 text-gray-700 dark:text-gray-300"><span class="h-2 w-2 rounded-full" style="background: {{ $product->category->color }}"></span>{{ $product->category->name }}</span>
                                @else<span class="text-gray-400">—</span>@endif
                            </td>
                            <td class="px-5 py-3"><x-status-badge :color="$product->type === 'service' ? 'blue' : 'gray'" :label="ucfirst($product->type)" /></td>
                            <td class="px-5 py-3 text-right font-mono text-sm text-gray-900 dark:text-white">{{ \App\Helpers\CurrencyHelper::format($product->sale_price, $product->currency_code) }}</td>
                            <td class="px-5 py-3 text-right text-sm">
                                @if (! $product->track_inventory)
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @else
                                    <span class="font-medium" style="color: {{ $product->stock_status_color }}">{{ rtrim(rtrim(number_format($product->stock_quantity, 2), '0'), '.') }} {{ $product->unit }}</span>
                                    @if ($product->is_low_stock && ! $product->is_out_of_stock)<span title="Low stock">⚠️</span>@endif
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right text-sm">
                                @if ($product->profit_margin !== null)
                                    <span class="font-medium {{ $product->profit_margin > 30 ? 'text-green-600 dark:text-green-400' : ($product->profit_margin > 10 ? 'text-amber-500' : 'text-red-500') }}">{{ $product->profit_margin }}%</span>
                                @else<span class="text-gray-300 dark:text-gray-600">—</span>@endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.products.show', $product) }}" wire:navigate class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="View">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </a>
                                    @permission('products.edit')
                                    <a href="{{ route('admin.products.edit', $product) }}" wire:navigate class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                    @endpermission
                                    @permission('products.delete')
                                    <button wire:click="confirmDelete({{ $product->id }})" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400">
                            No products yet.
                            @permission('products.create')<a href="{{ route('admin.products.create') }}" wire:navigate class="ml-1 text-brand-purple hover:underline">Add your first product →</a>@endpermission
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>

    @if ($confirmingDelete)
        <x-app-modal title="Delete product?" close="$set('confirmingDelete', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">This soft-deletes the product. Past invoices keep their line items. This can't be undone from the UI.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </x-app-modal>
    @endif

    {{-- Category manager --}}
    @if ($managingCategories)
        <x-app-modal title="Product Categories" close="$set('managingCategories', false)" max-width="sm:max-w-xl">
            <div class="space-y-4">
                @if ($categories->isNotEmpty())
                    <div class="divide-y divide-gray-100 rounded-lg border border-gray-200 dark:divide-ink-700 dark:border-ink-600">
                        @foreach ($categories as $cat)
                            <div wire:key="cat-{{ $cat->id }}" class="flex items-center justify-between px-3 py-2 text-sm">
                                <span class="inline-flex items-center gap-2 font-medium text-gray-900 dark:text-white"><span class="h-3 w-3 rounded-full" style="background: {{ $cat->color }}"></span>{{ $cat->name }} <span class="text-xs font-normal text-gray-400">· {{ $cat->products_count }}</span></span>
                                <div class="flex items-center gap-1">
                                    <button wire:click="editCategory({{ $cat->id }})" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="deleteCategory({{ $cat->id }})" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-ink-600 dark:bg-ink-800">
                    <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $categoryId ? 'Edit Category' : 'Add Category' }}</h4>
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="form-label">Name</label>
                            <input wire:model="categoryName" type="text" class="form-input-base" placeholder="e.g. Hardware">
                            @error('categoryName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Color</label>
                            <input wire:model="categoryColor" type="color" class="h-10 w-16 rounded-lg border border-gray-300 dark:border-ink-600">
                        </div>
                        <button wire:click="saveCategory" class="btn-primary">{{ $categoryId ? 'Update' : 'Add' }}</button>
                        @if ($categoryId)<button wire:click="resetCategoryForm" class="btn-secondary">Cancel</button>@endif
                    </div>
                </div>
            </div>
        </x-app-modal>
    @endif
</div>
