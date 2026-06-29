<?php

namespace App\Notifications\Admin;

use App\Models\Invoice;
use App\Notifications\BaseDatabaseNotification;

class InvoiceOverdueNotification extends BaseDatabaseNotification
{
    public function __construct(public Invoice $invoice) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Invoice Overdue',
            'message' => "{$this->invoice->invoice_number} for " . ($this->invoice->client?->name ?? 'a client') . ' is now overdue — ' . money($this->invoice->total),
            'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            'color' => '#ef4444',
            'url' => route('admin.invoices.show', $this->invoice),
            'type' => 'invoice_overdue',
        ];
    }
}
