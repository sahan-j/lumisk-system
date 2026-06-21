<?php

namespace App\Helpers;

use App\Models\Currency;
use Illuminate\Support\Collection;

class CurrencyHelper
{
    /** Format an amount with the given currency's symbol, e.g. "$ 1,200.00". */
    public static function format(float $amount, string $currencyCode = 'LKR'): string
    {
        return static::getSymbol($currencyCode) . ' ' . number_format($amount, 2);
    }

    /** Convert an amount in the given currency to its LKR equivalent. */
    public static function toLkr(float $amount, string $currencyCode, ?float $rate = null): float
    {
        if ($currencyCode === 'LKR') {
            return $amount;
        }

        if ($rate !== null) {
            return $amount * $rate;
        }

        return $amount * (float) (Currency::getByCode($currencyCode)?->exchange_rate ?? 1);
    }

    public static function getSymbol(string $code): string
    {
        return Currency::getByCode($code)?->symbol ?? 'Rs';
    }

    public static function getActiveCurrencies(): Collection
    {
        return Currency::active()->orderBy('code')->get();
    }
}
