<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, Auditable;

    public const METHODS = ['cash', 'bank_transfer', 'cheque', 'card', 'other'];

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'card' => 'Card',
            'other' => 'Other',
            default => 'Unknown',
        };
    }
}
