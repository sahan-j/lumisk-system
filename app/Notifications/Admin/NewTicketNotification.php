<?php

namespace App\Notifications\Admin;

use App\Models\Ticket;
use App\Notifications\BaseDatabaseNotification;

class NewTicketNotification extends BaseDatabaseNotification
{
    public function __construct(public Ticket $ticket) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Support Ticket',
            'message' => ($this->ticket->client?->name ?? 'A client') . " opened a ticket: {$this->ticket->subject}",
            'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
            'color' => '#ef4444',
            'url' => route('admin.tickets.show', $this->ticket),
            'type' => 'new_ticket',
        ];
    }
}
