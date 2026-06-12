<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Estimate;
use App\Support\PdfRenderer;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EstimatePdfController extends Controller
{
    public function download(Estimate $estimate): Response
    {
        // Clients may only download their own estimates.
        abort_unless($estimate->client_id === Auth::guard('client')->id(), 403);

        $estimate->load('items', 'client');

        return PdfRenderer::estimate($estimate)->download($estimate->estimate_number . '.pdf');
    }
}
