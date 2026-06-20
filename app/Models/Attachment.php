<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'filename',
        'stored_filename',
        'path',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    protected $appends = ['file_size_formatted', 'is_image', 'icon_path', 'icon_color'];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = (int) $this->size;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    /** Heroicon-style outline SVG path for the file type. */
    public function getIconPathAttribute(): string
    {
        $document = 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';

        return match (true) {
            $this->is_image => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            str_contains((string) $this->mime_type, 'zip') || str_contains((string) $this->mime_type, 'rar') => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
            default => $document,
        };
    }

    public function getIconColorAttribute(): string
    {
        return match (true) {
            $this->is_image => '#10b981',
            $this->mime_type === 'application/pdf' => '#ef4444',
            str_contains((string) $this->mime_type, 'word') => '#2563eb',
            str_contains((string) $this->mime_type, 'excel') || str_contains((string) $this->mime_type, 'spreadsheet') => '#10b981',
            str_contains((string) $this->mime_type, 'zip') || str_contains((string) $this->mime_type, 'rar') => '#f59e0b',
            default => '#94a3b8',
        };
    }
}
