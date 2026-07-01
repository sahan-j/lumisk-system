<?php

namespace App\Models;

use App\Services\DocumentNumberService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    use SoftDeletes;

    public const SERVICE_TYPES = ['website', 'mobile_app', 'design', 'maintenance', 'hosting', 'other'];
    public const BUDGET_RANGES = ['under_50k', '50k_150k', '150k_500k', 'over_500k', 'flexible'];
    public const TIMELINES = ['asap', '1_month', '3_months', '6_months', 'flexible'];
    public const STATUSES = ['pending', 'reviewing', 'converted', 'declined'];

    protected $fillable = [
        'request_number',
        'client_id',
        'title',
        'description',
        'service_type',
        'budget_range',
        'timeline',
        'status',
        'admin_note',
        'converted_estimate_id',
        'declined_reason',
        'attachments',
    ];

    protected $appends = [
        'status_color',
        'status_label',
        'service_type_label',
        'budget_range_label',
        'timeline_label',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function convertedEstimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class, 'converted_estimate_id');
    }

    /** Hex status color (brand palette). */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => '#f59e0b',
            'reviewing' => '#6d5cff',
            'converted' => '#10b981',
            'declined' => '#ef4444',
            default => '#94a3b8',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'reviewing' => 'Under Review',
            'converted' => 'Estimate Sent',
            'declined' => 'Declined',
            default => ucfirst($this->status),
        };
    }

    public function getServiceTypeLabelAttribute(): string
    {
        return match ($this->service_type) {
            'website' => '🌐 Website Development',
            'mobile_app' => '📱 Mobile App',
            'design' => '🎨 Design',
            'maintenance' => '🔧 Maintenance',
            'hosting' => '☁️ Hosting & Server',
            'other' => '📋 Other',
            default => ucfirst(str_replace('_', ' ', (string) $this->service_type)),
        };
    }

    public function getBudgetRangeLabelAttribute(): string
    {
        return match ($this->budget_range) {
            'under_50k' => 'Under LKR 50,000',
            '50k_150k' => 'LKR 50,000 – 150,000',
            '150k_500k' => 'LKR 150,000 – 500,000',
            'over_500k' => 'Over LKR 500,000',
            'flexible' => 'Flexible / Open to discussion',
            default => ucfirst((string) $this->budget_range),
        };
    }

    public function getTimelineLabelAttribute(): string
    {
        return match ($this->timeline) {
            'asap' => 'As soon as possible',
            '1_month' => 'Within 1 month',
            '3_months' => 'Within 3 months',
            '6_months' => 'Within 6 months',
            'flexible' => 'Flexible',
            default => ucfirst((string) $this->timeline),
        };
    }

    /** Reserve and return the next quote-request number (e.g. QR-001). */
    public static function generateNumber(): string
    {
        return DocumentNumberService::nextQuoteRequestNumber();
    }
}
