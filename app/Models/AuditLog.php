<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_type',
        'user_id',
        'user_name',
        'event',
        'auditable_type',
        'auditable_id',
        'auditable_label',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
    ];

    protected $appends = ['event_color', 'event_icon'];

    /** Human label per model class, used in the audit log filter dropdown. */
    public const MODELS = [
        'Invoice' => 'Invoices',
        'Estimate' => 'Estimates',
        'Client' => 'Clients',
        'Payment' => 'Payments',
        'Project' => 'Projects',
        'Expense' => 'Expenses',
        'User' => 'Staff',
        'Subscription' => 'Subscriptions',
        'CreditNote' => 'Credit Notes',
    ];

    public const EVENTS = ['created', 'updated', 'deleted', 'restored'];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an audit entry for a model change. Causer resolves to the
     * authenticated admin, or 'system' when run outside a request (console).
     */
    public static function record(string $event, Model $model, array $oldValues = [], array $newValues = []): void
    {
        self::create([
            'user_type' => auth()->check() ? 'admin' : 'system',
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'System',
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'auditable_label' => self::resolveLabel($model),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
            'url' => substr(request()->fullUrl() ?? '', 0, 500),
        ]);
    }

    /** Best human-readable identifier for the audited record. */
    private static function resolveLabel(Model $model): string
    {
        foreach (['invoice_number', 'estimate_number', 'credit_note_number', 'number', 'name', 'title', 'subject'] as $attr) {
            $value = $model->getAttribute($attr);
            if (filled($value)) {
                return (string) $value;
            }
        }

        return class_basename($model) . ' #' . $model->getKey();
    }

    /** Brand-palette hex color for the event badge. */
    public function getEventColorAttribute(): string
    {
        return match ($this->event) {
            'created' => '#10b981',
            'updated' => '#6d5cff',
            'deleted' => '#ef4444',
            'restored' => '#f59e0b',
            default => '#94a3b8',
        };
    }

    /** Heroicon-style outline SVG path for the event icon. */
    public function getEventIconAttribute(): string
    {
        return match ($this->event) {
            'created' => 'M12 4v16m8-8H4',
            'updated' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'deleted' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
            'restored' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }
}
