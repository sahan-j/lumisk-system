<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Products & Inventory')]
class ProductsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $type = '';

    #[Url(as: 'stock')]
    public string $stockStatus = '';

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    // Category manager modal
    public bool $managingCategories = false;
    public ?int $categoryId = null;
    public string $categoryName = '';
    public string $categoryColor = '#6d5cff';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'category', 'type', 'stockStatus'])) {
            $this->resetPage();
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('products.delete'), 403);

        if ($this->deleteId) {
            Product::find($this->deleteId)?->delete();
            $this->dispatch('toast', type: 'success', message: 'Product deleted.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    // ---- Category management ----

    public function openCategories(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('products.edit'), 403);
        $this->resetCategoryForm();
        $this->managingCategories = true;
    }

    public function resetCategoryForm(): void
    {
        $this->categoryId = null;
        $this->categoryName = '';
        $this->categoryColor = '#6d5cff';
        $this->resetValidation();
    }

    public function editCategory(int $id): void
    {
        $cat = ProductCategory::find($id);
        if ($cat) {
            $this->categoryId = $cat->id;
            $this->categoryName = $cat->name;
            $this->categoryColor = $cat->color;
        }
    }

    public function saveCategory(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('products.edit'), 403);

        $validated = $this->validate([
            'categoryName' => ['required', 'string', 'max:255'],
            'categoryColor' => ['required', 'string', 'max:20'],
        ]);

        ProductCategory::updateOrCreate(
            ['id' => $this->categoryId],
            ['name' => $validated['categoryName'], 'color' => $validated['categoryColor']]
        );

        $this->resetCategoryForm();
        $this->dispatch('toast', type: 'success', message: 'Category saved.');
    }

    public function deleteCategory(int $id): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('products.edit'), 403);

        $cat = ProductCategory::withCount('products')->find($id);
        if (! $cat) {
            return;
        }
        if ($cat->products_count > 0) {
            $this->dispatch('toast', type: 'error', message: 'Cannot delete a category with products.');

            return;
        }
        $cat->delete();
        $this->dispatch('toast', type: 'success', message: 'Category deleted.');
    }

    public function render()
    {
        $products = Product::with('category')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%");
                });
            })
            ->when($this->category !== '', fn ($q) => $q->where('category_id', $this->category))
            ->when($this->type !== '', fn ($q) => $q->where('type', $this->type))
            ->when($this->stockStatus === 'low', fn ($q) => $q->where('track_inventory', true)
                ->whereNotNull('low_stock_threshold')->whereColumn('stock_quantity', '<=', 'low_stock_threshold'))
            ->when($this->stockStatus === 'out', fn ($q) => $q->where('track_inventory', true)->where('stock_quantity', '<=', 0))
            ->when($this->stockStatus === 'in', fn ($q) => $q->where('track_inventory', true)->where('stock_quantity', '>', 0))
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low_stock' => Product::where('track_inventory', true)
                ->whereNotNull('low_stock_threshold')
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'out_of_stock' => Product::where('track_inventory', true)->where('stock_quantity', '<=', 0)->count(),
            'total_inventory_value' => round((float) Product::where('track_inventory', true)
                ->get()->sum(fn ($p) => (float) $p->stock_quantity * (float) ($p->purchase_cost ?? 0)), 2),
        ];

        return view('livewire.admin.products.products-index', [
            'products' => $products,
            'stats' => $stats,
            'categories' => ProductCategory::withCount('products')->orderBy('name')->get(),
            'types' => Product::TYPES,
        ]);
    }
}
