<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Support\PdfRenderer;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfController extends Controller
{
    public function download(Invoice $invoice): Response
    {
        $invoice->load('items', 'client');

        return PdfRenderer::invoice($invoice)->download($invoice->invoice_number . '.pdf');
    }
}
