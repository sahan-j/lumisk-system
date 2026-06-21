<?php

namespace App\Models;

use App\Traits\HasAttachments;
use App\Traits\HasCurrency;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    use HasFactory, SoftDeletes, HasAttachments, HasNotes, HasCurrency;

    public const STATUSES = ['draft', 'sent', 'accepted', 'rejected', 'expired'];

    protected $fillable = [
        'estimate_number',
        'client_id',
        'status',
        'converted_from',
        'currency_code',
        'exchange_rate',
        'issue_date',
        'expiry_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total',
        'total_lkr',
        'notes',
        'terms',
        'client_note',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'total_lkr' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class)->orderBy('order');
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items->sum(fn ($item) => (float) $item->quantity * (float) $item->unit_price);
        $taxAmount = round($subtotal * ((float) $this->tax_rate / 100), 2);
        $total = $subtotal + $taxAmount - (float) $this->discount_amount;

        $this->subtotal = round($subtotal, 2);
        $this->tax_amount = $taxAmount;
        $this->total = round(max($total, 0), 2);
        $this->total_lkr = round($this->total * (float) ($this->exchange_rate ?: 1), 2);
    }

    /** Recompute only the LKR equivalent from the current total + exchange rate. */
    public function calculateLkrTotal(): void
    {
        $this->update(['total_lkr' => round((float) $this->total * (float) ($this->exchange_rate ?: 1), 2)]);
    }

    public function isExpired(): bool
    {
        return $this->status !== 'accepted'
            && $this->expiry_date
            && $this->expiry_date->isPast();
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'accepted' => 'green',
            'sent' => 'blue',
            'rejected' => 'red',
            'expired' => 'gray',
            default => 'amber',
        };
    }
}
