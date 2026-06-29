<?php

namespace App\Notifications\Client;

use App\Models\Subscription;
use App\Notifications\BaseDatabaseNotification;

class RenewalReminderNotification extends BaseDatabaseNotification
{
    public function __construct(public Subscription $subscription) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Subscription Renewal Due Soon',
            'message' => "{$this->subscription->name} renews on " . ($this->subscription->next_billing_date?->format('M d, Y') ?? 'soon') . ' — ' . money($this->subscription->amount),
            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'color' => '#f59e0b',
            'url' => route('portal.subscriptions.show', $this->subscription),
            'type' => 'renewal_reminder',
        ];
    }
}
