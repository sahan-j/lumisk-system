<?php

namespace App\Notifications\Admin;

use App\Models\Estimate;
use App\Notifications\BaseDatabaseNotification;

class EstimateRejectedNotification extends BaseDatabaseNotification
{
    public function __construct(public Estimate $estimate) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Estimate Rejected',
            'message' => ($this->estimate->client?->name ?? 'A client') . " rejected estimate {$this->estimate->estimate_number}",
            'icon' => 'M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.737 3h4.017c.163 0 .326.02.485.06L17 4m-7 10v5a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5',
            'color' => '#ef4444',
            'url' => route('admin.estimates.show', $this->estimate),
            'type' => 'estimate_rejected',
        ];
    }
}
