<?php

namespace App\Notifications\Client;

use App\Models\Estimate;
use App\Models\QuoteRequest;
use App\Notifications\BaseDatabaseNotification;

class QuoteRequestConvertedNotification extends BaseDatabaseNotification
{
    public function __construct(public QuoteRequest $quoteRequest, public Estimate $estimate) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Estimate Ready for Review',
            'message' => "We've prepared an estimate for your quote request '{$this->quoteRequest->title}'. Please review it.",
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'color' => '#10b981',
            'url' => route('portal.estimates.show', $this->estimate),
            'type' => 'quote_request_converted',
        ];
    }
}
