<?php

namespace App\Notifications\Admin;

use App\Models\Ticket;
use App\Notifications\BaseDatabaseNotification;

class TicketRepliedNotification extends BaseDatabaseNotification
{
    public function __construct(public Ticket $ticket) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Client Replied to Ticket',
            'message' => ($this->ticket->client?->name ?? 'A client') . " replied on {$this->ticket->ticket_number}: {$this->ticket->subject}",
            'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
            'color' => '#f59e0b',
            'url' => route('admin.tickets.show', $this->ticket),
            'type' => 'ticket_replied',
        ];
    }
}
