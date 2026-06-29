<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ClientDocument extends Model
{
    use SoftDeletes;

    public const CATEGORIES = ['payment_proof', 'requirements', 'content', 'contract', 'design_feedback', 'other'];

    protected $fillable = [
        'client_id',
        'project_id',
        'invoice_id',
        'uploaded_by',
        'category',
        'title',
        'original_filename',
        'stored_filename',
        'path',
        'mime_type',
        'size',
        'description',
        'is_visible_to_client',
        'admin_note',
        'client_note',
        'viewed_by_admin',
        'viewed_by_client',
    ];

    protected $appends = ['file_size_formatted', 'is_image', 'is_pdf', 'icon', 'icon_color', 'category_label'];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'is_visible_to_client' => 'boolean',
            'viewed_by_admin' => 'boolean',
            'viewed_by_client' => 'boolean',
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
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

    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /** Inline-SVG path for the file-type icon. */
    public function getIconAttribute(): string
    {
        $documentPath = 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';

        return match (true) {
            str_starts_with((string) $this->mime_type, 'image/') => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            str_contains((string) $this->mime_type, 'zip') || str_contains((string) $this->mime_type, 'rar') => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
            default => $documentPath,
        };
    }

    public function getIconColorAttribute(): string
    {
        return match (true) {
            str_starts_with((string) $this->mime_type, 'image/') => '#10b981',
            $this->mime_type === 'application/pdf' => '#ef4444',
            str_contains((string) $this->mime_type, 'word') => '#2563eb',
            str_contains((string) $this->mime_type, 'excel') || str_contains((string) $this->mime_type, 'spreadsheet') => '#10b981',
            default => '#94a3b8',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'payment_proof' => '💳 Payment Proof',
            'requirements' => '📋 Requirements',
            'content' => '✏️ Content',
            'contract' => '📄 Contract',
            'design_feedback' => '🎨 Design Feedback',
            default => '📎 Other',
        };
    }
}
