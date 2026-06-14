<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PortalPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public function __construct(
        public readonly Client $client,
        public readonly string $token,
    ) {
        $this->resetUrl = route('portal.password.reset', [
            'token' => $token,
            'email' => $client->email,
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reset Your Password — Lumisk System');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.portal-password-reset');
    }
}
