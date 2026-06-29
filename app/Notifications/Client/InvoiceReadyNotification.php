<?php

namespace App\Notifications\Client;

use App\Models\Invoice;
use App\Notifications\BaseDatabaseNotification;

class InvoiceReadyNotification extends BaseDatabaseNotification
{
    public function __construct(public Invoice $invoice) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Invoice Ready',
            'message' => "Invoice {$this->invoice->invoice_number} for " . money($this->invoice->total) . ' is ready for review',
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'color' => '#6d5cff',
            'url' => route('portal.invoices.show', $this->invoice),
            'type' => 'invoice_ready',
        ];
    }
}
