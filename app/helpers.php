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
