<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use SoftDeletes;

    public const TYPES = ['product', 'service'];
    public const UNITS = ['unit', 'hour', 'day', 'month', 'kg', 'litre', 'item', 'piece'];

    protected $fillable = [
        'sku',
        'name',
        'description',
        'type',
        'category_id',
        'unit',
        'sale_price',
        'purchase_cost',
        'tax_rate',
        'currency_code',
        'track_inventory',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
        'notes',
        'image',
    ];

    protected $appends = [
        'profit_margin', 'profit_amount', 'is_low_stock', 'is_out_of_stock',
        'stock_status', 'stock_status_color', 'stock_status_label',
    ];

    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:2',
            'purchase_cost' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'stock_quantity' => 'decimal:2',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function estimateItems(): HasMany
    {
        return $this->hasMany(EstimateItem::class);
    }

    public function getProfitMarginAttribute(): ?float
    {
        if (! $this->purchase_cost || (float) $this->sale_price <= 0) {
            return null;
        }

        return round((((float) $this->sale_price - (float) $this->purchase_cost) / (float) $this->sale_price) * 100, 1);
    }

    public function getProfitAmountAttribute(): float
    {
        return round((float) $this->sale_price - (float) ($this->purchase_cost ?? 0), 2);
    }

    public function getIsLowStockAttribute(): bool
    {
        if (! $this->track_inventory || $this->low_stock_threshold === null) {
            return false;
        }

        return (float) $this->stock_quantity <= (float) $this->low_stock_threshold;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->track_inventory && (float) $this->stock_quantity <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if (! $this->track_inventory) {
            return 'service';
        }
        if ($this->is_out_of_stock) {
            return 'out_of_stock';
        }
        if ($this->is_low_stock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getStockStatusColorAttribute(): string
    {
        return match ($this->stock_status) {
            'service' => '#6d5cff',
            'in_stock' => '#10b981',
            'low_stock' => '#f59e0b',
            'out_of_stock' => '#ef4444',
            default => '#94a3b8',
        };
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'service' => 'Service',
            'in_stock' => 'In Stock',
            'low_stock' => 'Low Stock',
            'out_of_stock' => 'Out of Stock',
            default => ucfirst($this->stock_status),
        };
    }

    /**
     * Record an inventory change and update the stock level. Positive quantity
     * adds stock, negative deducts. No-op for non-tracked products.
     */
    public function adjustStock(
        float $quantity,
        string $type = 'adjustment',
        string $notes = '',
        ?string $refType = null,
        ?int $refId = null,
        ?string $refLabel = null,
        ?float $unitCost = null,
    ): ?StockMovement {
        if (! $this->track_inventory) {
            return null;
        }

        $before = (float) $this->stock_quantity;
        $after = round($before + $quantity, 2);

        $movement = StockMovement::create([
            'product_id' => $this->id,
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'reference_label' => $refLabel,
            'unit_cost' => $unitCost,
            'notes' => $notes,
            'created_by' => auth()->user()->name ?? 'System',
        ]);

        $this->update(['stock_quantity' => $after]);

        return $movement;
    }
}
