<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KbArticle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'visibility',
        'view_count',
        'helpful_count',
        'not_helpful_count',
        'sort_order',
        'published_at',
        'author_name',
    ];

    protected $appends = ['is_published', 'helpful_percentage', 'read_time'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'view_count' => 'integer',
            'helpful_count' => 'integer',
            'not_helpful_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (KbArticle $model) {
            if (! $model->slug) {
                $model->slug = static::uniqueSlug($model->title);
            }
            if ($model->status === 'published' && ! $model->published_at) {
                $model->published_at = now();
            }
        });

        static::updating(function (KbArticle $model) {
            if ($model->isDirty('status') && $model->status === 'published' && ! $model->published_at) {
                $model->published_at = now();
            }
        });
    }

    public static function uniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: 'article';
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('visibility', 'public');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class, 'category_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(KbArticleFeedback::class, 'article_id');
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }

    public function getHelpfulPercentageAttribute(): int
    {
        $total = $this->helpful_count + $this->not_helpful_count;

        return $total === 0 ? 0 : (int) round(($this->helpful_count / $total) * 100);
    }

    public function getReadTimeAttribute(): string
    {
        $words = str_word_count(strip_tags((string) $this->content));

        return max(1, (int) round($words / 200)) . ' min read';
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }
}
