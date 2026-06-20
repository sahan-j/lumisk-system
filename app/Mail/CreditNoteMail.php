<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\CreditNote;
use App\Support\PdfRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreditNoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CreditNote $creditNote,
        public Company $company,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Credit Note {$this->creditNote->credit_note_number} from {$this->company->name}",
            replyTo: $this->company->reply_to_email ? [$this->company->reply_to_email] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.credit-note',
            with: [
                'creditNote' => $this->creditNote,
                'company' => $this->company,
            ],
        );
    }

    public function attachments(): array
    {
        $pdf = PdfRenderer::creditNote($this->creditNote);

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                $this->creditNote->credit_note_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
