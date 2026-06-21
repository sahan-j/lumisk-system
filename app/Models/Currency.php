<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_default',
        'is_active',
        'updated_at_rate',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:4',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'updated_at_rate' => 'datetime',
        ];
    }

    /** Per-request memo of code => Currency so symbol/rate lookups don't hit the DB N times. */
    protected static array $codeCache = [];

    protected static function booted(): void
    {
        // Any write invalidates the lookup cache.
        static::saved(fn () => static::$codeCache = []);
        static::deleted(fn () => static::$codeCache = []);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public static function getDefault(): ?self
    {
        return static::getByCode('LKR') ?? static::where('is_default', true)->first();
    }

    public static function getByCode(string $code): ?self
    {
        if (empty(static::$codeCache)) {
            static::$codeCache = static::all()->keyBy('code')->all();
        }

        return static::$codeCache[$code] ?? null;
    }
}
