<?php

namespace App\Notifications\Client;

use App\Models\Ticket;
use App\Notifications\BaseDatabaseNotification;

class TicketRepliedClientNotification extends BaseDatabaseNotification
{
    public function __construct(public Ticket $ticket) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Support Ticket Update',
            'message' => "We replied to your ticket: {$this->ticket->subject}",
            'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
            'color' => '#6d5cff',
            'url' => route('portal.tickets.show', $this->ticket),
            'type' => 'ticket_replied',
        ];
    }
}
