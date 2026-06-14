<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public Company $company,
        public int $daysOverdue = 0,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->daysOverdue > 0
            ? "Payment Reminder ({$this->daysOverdue} days overdue) — {$this->invoice->invoice_number}"
            : "Payment Due — Invoice {$this->invoice->invoice_number}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.overdue-reminder');
    }
}
