<?php

namespace App\Models;

use App\Traits\HasAttachments;
use App\Traits\HasCurrency;
use App\Traits\HasNotes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, HasAttachments, HasNotes, HasCurrency;

    public const STATUSES = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];

    protected $fillable = [
        'invoice_number',
        'client_id',
        'status',
        'stock_deducted',
        'converted_from',
        'currency_code',
        'exchange_rate',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total',
        'total_lkr',
        'notes',
        'terms',
        'is_recurring',
        'recurring_cycle',
        'recurring_next_date',
        'recurring_end_date',
        'recurring_parent_id',
        'recurring_count',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'total_lkr' => 'decimal:2',
            'stock_deducted' => 'boolean',
            'is_recurring' => 'boolean',
            'recurring_next_date' => 'date',
            'recurring_end_date' => 'date',
        ];
    }

    /**
     * Deduct stock for tracked products on this invoice's line items, once.
     * Safe to call repeatedly — the stock_deducted flag prevents double counting.
     */
    public function deductStock(): void
    {
        if ($this->stock_deducted) {
            return;
        }

        $this->loadMissing('items.product');

        foreach ($this->items as $item) {
            $product = $item->product;
            if ($product && $product->track_inventory) {
                $product->adjustStock(
                    quantity: -(float) $item->quantity,
                    type: 'sale',
                    notes: "Sold via invoice {$this->invoice_number}",
                    refType: 'Invoice',
                    refId: $this->id,
                    refLabel: $this->invoice_number,
                );
            }
        }

        $this->update(['stock_deducted' => true]);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date', 'desc');
    }

    public function recurringParent(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'recurring_parent_id');
    }

    public function recurringChildren(): HasMany
    {
        return $this->hasMany(Invoice::class, 'recurring_parent_id')->latest();
    }

    public function getTotalPaidAttribute(): float
    {
        return round((float) $this->payments->sum('amount'), 2);
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return round(max(0, (float) $this->total - $this->total_paid), 2);
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->total > 0 && $this->outstanding_balance <= 0;
    }

    public function getRecurringCycleLabelAttribute(): string
    {
        return match ($this->recurring_cycle) {
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Every 3 months',
            'semi_annual' => 'Every 6 months',
            'annual' => 'Annual',
            default => '—',
        };
    }

    public function calculateNextRecurringDate(): Carbon
    {
        $base = $this->recurring_next_date ?? today();

        return match ($this->recurring_cycle) {
            'weekly' => $base->copy()->addWeek(),
            'monthly' => $base->copy()->addMonth(),
            'quarterly' => $base->copy()->addMonths(3),
            'semi_annual' => $base->copy()->addMonths(6),
            'annual' => $base->copy()->addYear(),
            default => $base->copy()->addMonth(),
        };
    }

    /**
     * Recalculate subtotal, tax and total from the related line items.
     */
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

    public function isOverdue(): bool
    {
        return in_array($this->status, ['sent', 'overdue'])
            && $this->due_date
            && $this->due_date->isPast();
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'paid' => 'green',
            'sent' => 'blue',
            'overdue' => 'red',
            'cancelled' => 'gray',
            default => 'amber',
        };
    }
}
