<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Support\PdfRenderer;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CreditNotePdfController extends Controller
{
    public function download(CreditNote $creditNote): Response
    {
        abort_unless(
            $creditNote->client_id === Auth::guard('client')->id() && $creditNote->status !== 'draft',
            403
        );

        $creditNote->load('items', 'client', 'invoice');

        return PdfRenderer::creditNote($creditNote)->download($creditNote->credit_note_number . '.pdf');
    }
}
