<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'task_id',
        'client_id',
        'user_id',
        'user_name',
        'description',
        'started_at',
        'ended_at',
        'duration_minutes',
        'hourly_rate',
        'is_billable',
        'is_billed',
        'billed_invoice_id',
        'date',
    ];

    protected $appends = ['duration_formatted', 'duration_hours', 'billable_amount', 'is_running'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'date' => 'date',
            'hourly_rate' => 'decimal:2',
            'is_billable' => 'boolean',
            'is_billed' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function billedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'billed_invoice_id');
    }

    public function getDurationFormattedAttribute(): string
    {
        $mins = (int) ($this->duration_minutes ?? 0);

        return sprintf('%dh %02dm', intdiv($mins, 60), $mins % 60);
    }

    public function getDurationHoursAttribute(): float
    {
        return round(((int) ($this->duration_minutes ?? 0)) / 60, 2);
    }

    public function getBillableAmountAttribute(): float
    {
        if (! $this->is_billable || ! $this->hourly_rate) {
            return 0;
        }

        return round($this->duration_hours * (float) $this->hourly_rate, 2);
    }

    public function getIsRunningAttribute(): bool
    {
        return $this->started_at && ! $this->ended_at;
    }

    public static function calculateDuration(Carbon $start, Carbon $end): int
    {
        return (int) $start->diffInMinutes($end);
    }
}
