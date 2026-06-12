<?php

namespace App\Livewire\Portal\Invoices;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('Invoice')]
class InvoiceShow extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        // Clients may only view their own invoices.
        abort_unless($invoice->client_id === Auth::guard('client')->id(), 403);

        $this->invoice = $invoice->load('items', 'client', 'payments');
    }

    public function render()
    {
        return view('livewire.portal.invoices.invoice-show');
    }
}
