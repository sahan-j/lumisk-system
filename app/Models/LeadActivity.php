<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    protected $fillable = [
        'lead_id',
        'type',
        'content',
        'created_by',
    ];

    protected $appends = ['icon_path', 'icon_color', 'type_label'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** Heroicon-style outline SVG path for the activity type. */
    public function getIconPathAttribute(): string
    {
        return match ($this->type) {
            'call' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
            'email' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'meeting' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z',
            'stage_change' => 'M14 5l7 7m0 0l-7 7m7-7H3',
            'whatsapp' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
            default => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        };
    }

    /** Hex color for the activity icon (brand palette). */
    public function getIconColorAttribute(): string
    {
        return match ($this->type) {
            'call' => '#10b981',
            'email' => '#00d4ff',
            'meeting' => '#8b5cf6',
            'stage_change' => '#6d5cff',
            'whatsapp' => '#25d366',
            default => '#94a3b8',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', (string) $this->type));
    }
}
