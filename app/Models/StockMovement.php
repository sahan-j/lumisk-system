<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'reference_label',
        'unit_cost',
        'notes',
        'created_by',
    ];

    protected $appends = ['type_label', 'is_inbound'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'quantity_before' => 'decimal:2',
            'quantity_after' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'purchase' => '📦 Purchase',
            'sale' => '🛒 Sale',
            'adjustment' => '✏️ Adjustment',
            'return' => '↩️ Return',
            'opening' => '🔓 Opening Stock',
            default => ucfirst($this->type),
        };
    }

    public function getIsInboundAttribute(): bool
    {
        return (float) $this->quantity > 0;
    }
}
