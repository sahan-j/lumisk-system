<?php

namespace App\Models;

use App\Services\DocumentNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['open', 'in_progress', 'waiting_client', 'resolved', 'closed'];
    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];
    public const TYPES = ['bug_report', 'feature_request', 'general_question', 'maintenance_request'];

    protected $fillable = [
        'ticket_number',
        'subject',
        'type',
        'status',
        'priority',
        'client_id',
        'project_id',
        'assigned_to',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'sla_due_at',
        'is_overdue_sla',
    ];

    protected function casts(): array
    {
        return [
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'sla_due_at' => 'datetime',
            'is_overdue_sla' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    /** Reserve and return the next ticket number (e.g. TKT-001). */
    public static function generateNumber(): string
    {
        return DocumentNumberService::nextTicketNumber();
    }

    public static function getSlaHours(string $priority): int
    {
        $company = Company::settings();

        return (int) match ($priority) {
            'low' => $company->sla_low_hours ?? 72,
            'medium' => $company->sla_medium_hours ?? 24,
            'high' => $company->sla_high_hours ?? 4,
            'critical' => $company->sla_critical_hours ?? 1,
            default => 24,
        };
    }

    public function isSlaOverdue(): bool
    {
        return $this->sla_due_at
            && $this->sla_due_at->isPast()
            && ! in_array($this->status, ['resolved', 'closed'], true);
    }

    /** Named color compatible with x-status-badge. */
    public function statusColor(): string
    {
        return match ($this->status) {
            'open' => 'red',
            'in_progress' => 'blue',
            'waiting_client' => 'amber',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'waiting_client' => 'Waiting on Client',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            default => ucfirst($this->status),
        };
    }

    /** Named color compatible with x-status-badge. */
    public function priorityColor(): string
    {
        return match ($this->priority) {
            'low' => 'green',
            'medium' => 'amber',
            'high', 'critical' => 'red',
            default => 'gray',
        };
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'bug_report' => '🐛 Bug Report',
            'feature_request' => '✨ Feature Request',
            'general_question' => '❓ General Question',
            'maintenance_request' => '🔧 Maintenance Request',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
