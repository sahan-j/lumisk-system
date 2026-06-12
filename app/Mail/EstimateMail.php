<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Estimate;
use App\Support\PdfRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EstimateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Estimate $estimate,
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
            view: 'emails.estimate',
            with: [
                'estimate'     => $this->estimate,
                'company'      => $this->company,
                'emailMessage' => $this->emailMessage,
            ],
        );
    }

    public function attachments(): array
    {
        $pdf = PdfRenderer::estimate($this->estimate);

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                $this->estimate->estimate_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
