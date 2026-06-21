<?php

namespace App\Livewire\Admin\Products;

use App\Models\InvoiceItem;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Product')]
class ProductShow extends Component
{
    public Product $product;

    // Adjust-stock form
    public string $adjustType = 'purchase';
    public ?float $adjustQuantity = null;
    public ?string $adjustNotes = null;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function adjustStock(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('inventory.adjust'), 403);

        if (! $this->product->track_inventory) {
            return;
        }

        $validated = $this->validate([
            'adjustType' => ['required', 'in:purchase,return,adjustment'],
            'adjustQuantity' => ['required', 'numeric', 'not_in:0'],
            'adjustNotes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->product->adjustStock(
            quantity: (float) $validated['adjustQuantity'],
            type: $validated['adjustType'],
            notes: $validated['adjustNotes'] ?? '',
        );
        $this->product->refresh();

        $this->reset('adjustQuantity', 'adjustNotes');
        $this->adjustType = 'purchase';
        $this->dispatch('toast', type: 'success', message: 'Stock updated. New quantity: ' . rtrim(rtrim(number_format($this->product->stock_quantity, 2), '0'), '.'));
    }

    public function render()
    {
        $this->product->load(['category', 'movements' => fn ($q) => $q->limit(50)]);

        // Sales totals from invoice line items linked to this product.
        $soldAgg = InvoiceItem::where('product_id', $this->product->id)
            ->selectRaw('COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(total),0) as revenue')
            ->first();

        return view('livewire.admin.products.product-show', [
            'unitsSold' => (float) ($soldAgg->qty ?? 0),
            'revenue' => (float) ($soldAgg->revenue ?? 0),
        ]);
    }
}
