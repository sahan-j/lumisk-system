<?php

namespace App\Livewire\Admin\Invoices;

use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Invoice')]
class InvoiceShow extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load('items', 'client', 'payments');
    }

    public function setStatus(string $status): void
    {
        if (! in_array($status, Invoice::STATUSES)) {
            return;
        }

        $this->invoice->update(['status' => $status]);
        $this->invoice->refresh();
        $this->dispatch('toast', type: 'success', message: 'Status updated to ' . ucfirst($status) . '.');
    }

    public function openSendEmail(): void
    {
        $this->dispatch('open-send-email', type: 'invoice', id: $this->invoice->id);
    }

    #[On('email-sent')]
    public function refreshAfterSend(): void
    {
        $this->invoice->refresh();
    }

    #[On('payment-recorded')]
    public function refreshAfterPayment(): void
    {
        $this->invoice = Invoice::with('items', 'client', 'payments')->findOrFail($this->invoice->id);
    }

    public function render()
    {
        return view('livewire.admin.invoices.invoice-show');
    }
}
