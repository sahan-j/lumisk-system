<?php

namespace App\Traits;

use App\Models\Currency;

/**
 * Adds per-document currency awareness. The model is expected to have a
 * `currency_code` column (and, for documents that store it, `exchange_rate`).
 */
trait HasCurrency
{
    /** Symbol for this document's currency, falling back to the LKR symbol. */
    public function getCurrencySymbolAttribute(): string
    {
        return Currency::getByCode($this->currency_code ?: 'LKR')?->symbol ?? 'Rs';
    }

    public function getIsForeignCurrencyAttribute(): bool
    {
        return ($this->currency_code ?: 'LKR') !== 'LKR';
    }

    /** Format an amount with this document's currency symbol, e.g. "$ 1,200.00". */
    public function formatAmount(float|int|string|null $amount): string
    {
        return $this->currency_symbol . ' ' . number_format((float) $amount, 2);
    }
}
