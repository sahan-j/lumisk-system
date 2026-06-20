<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Reserve and return the next invoice number (e.g. INV-001).
     * The company's counter is incremented atomically.
     */
    public static function nextInvoiceNumber(): string
    {
        return self::next('invoice_prefix', 'invoice_next_number');
    }

    /**
     * Reserve and return the next estimate number (e.g. EST-001).
     */
    public static function nextEstimateNumber(): string
    {
        return self::next('estimate_prefix', 'estimate_next_number');
    }

    /**
     * Reserve and return the next ticket number (e.g. TKT-001).
     */
    public static function nextTicketNumber(): string
    {
        return self::next('ticket_prefix', 'ticket_next_number');
    }

    /**
     * Reserve and return the next subscription number (e.g. SUB-001).
     */
    public static function nextSubscriptionNumber(): string
    {
        return self::next('subscription_prefix', 'subscription_next_number');
    }

    protected static function next(string $prefixColumn, string $counterColumn): string
    {
        return DB::transaction(function () use ($prefixColumn, $counterColumn) {
            $company = Company::query()->lockForUpdate()->find(1) ?? Company::settings();

            $number = (int) $company->{$counterColumn};
            $prefix = $company->{$prefixColumn} ?: 'DOC';

            $company->{$counterColumn} = $number + 1;
            $company->save();

            return sprintf('%s-%s', $prefix, str_pad((string) $number, 3, '0', STR_PAD_LEFT));
        });
    }
}
