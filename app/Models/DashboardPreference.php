<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DashboardPreference extends Model
{
    protected $fillable = [
        'user_id',
        'widget_layout',
    ];

    protected function casts(): array
    {
        return [
            'widget_layout' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
