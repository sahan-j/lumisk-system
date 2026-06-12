<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Support\PdfRenderer;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfController extends Controller
{
    public function download(Invoice $invoice): Response
    {
        // Clients may only download their own invoices.
        abort_unless($invoice->client_id === Auth::guard('client')->id(), 403);

        $invoice->load('items', 'client');

        return PdfRenderer::invoice($invoice)->download($invoice->invoice_number . '.pdf');
    }
}
