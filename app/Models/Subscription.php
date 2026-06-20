<?php

namespace App\Models;

use App\Services\DocumentNumberService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use SoftDeletes;

    public const STATUSES = ['trial', 'active', 'past_due', 'cancelled', 'expired', 'paused'];

    public const BILLING_CYCLES = ['weekly', 'monthly', 'quarterly', 'semi_annual', 'annual'];

    protected $fillable = [
        'subscription_number',
        'client_id',
        'plan_id',
        'name',
        'description',
        'amount',
        'currency',
        'billing_cycle',
        'status',
        'start_date',
        'trial_end_date',
        'next_billing_date',
        'last_billed_date',
        'end_date',
        'cancelled_at',
        'cancellation_reason',
        'auto_invoice',
        'auto_send_invoice',
        'notes',
        'created_by',
    ];

    protected $appends = [
        'status_color',
        'status_label',
        'is_overdue',
        'days_until_next_billing',
        'yearly_value',
        'monthly_value',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'trial_end_date' => 'date',
            'next_billing_date' => 'date',
            'last_billed_date' => 'date',
            'end_date' => 'date',
            'cancelled_at' => 'datetime',
            'auto_invoice' => 'boolean',
            'auto_send_invoice' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'subscription_invoices')
            ->withPivot('billing_period_start', 'billing_period_end')
            ->withTimestamps();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => '#10b981',
            'trial' => '#00d4ff',
            'past_due' => '#ef4444',
            'paused' => '#f59e0b',
            'cancelled' => '#94a3b8',
            'expired' => '#64748b',
            default => '#94a3b8',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'trial' => 'Trial',
            'past_due' => 'Past Due',
            'paused' => 'Paused',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
            default => ucfirst($this->status),
        };
    }

    public function getBillingCycleLabelAttribute(): string
    {
        return match ($this->billing_cycle) {
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly (3 months)',
            'semi_annual' => 'Semi-Annual (6 months)',
            'annual' => 'Annual',
            default => ucfirst($this->billing_cycle),
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'active'
            && $this->next_billing_date
            && $this->next_billing_date->isPast();
    }

    public function getDaysUntilNextBillingAttribute(): int
    {
        if (! $this->next_billing_date) {
            return 0;
        }

        return (int) ceil(now()->startOfDay()->diffInDays($this->next_billing_date->copy()->startOfDay(), false));
    }

    public function getTotalBilledAttribute(): float
    {
        return round((float) $this->invoices()->sum('total'), 2);
    }

    public function getYearlyValueAttribute(): float
    {
        return round((float) match ($this->billing_cycle) {
            'weekly' => $this->amount * 52,
            'monthly' => $this->amount * 12,
            'quarterly' => $this->amount * 4,
            'semi_annual' => $this->amount * 2,
            'annual' => $this->amount,
            default => 0,
        }, 2);
    }

    /** Monthly recurring equivalent — used for MRR. */
    public function getMonthlyValueAttribute(): float
    {
        return round((float) match ($this->billing_cycle) {
            'weekly' => $this->amount * 52 / 12,
            'monthly' => $this->amount,
            'quarterly' => $this->amount / 3,
            'semi_annual' => $this->amount / 6,
            'annual' => $this->amount / 12,
            default => 0,
        }, 2);
    }

    /** Reserve the next subscription number (e.g. SUB-001). */
    public static function generateNumber(): string
    {
        return DocumentNumberService::nextSubscriptionNumber();
    }

    /** Advance a date by one billing cycle. Defaults to the current next-billing date. */
    public function calculateNextBillingDate(?Carbon $from = null): Carbon
    {
        $base = ($from ?? $this->next_billing_date ?? now())->copy();

        return match ($this->billing_cycle) {
            'weekly' => $base->addWeek(),
            'monthly' => $base->addMonth(),
            'quarterly' => $base->addMonths(3),
            'semi_annual' => $base->addMonths(6),
            'annual' => $base->addYear(),
            default => $base->addMonth(),
        };
    }

    /**
     * Create an invoice for the current billing period and roll the billing dates forward.
     */
    public function generateInvoice(): Invoice
    {
        $periodStart = ($this->next_billing_date ?? today())->copy();
        $periodEnd = $this->calculateNextBillingDate($periodStart)->copy()->subDay();
        $dueDays = (int) (company_settings()->subscription_invoice_due_days ?? 14);

        $invoice = new Invoice();
        $invoice->invoice_number = DocumentNumberService::nextInvoiceNumber();
        $invoice->client_id = $this->client_id;
        $invoice->status = 'draft';
        $invoice->issue_date = today();
        $invoice->due_date = today()->addDays($dueDays);
        $invoice->tax_rate = 0;
        $invoice->discount_amount = 0;
        $invoice->notes = "Subscription: {$this->name} ({$this->subscription_number})";
        $invoice->save();

        $invoice->items()->create([
            'name' => $this->name,
            'description' => 'Billing period: ' . $periodStart->format('M d, Y') . ' to ' . $periodEnd->format('M d, Y'),
            'quantity' => 1,
            'unit_price' => $this->amount,
            'total' => $this->amount,
            'order' => 0,
        ]);

        $invoice->load('items');
        $invoice->recalculateTotals();
        $invoice->save();

        $this->invoices()->attach($invoice->id, [
            'billing_period_start' => $periodStart,
            'billing_period_end' => $periodEnd,
        ]);

        $this->update([
            'last_billed_date' => today(),
            'next_billing_date' => $this->calculateNextBillingDate($periodStart),
        ]);

        return $invoice;
    }
}
