<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Support\PdfRenderer;
use Symfony\Component\HttpFoundation\Response;

class CreditNotePdfController extends Controller
{
    public function download(CreditNote $creditNote): Response
    {
        $creditNote->load('items', 'client', 'invoice');

        return PdfRenderer::creditNote($creditNote)->download($creditNote->credit_note_number . '.pdf');
    }
}
