<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class InvoiceTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'name',
        'description',
        'quantity',
        'unit_price',
        'total',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'template_id');
    }
}
