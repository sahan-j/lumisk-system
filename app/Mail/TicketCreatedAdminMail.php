<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCreatedAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public Company $company,
        public string $messageBody = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Support Ticket [{$this->ticket->ticket_number}] — {$this->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-created-admin');
    }
}
