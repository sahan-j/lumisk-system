<?php

namespace App\Livewire\Admin\Invoices;

use App\Models\Invoice;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
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

    public function duplicate()
    {
        $copy = DB::transaction(function () {
            $copy = $this->invoice->replicate(['invoice_number']);
            $copy->invoice_number = DocumentNumberService::nextInvoiceNumber();
            $copy->status = 'draft';
            $copy->issue_date = now();
            $copy->due_date = now()->addDays(14);
            $copy->save();

            foreach ($this->invoice->items as $item) {
                $copy->items()->create($item->only(['name', 'description', 'quantity', 'unit_price', 'total', 'order']));
            }

            return $copy;
        });

        $this->dispatch('toast', type: 'success', message: 'Invoice duplicated.');

        return $this->redirect(route('admin.invoices.edit', $copy), navigate: true);
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
