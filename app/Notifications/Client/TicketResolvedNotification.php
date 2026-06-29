<?php

namespace App\Notifications\Client;

use App\Models\Ticket;
use App\Notifications\BaseDatabaseNotification;

class TicketResolvedNotification extends BaseDatabaseNotification
{
    public function __construct(public Ticket $ticket) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Ticket Resolved',
            'message' => "Your support ticket '{$this->ticket->subject}' has been resolved",
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => '#10b981',
            'url' => route('portal.tickets.show', $this->ticket),
            'type' => 'ticket_resolved',
        ];
    }
}
