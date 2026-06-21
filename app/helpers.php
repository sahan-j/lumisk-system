<?php

use App\Models\Company;

if (! function_exists('company_settings')) {
    /**
     * Resolve the cached single company settings row.
     */
    function company_settings(): Company
    {
        return once(fn () => Company::settings());
    }
}

if (! function_exists('money')) {
    /**
     * Format an amount using the company currency, e.g. "LKR 12,500.00".
     */
    function money(float|int|string|null $amount, bool $withCode = true): string
    {
        $value = number_format((float) $amount, 2, '.', ',');
        $currency = company_settings()->currency ?: 'LKR';

        return $withCode ? "{$currency} {$value}" : $value;
    }
}

if (! function_exists('currency_amount')) {
    /**
     * Format a document amount in the document's own currency. LKR documents keep
     * the base "LKR 1,234.00" style; foreign documents render with their symbol,
     * e.g. "$ 1,234.00". $doc must expose `currency_code` and `currency_symbol`.
     */
    function currency_amount(object $doc, float|int|string|null $amount = null): string
    {
        $amount = $amount ?? ($doc->total ?? 0);
        $code = $doc->currency_code ?? 'LKR';

        if ($code === 'LKR') {
            return money($amount);
        }

        return ($doc->currency_symbol ?? 'Rs') . ' ' . number_format((float) $amount, 2, '.', ',');
    }
}
