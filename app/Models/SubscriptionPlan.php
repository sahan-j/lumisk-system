<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    public const BILLING_CYCLES = ['weekly', 'monthly', 'quarterly', 'semi_annual', 'annual'];

    protected $fillable = [
        'name',
        'description',
        'amount',
        'currency',
        'billing_cycle',
        'billing_cycle_days',
        'trial_days',
        'is_active',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
            'features' => 'array',
            'trial_days' => 'integer',
            'billing_cycle_days' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    /** Days in one billing cycle — used to seed billing_cycle_days. */
    public static function cycleDays(string $cycle): int
    {
        return match ($cycle) {
            'weekly' => 7,
            'monthly' => 30,
            'quarterly' => 90,
            'semi_annual' => 180,
            'annual' => 365,
            default => 30,
        };
    }

    public function getCycleDaysAttribute(): int
    {
        return self::cycleDays($this->billing_cycle);
    }

    /** Monthly recurring equivalent of this plan's amount. */
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
}
