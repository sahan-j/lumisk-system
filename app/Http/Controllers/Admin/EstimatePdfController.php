<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Estimate;
use App\Support\PdfRenderer;
use Symfony\Component\HttpFoundation\Response;

class EstimatePdfController extends Controller
{
    public function download(Estimate $estimate): Response
    {
        $estimate->load('items', 'client');

        return PdfRenderer::estimate($estimate)->download($estimate->estimate_number . '.pdf');
    }
}
