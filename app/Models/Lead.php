<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    public const SOURCES = [
        'website', 'referral', 'social_media', 'cold_outreach',
        'walk_in', 'whatsapp', 'other',
    ];

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'source',
        'stage_id',
        'value',
        'currency',
        'probability',
        'expected_close_date',
        'assigned_to',
        'notes',
        'sort_order',
        'converted_to_client_id',
        'converted_at',
        'lost_reason',
        'last_activity_at',
    ];

    protected $appends = ['source_label', 'weighted_value', 'is_converted'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'probability' => 'integer',
            'sort_order' => 'integer',
            'expected_close_date' => 'date',
            'converted_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_to_client_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'website' => '🌐 Website',
            'referral' => '🤝 Referral',
            'social_media' => '📱 Social Media',
            'cold_outreach' => '📧 Cold Outreach',
            'walk_in' => '🚶 Walk-in',
            'whatsapp' => '💬 WhatsApp',
            'other' => '📌 Other',
            default => ucwords(str_replace('_', ' ', (string) $this->source)),
        };
    }

    public function getWeightedValueAttribute(): float
    {
        return (float) ($this->value ?? 0) * ((int) ($this->probability ?? 0) / 100);
    }

    public function getIsConvertedAttribute(): bool
    {
        return $this->converted_to_client_id !== null;
    }

    public function getDaysInStageAttribute(): int
    {
        return (int) $this->updated_at->diffInDays(now());
    }

    /** Record a timeline activity and bump the lead's last-activity timestamp. */
    public function logActivity(string $type, string $content): LeadActivity
    {
        $activity = LeadActivity::create([
            'lead_id' => $this->id,
            'type' => $type,
            'content' => $content,
            'created_by' => auth()->user()->name ?? 'Admin',
        ]);

        $this->update(['last_activity_at' => now()]);

        return $activity;
    }

    /**
     * Create a Client from this lead, mark the lead converted, and move it to the won stage.
     * Returns the existing client if already converted.
     */
    public function convertToClient(): Client
    {
        if ($this->convertedClient) {
            return $this->convertedClient;
        }

        $client = Client::create([
            'name' => $this->name,
            'company_name' => $this->company_name,
            'email' => $this->email ?: 'lead-' . $this->id . '@placeholder.com',
            'phone' => $this->phone,
            'address' => '',
            'portal_enabled' => false,
        ]);

        $wonStage = PipelineStage::where('is_won', true)->first();

        $this->update([
            'converted_to_client_id' => $client->id,
            'converted_at' => now(),
            'stage_id' => $wonStage?->id ?? $this->stage_id,
        ]);

        $this->logActivity('note', "Converted to client: {$client->name}");

        ActivityLog::log('lead_converted',
            "Lead '{$this->name}' converted to client",
            ['subject_type' => 'Lead', 'subject_id' => $this->id, 'subject_label' => $this->name, 'client_id' => $client->id]);

        return $client;
    }

    /** Move the lead to the lost stage with an optional reason. */
    public function markLost(?string $reason = null): void
    {
        $lostStage = PipelineStage::where('is_lost', true)->first();

        $this->update([
            'stage_id' => $lostStage?->id ?? $this->stage_id,
            'lost_reason' => $reason,
        ]);

        $this->logActivity('note', 'Marked as lost' . ($reason ? ": {$reason}" : '.'));

        ActivityLog::log('lead_lost',
            "Lead '{$this->name}' marked as lost",
            ['subject_type' => 'Lead', 'subject_id' => $this->id, 'subject_label' => $this->name]);
    }
}
