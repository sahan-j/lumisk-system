<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Estimate;
use App\Models\QuoteRequest;
use App\Notifications\Client\QuoteRequestConvertedNotification;
use App\Notifications\Client\QuoteRequestDeclinedNotification;
use App\Notifications\Client\QuoteRequestReviewingNotification;
use App\Services\DocumentNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminQuoteRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $requests = QuoteRequest::with('client')
            ->when(in_array($status, QuoteRequest::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending'     => QuoteRequest::where('status', 'pending')->count(),
            'reviewing'   => QuoteRequest::where('status', 'reviewing')->count(),
            'total_today' => QuoteRequest::whereDate('created_at', today())->count(),
            'total'       => QuoteRequest::count(),
        ];

        return view('admin.quote-requests.index', compact('requests', 'stats', 'status'));
    }

    public function show(QuoteRequest $quoteRequest): View
    {
        $quoteRequest->load(['client', 'convertedEstimate']);

        // Opening a pending request moves it to "reviewing" and notifies the client.
        if ($quoteRequest->status === 'pending') {
            $quoteRequest->update(['status' => 'reviewing']);
            $quoteRequest->client->notify(new QuoteRequestReviewingNotification($quoteRequest));
        }

        return view('admin.quote-requests.show', compact('quoteRequest'));
    }

    public function convertToEstimate(Request $request, QuoteRequest $quoteRequest): RedirectResponse
    {
        abort_if($quoteRequest->status === 'converted', 400, 'Already converted.');

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $estimate = DB::transaction(function () use ($quoteRequest, $validated) {
            $company = Company::settings();

            $estimate = Estimate::create([
                'estimate_number' => DocumentNumberService::nextEstimateNumber(),
                'client_id'       => $quoteRequest->client_id,
                'status'          => 'draft',
                'issue_date'      => today(),
                'expiry_date'     => today()->addDays(30),
                'tax_rate'        => $company->default_tax_rate ?? 0,
                'discount_amount' => 0,
                'terms'           => $company->default_terms,
                'notes'           => "Quote Request: {$quoteRequest->request_number}\n\n"
                    . "Service: {$quoteRequest->service_type_label}\n"
                    . "Budget Range: {$quoteRequest->budget_range_label}\n"
                    . "Timeline: {$quoteRequest->timeline_label}\n\n"
                    . "Client Requirements:\n{$quoteRequest->description}",
            ]);

            // Placeholder line item — admin fills in the real price on the edit screen.
            $estimate->items()->create([
                'name'        => $quoteRequest->title,
                'description' => $quoteRequest->service_type_label,
                'quantity'    => 1,
                'unit_price'  => 0,
                'total'       => 0,
                'order'       => 0,
            ]);

            $estimate->load('items');
            $estimate->recalculateTotals();
            $estimate->save();

            $quoteRequest->update([
                'status'                => 'converted',
                'converted_estimate_id' => $estimate->id,
                'admin_note'            => $validated['note'] ?? null,
            ]);

            $quoteRequest->client->notify(new QuoteRequestConvertedNotification($quoteRequest, $estimate));

            ActivityLog::log(
                'quote_request_converted',
                "Quote request {$quoteRequest->request_number} converted to estimate {$estimate->estimate_number}",
                [
                    'client_id'     => $quoteRequest->client_id,
                    'subject_label' => $quoteRequest->request_number,
                ]
            );

            return $estimate;
        });

        return redirect()
            ->route('admin.estimates.edit', $estimate)
            ->with('success', 'Estimate created from quote request! Fill in the prices and send it to the client.');
    }

    public function decline(Request $request, QuoteRequest $quoteRequest): RedirectResponse
    {
        $validated = $request->validate([
            'declined_reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $quoteRequest->update([
            'status'          => 'declined',
            'declined_reason' => $validated['declined_reason'],
        ]);

        $quoteRequest->client->notify(new QuoteRequestDeclinedNotification($quoteRequest));

        return redirect()
            ->route('admin.quote-requests.show', $quoteRequest)
            ->with('success', 'Quote request declined and the client has been notified.');
    }

    public function downloadAttachment(QuoteRequest $quoteRequest, int $index): StreamedResponse
    {
        $attachment = $quoteRequest->attachments[$index] ?? abort(404);

        return Storage::disk('private')->download($attachment['path'], $attachment['name']);
    }
}
