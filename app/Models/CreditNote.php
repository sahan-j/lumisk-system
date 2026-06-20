<?php

namespace App\Models;

use App\Services\DocumentNumberService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreditNote extends Model
{
    use SoftDeletes;

    public const STATUSES = ['draft', 'issued', 'applied', 'void'];

    /** Common credit-note reasons offered in the form. */
    public const REASONS = [
        'Service not delivered',
        'Duplicate invoice',
        'Pricing error',
        'Project cancellation',
        'Partial refund',
        'Goodwill credit',
        'Other',
    ];

    protected $fillable = [
        'credit_note_number',
        'invoice_id',
        'client_id',
        'status',
        'issue_date',
        'reason',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'amount_applied',
        'notes',
    ];

    protected $appends = ['amount_remaining', 'is_fully_applied', 'status_color', 'status_label'];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_applied' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class)->orderBy('sort_order');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CreditNoteApplication::class)->latest('applied_at');
    }

    public function getAmountRemainingAttribute(): float
    {
        return round(max(0, (float) $this->total - (float) $this->amount_applied), 2);
    }

    public function getIsFullyAppliedAttribute(): bool
    {
        return $this->amount_remaining <= 0;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => '#94a3b8',
            'issued' => '#6d5cff',
            'applied' => '#10b981',
            'void' => '#ef4444',
            default => '#94a3b8',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'issued' => 'Issued',
            'applied' => 'Applied',
            'void' => 'Void',
            default => ucfirst($this->status),
        };
    }

    public static function generateNumber(): string
    {
        return DocumentNumberService::nextCreditNoteNumber();
    }

    /**
     * Recompute subtotal, tax and total from the line items (does not persist).
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items->sum(fn ($item) => (float) $item->quantity * (float) $item->unit_price);
        $taxAmount = round($subtotal * ((float) $this->tax_rate / 100), 2);

        $this->subtotal = round($subtotal, 2);
        $this->tax_amount = $taxAmount;
        $this->total = round($subtotal + $taxAmount, 2);
    }

    /**
     * Apply part (or all) of this credit note to an invoice: records the application,
     * bumps the applied amount/status, posts a payment to the invoice, and marks the
     * invoice paid if it is now settled. Returns the application record.
     */
    public function applyToInvoice(Invoice $invoice, float $amount): CreditNoteApplication
    {
        return DB::transaction(function () use ($invoice, $amount) {
            $application = $this->applications()->create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'applied_at' => now(),
            ]);

            $newApplied = round((float) $this->amount_applied + $amount, 2);
            $this->update([
                'amount_applied' => $newApplied,
                'status' => $newApplied >= (float) $this->total ? 'applied' : 'issued',
            ]);

            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_method' => 'other',
                'payment_date' => today(),
                'reference_number' => $this->credit_note_number,
                'note' => "Credit note {$this->credit_note_number} applied",
            ]);

            $invoice->load('payments');
            if ($invoice->is_fully_paid && $invoice->status !== 'paid') {
                $invoice->update(['status' => 'paid']);
            }

            return $application;
        });
    }
}
