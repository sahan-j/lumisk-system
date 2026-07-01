<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Notifications\Admin\NewQuoteRequestNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalQuoteRequestController extends Controller
{
    public function index(): View
    {
        $client = Auth::guard('client')->user();

        $requests = QuoteRequest::where('client_id', $client->id)
            ->with('convertedEstimate')
            ->latest()
            ->paginate(10);

        $stats = [
            'total'     => QuoteRequest::where('client_id', $client->id)->count(),
            'pending'   => QuoteRequest::where('client_id', $client->id)->whereIn('status', ['pending', 'reviewing'])->count(),
            'converted' => QuoteRequest::where('client_id', $client->id)->where('status', 'converted')->count(),
        ];

        return view('portal.quote-requests.index', compact('requests', 'stats'));
    }

    public function create(): View
    {
        return view('portal.quote-requests.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $client = Auth::guard('client')->user();

        $validated = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string', 'min:20', 'max:5000'],
            'service_type'  => ['required', 'in:' . implode(',', QuoteRequest::SERVICE_TYPES)],
            'budget_range'  => ['required', 'in:' . implode(',', QuoteRequest::BUDGET_RANGES)],
            'timeline'      => ['required', 'in:' . implode(',', QuoteRequest::TIMELINES)],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ]);

        // Store uploads on the private disk with UUID names (served only via controller).
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $stored = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('quote-requests/' . $client->id, $stored, 'private');
                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
        }

        $quoteRequest = QuoteRequest::create([
            'request_number' => QuoteRequest::generateNumber(),
            'client_id'      => $client->id,
            'title'          => $validated['title'],
            'description'    => $validated['description'],
            'service_type'   => $validated['service_type'],
            'budget_range'   => $validated['budget_range'],
            'timeline'       => $validated['timeline'],
            'attachments'    => $attachmentPaths ?: null,
            'status'         => 'pending',
        ]);

        User::all()->each(fn ($admin) => $admin->notify(new NewQuoteRequestNotification($quoteRequest)));

        ActivityLog::log(
            'quote_request_created',
            "Quote request '{$quoteRequest->title}' submitted by {$client->name}",
            [
                'client_id'     => $client->id,
                'causer_type'   => 'client',
                'causer_name'   => $client->name,
                'subject_label' => $quoteRequest->request_number,
            ]
        );

        return redirect()
            ->route('portal.quote-requests.show', $quoteRequest)
            ->with('success', "Quote request {$quoteRequest->request_number} submitted! We'll get back to you soon.");
    }

    public function show(QuoteRequest $quoteRequest): View
    {
        abort_unless($quoteRequest->client_id === Auth::guard('client')->id(), 403);

        $quoteRequest->load('convertedEstimate');

        return view('portal.quote-requests.show', compact('quoteRequest'));
    }

    public function downloadAttachment(QuoteRequest $quoteRequest, int $index): StreamedResponse
    {
        abort_unless($quoteRequest->client_id === Auth::guard('client')->id(), 403);

        $attachment = $quoteRequest->attachments[$index] ?? abort(404);

        return Storage::disk('private')->download($attachment['path'], $attachment['name']);
    }
}
