<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\PublicToken;
use App\Support\PdfRenderer;
use Illuminate\Http\Request;

class PublicViewController extends Controller
{
    public function show(Request $request, string $token)
    {
        $publicToken = PublicToken::where('token', $token)->valid()->first();

        if (! $publicToken) {
            abort(404, 'This link is invalid or has expired.');
        }

        $publicToken->forceFill([
            'last_accessed_at' => now(),
            'access_count' => $publicToken->access_count + 1,
        ])->save();

        $company = Company::settings();

        if ($publicToken->type === 'invoice') {
            $record = Invoice::with(['client', 'items'])->findOrFail($publicToken->reference_id);

            if ($request->query('download') === '1') {
                return PdfRenderer::invoice($record)->download($record->invoice_number . '.pdf');
            }

            return view('public.invoice', compact('record', 'company'));
        }

        $record = Estimate::with(['client', 'items'])->findOrFail($publicToken->reference_id);

        if ($request->query('download') === '1') {
            return PdfRenderer::estimate($record)->download($record->estimate_number . '.pdf');
        }

        return view('public.estimate', compact('record', 'company'));
    }
}
