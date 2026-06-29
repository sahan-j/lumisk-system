<?php

namespace App\Notifications\Client;

use App\Models\Invoice;
use App\Models\Payment;
use App\Notifications\BaseDatabaseNotification;

class PaymentConfirmedNotification extends BaseDatabaseNotification
{
    public function __construct(public Payment $payment, public Invoice $invoice) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Payment Confirmed',
            'message' => 'Payment of ' . money($this->payment->amount) . " for {$this->invoice->invoice_number} confirmed",
            'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            'color' => '#10b981',
            'url' => route('portal.invoices.show', $this->invoice),
            'type' => 'payment_confirmed',
        ];
    }
}
