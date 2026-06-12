<?php

namespace App\Helpers;

use App\Models\Company;
use App\Models\Estimate;
use App\Models\Invoice;

class EmailTemplateHelper
{
    public static function forInvoice(string $template, Invoice $invoice, Company $company): string
    {
        return str_replace(
            ['{invoice_number}', '{client_name}', '{total}', '{due_date}', '{company_name}'],
            [
                $invoice->invoice_number,
                $invoice->client->name,
                ($company->currency ?: 'LKR') . ' ' . number_format((float) $invoice->total, 2),
                $invoice->due_date?->format('M d, Y') ?? '—',
                $company->name,
            ],
            $template
        );
    }

    public static function forEstimate(string $template, Estimate $estimate, Company $company): string
    {
        return str_replace(
            ['{estimate_number}', '{client_name}', '{total}', '{expiry_date}', '{company_name}'],
            [
                $estimate->estimate_number,
                $estimate->client->name,
                ($company->currency ?: 'LKR') . ' ' . number_format((float) $estimate->total, 2),
                $estimate->expiry_date?->format('M d, Y') ?? '—',
                $company->name,
            ],
            $template
        );
    }
}
