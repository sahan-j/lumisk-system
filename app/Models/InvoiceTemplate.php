<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class InvoiceTemplate extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = ['invoice', 'estimate', 'both'];

    protected $fillable = [
        'name',
        'description',
        'type',
        'tax_rate',
        'discount_amount',
        'notes',
        'terms',
        'currency_code',
        'is_active',
        'usage_count',
        'created_by',
    ];

    protected $appends = ['subtotal', 'total', 'type_label'];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'usage_count' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceTemplateItem::class, 'template_id')->orderBy('sort_order');
    }

    public function getSubtotalAttribute(): float
    {
        return round((float) $this->items->sum('total'), 2);
    }

    public function getTotalAttribute(): float
    {
        $subtotal = $this->subtotal;
        $tax = $subtotal * ((float) $this->tax_rate / 100);

        return round(max($subtotal + $tax - (float) $this->discount_amount, 0), 2);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'invoice' => 'Invoice only',
            'estimate' => 'Estimate only',
            default => 'Invoice & Estimate',
        };
    }
}
