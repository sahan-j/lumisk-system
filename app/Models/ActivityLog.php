<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'type',
        'description',
        'subject_type',
        'subject_id',
        'subject_label',
        'causer_type',
        'causer_name',
        'client_id',
        'meta',
    ];

    protected $appends = ['color', 'icon_path'];

    /** Type groups used by the dashboard filter chips. */
    public const GROUPS = [
        'invoices' => ['invoice_created', 'invoice_sent', 'invoice_paid', 'invoice_overdue'],
        'payments' => ['payment_recorded'],
        'tickets' => ['ticket_created', 'ticket_replied', 'ticket_resolved'],
        'clients' => ['client_created', 'client_updated'],
        'projects' => ['project_created', 'project_completed', 'task_completed'],
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Record an activity. Admin causer name defaults to the authenticated user.
     */
    public static function log(string $type, string $description, array $options = []): self
    {
        $causerType = $options['causer_type'] ?? 'admin';
        $causerName = $options['causer_name']
            ?? ($causerType === 'admin' ? (auth()->user()?->name ?? 'Admin') : 'Client');

        return self::create([
            'type' => $type,
            'description' => $description,
            'subject_type' => $options['subject_type'] ?? null,
            'subject_id' => $options['subject_id'] ?? null,
            'subject_label' => $options['subject_label'] ?? null,
            'causer_type' => $causerType,
            'causer_name' => $causerName,
            'client_id' => $options['client_id'] ?? null,
            'meta' => $options['meta'] ?? null,
        ]);
    }

    /** Hex color for the activity icon (brand palette). */
    public function getColorAttribute(): string
    {
        return match ($this->type) {
            'invoice_paid', 'estimate_accepted', 'ticket_resolved', 'project_completed', 'task_completed' => '#10b981',
            'invoice_created', 'estimate_created', 'client_created', 'project_created', 'ticket_created' => '#6d5cff',
            'payment_recorded' => '#00d4ff',
            'invoice_overdue', 'estimate_rejected' => '#ef4444',
            'invoice_sent', 'estimate_sent', 'ticket_replied' => '#f59e0b',
            'expense_recorded' => '#94a3b8',
            'subscription_billed' => '#00d4ff',
            'subscription_created' => '#6d5cff',
            'subscription_cancelled' => '#ef4444',
            'client_updated', 'estimate_converted' => '#6d5cff',
            'lead_converted' => '#10b981',
            'lead_created' => '#6d5cff',
            'lead_stage_changed' => '#00d4ff',
            'lead_lost' => '#ef4444',
            'credit_note_applied' => '#10b981',
            'credit_note_created', 'credit_note_issued' => '#ef4444',
            'credit_note_void' => '#94a3b8',
            'low_stock_alert' => '#f59e0b',
            default => '#6d5cff',
        };
    }

    /** Heroicon-style outline SVG path for the activity icon. */
    public function getIconPathAttribute(): string
    {
        return match ($this->type) {
            'invoice_created' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'invoice_sent', 'estimate_sent' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
            'invoice_paid', 'estimate_accepted', 'ticket_resolved', 'task_completed' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'invoice_overdue' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'estimate_created' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            'estimate_rejected' => 'M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.737 3h4.017c.163 0 .326.02.485.06L17 4m-7 10v5a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5',
            'estimate_converted' => 'M8 7h12m0 0l-4-4m4 4l-4 4m4 6H4m0 0l4 4m-4-4l4-4',
            'payment_recorded' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            'client_created' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
            'client_updated' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'project_created', 'project_completed' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'ticket_created' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
            'ticket_replied' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
            'expense_recorded' => 'M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 011-1z',
            'subscription_billed', 'subscription_created', 'subscription_cancelled' => 'M7 7h10v10M7 17L17 7',
            'lead_created', 'lead_stage_changed', 'lead_converted', 'lead_lost' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
            'credit_note_created', 'credit_note_issued', 'credit_note_applied', 'credit_note_void' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
            'low_stock_alert' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            default => 'M3 12h4l3 8 4-16 3 8h4',
        };
    }
}
