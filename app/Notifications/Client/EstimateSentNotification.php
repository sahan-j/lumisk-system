<?php

namespace App\Notifications\Client;

use App\Models\Estimate;
use App\Notifications\BaseDatabaseNotification;

class EstimateSentNotification extends BaseDatabaseNotification
{
    public function __construct(public Estimate $estimate) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Estimate to Review',
            'message' => "Estimate {$this->estimate->estimate_number} for " . money($this->estimate->total) . ' is awaiting your approval',
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            'color' => '#00d4ff',
            'url' => route('portal.estimates.show', $this->estimate),
            'type' => 'estimate_sent',
        ];
    }
}
