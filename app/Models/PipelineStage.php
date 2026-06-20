<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    protected $fillable = [
        'name',
        'color',
        'sort_order',
        'is_won',
        'is_lost',
    ];

    protected function casts(): array
    {
        return [
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'stage_id')->orderBy('sort_order');
    }
}
