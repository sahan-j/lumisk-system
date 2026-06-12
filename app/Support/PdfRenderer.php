<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Estimate;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfWrapper;

class PdfRenderer
{
    private static function options(): array
    {
        return [
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled'         => false,
            'defaultFont'          => 'DejaVu Sans',
            'enable_css_float'     => true,
            'isRemoteEnabled'      => false,
            'chroot'               => public_path(),
        ];
    }

    public static function invoice(Invoice $invoice): PdfWrapper
    {
        return Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'company' => Company::settings(),
        ])->setPaper('a4', 'portrait')->setOptions(self::options());
    }

    public static function estimate(Estimate $estimate): PdfWrapper
    {
        return Pdf::loadView('pdf.estimate', [
            'estimate' => $estimate,
            'company' => Company::settings(),
        ])->setPaper('a4', 'portrait')->setOptions(self::options());
    }
}
