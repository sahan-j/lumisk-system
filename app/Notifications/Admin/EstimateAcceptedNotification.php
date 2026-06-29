<?php

namespace App\Notifications\Admin;

use App\Models\Estimate;
use App\Notifications\BaseDatabaseNotification;

class EstimateAcceptedNotification extends BaseDatabaseNotification
{
    public function __construct(public Estimate $estimate) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Estimate Accepted',
            'message' => ($this->estimate->client?->name ?? 'A client') . " accepted estimate {$this->estimate->estimate_number} — " . money($this->estimate->total),
            'icon' => 'M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5',
            'color' => '#10b981',
            'url' => route('admin.estimates.show', $this->estimate),
            'type' => 'estimate_accepted',
        ];
    }
}
