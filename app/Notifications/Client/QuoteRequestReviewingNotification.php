<?php

namespace App\Notifications\Client;

use App\Models\QuoteRequest;
use App\Notifications\BaseDatabaseNotification;

class QuoteRequestReviewingNotification extends BaseDatabaseNotification
{
    public function __construct(public QuoteRequest $quoteRequest) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Quote Request Under Review',
            'message' => "Your request '{$this->quoteRequest->title}' is now being reviewed by our team.",
            'icon' => 'M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
            'color' => '#6d5cff',
            'url' => route('portal.quote-requests.show', $this->quoteRequest),
            'type' => 'quote_request_reviewing',
        ];
    }
}
