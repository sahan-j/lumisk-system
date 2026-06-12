<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'invoice_next_number' => 'integer',
            'estimate_next_number' => 'integer',
            'estimate_expiry_days' => 'integer',
            'default_tax_rate' => 'decimal:2',
        ];
    }

    /**
     * Get the single company settings row, creating it if missing.
     */
    public static function settings(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return asset('storage/' . $this->logo);
    }
}
