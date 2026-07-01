<?php

namespace App\Notifications\Client;

use App\Models\QuoteRequest;
use App\Notifications\BaseDatabaseNotification;

class QuoteRequestDeclinedNotification extends BaseDatabaseNotification
{
    public function __construct(public QuoteRequest $quoteRequest) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Quote Request Update',
            'message' => "Your quote request '{$this->quoteRequest->title}' could not be fulfilled at this time.",
            'icon' => 'M6 18L18 6M6 6l12 12',
            'color' => '#ef4444',
            'url' => route('portal.quote-requests.show', $this->quoteRequest),
            'type' => 'quote_request_declined',
        ];
    }
}
