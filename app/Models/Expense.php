<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    public const PAYMENT_METHODS = ['cash', 'bank_transfer', 'card', 'cheque', 'other'];

    protected $fillable = [
        'title',
        'description',
        'amount',
        'expense_date',
        'category_id',
        'client_id',
        'project_id',
        'payment_method',
        'receipt',
        'reference_number',
        'is_billable',
        'is_billed',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'is_billable' => 'boolean',
            'is_billed' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
            'cheque' => 'Cheque',
            'other' => 'Other',
            default => 'Unknown',
        };
    }
}
