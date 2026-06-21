<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Product')]
class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public ?string $sku = null;
    public string $name = '';
    public ?string $description = null;
    public string $type = 'product';
    public ?int $category_id = null;
    public string $unit = 'unit';
    public ?float $sale_price = 0;
    public ?float $purchase_cost = null;
    public ?float $tax_rate = 0;
    public string $currency_code = 'LKR';
    public bool $track_inventory = false;
    public ?float $stock_quantity = 0;
    public ?int $low_stock_threshold = null;
    public bool $is_active = true;
    public ?string $notes = null;
    public $image = null; // new upload

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->product = $product;
            $this->sku = $product->sku;
            $this->name = $product->name;
            $this->description = $product->description;
            $this->type = $product->type;
            $this->category_id = $product->category_id;
            $this->unit = $product->unit;
            $this->sale_price = (float) $product->sale_price;
            $this->purchase_cost = $product->purchase_cost !== null ? (float) $product->purchase_cost : null;
            $this->tax_rate = (float) $product->tax_rate;
            $this->currency_code = $product->currency_code ?: 'LKR';
            $this->track_inventory = (bool) $product->track_inventory;
            $this->stock_quantity = (float) $product->stock_quantity;
            $this->low_stock_threshold = $product->low_stock_threshold;
            $this->is_active = (bool) $product->is_active;
            $this->notes = $product->notes;

            return;
        }

        $this->tax_rate = (float) company_settings()->default_tax_rate;
        $this->currency_code = company_settings()->currency ?: 'LKR';
    }

    /** Generate a SKU from the product name. */
    public function generateSku(): void
    {
        $base = Str::upper(Str::slug(Str::limit($this->name ?: 'PRD', 12, ''), ''));
        $base = $base ?: 'PRD';
        $this->sku = $base . '-' . Str::upper(Str::random(4));
    }

    public function updatedType($value): void
    {
        if ($value === 'service') {
            $this->track_inventory = false;
        }
    }

    protected function rules(): array
    {
        return [
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($this->product?->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:' . implode(',', Product::TYPES)],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'unit' => ['required', 'string', 'max:30'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'currency_code' => ['required', 'string', 'max:3'],
            'track_inventory' => ['boolean'],
            'stock_quantity' => ['nullable', 'numeric'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function save()
    {
        abort_unless((bool) auth()->user()?->hasPermission($this->product ? 'products.edit' : 'products.create'), 403);

        $validated = $this->validate();

        $data = [
            'sku' => $validated['sku'] ?: null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'category_id' => $validated['category_id'] ?? null,
            'unit' => $validated['unit'],
            'sale_price' => $validated['sale_price'],
            'purchase_cost' => $validated['purchase_cost'] ?? null,
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'currency_code' => $validated['currency_code'] ?: 'LKR',
            'track_inventory' => $this->type === 'service' ? false : $this->track_inventory,
            'low_stock_threshold' => $this->track_inventory ? $this->low_stock_threshold : null,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('products', 'public');
        }

        if ($this->product) {
            $this->product->update($data);
            $product = $this->product;
        } else {
            // Opening stock is recorded as a movement so history is complete.
            $opening = (float) ($this->track_inventory ? ($this->stock_quantity ?? 0) : 0);
            $data['stock_quantity'] = 0;
            $product = Product::create($data);

            if ($product->track_inventory && $opening != 0) {
                $product->adjustStock($opening, 'opening', 'Opening stock');
            }
        }

        $this->dispatch('toast', type: 'success', message: 'Product saved.');

        return $this->redirect(route('admin.products.show', $product), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.products.product-form', [
            'categories' => ProductCategory::orderBy('name')->get(),
            'units' => Product::UNITS,
            'currencies' => \App\Helpers\CurrencyHelper::getActiveCurrencies(),
        ]);
    }
}
