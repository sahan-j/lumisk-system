<?php

namespace App\Models;

use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, HasAttachments, HasNotes;

    public const STATUSES = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];

    protected $fillable = [
        'invoice_number',
        'client_id',
        'status',
        'converted_from',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total',
        'notes',
        'terms',
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
        ];
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
