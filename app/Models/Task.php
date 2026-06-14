<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['todo', 'in_progress', 'done'];
    public const PRIORITIES = ['low', 'medium', 'high'];

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function markDone(): void
    {
        $this->update(['status' => 'done', 'completed_at' => now()]);
    }

    public function markTodo(): void
    {
        $this->update(['status' => 'todo', 'completed_at' => null]);
    }

    public function priorityColor(): string
    {
        return match ($this->priority) {
            'high' => 'red',
            'medium' => 'amber',
            default => 'green',
        };
    }
}
