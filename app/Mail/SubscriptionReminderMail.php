<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public Company $company,
        public int $days,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Upcoming renewal in {$this->days} days — {$this->subscription->name}",
            replyTo: $this->company->reply_to_email ? [$this->company->reply_to_email] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-reminder',
            with: [
                'subscription' => $this->subscription,
                'company' => $this->company,
                'days' => $this->days,
            ],
        );
    }
}
