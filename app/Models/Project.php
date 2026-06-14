<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['planning', 'active', 'on_hold', 'completed', 'cancelled'];
    public const PRIORITIES = ['low', 'medium', 'high'];

    protected $fillable = [
        'name',
        'description',
        'client_id',
        'status',
        'priority',
        'start_date',
        'due_date',
        'completed_at',
        'budget',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'budget' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('sort_order');
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'project_invoice');
    }

    public function getCompletionPercentageAttribute(): int
    {
        $total = $this->tasks->count();
        if ($total === 0) {
            return 0;
        }

        $done = $this->tasks->where('status', 'done')->count();

        return (int) round(($done / $total) * 100);
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['completed', 'cancelled']);
    }

    /** Named color compatible with x-status-badge. */
    public function statusColor(): string
    {
        return match ($this->status) {
            'active' => 'blue',
            'completed' => 'green',
            'on_hold' => 'amber',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    /** Named color compatible with x-status-badge. */
    public function priorityColor(): string
    {
        return match ($this->priority) {
            'high' => 'red',
            'medium' => 'amber',
            default => 'green',
        };
    }

    public function statusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
}
