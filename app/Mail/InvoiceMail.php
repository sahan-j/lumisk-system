<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Invoice;
use App\Support\PdfRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public Company $company,
        public string $emailSubject,
        public string $emailMessage,
        public ?string $ccEmail = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
            cc: $this->ccEmail ? [$this->ccEmail] : [],
            replyTo: $this->company->reply_to_email ? [$this->company->reply_to_email] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice'      => $this->invoice,
                'company'      => $this->company,
                'emailMessage' => $this->emailMessage,
            ],
        );
    }

    public function attachments(): array
    {
        $pdf = PdfRenderer::invoice($this->invoice);

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                $this->invoice->invoice_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
