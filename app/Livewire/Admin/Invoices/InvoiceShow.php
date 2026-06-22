<?php

namespace App\Livewire\Admin\Invoices;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
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

    public bool $showTemplateModal = false;
    public string $templateName = '';

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load('items', 'client', 'payments', 'recurringChildren');
    }

    public function setStatus(string $status): void
    {
        if (! in_array($status, Invoice::STATUSES)) {
            return;
        }

        $this->invoice->update(['status' => $status]);

        // Deduct stock the first time an invoice becomes sent or paid.
        if (in_array($status, ['sent', 'paid'], true)) {
            $this->invoice->deductStock();
        }

        $this->invoice->refresh();

        if (in_array($status, ['sent', 'paid'], true)) {
            ActivityLog::log("invoice_{$status}",
                "Invoice {$this->invoice->invoice_number} marked as {$status}",
                ['subject_type' => 'Invoice', 'subject_id' => $this->invoice->id,
                 'subject_label' => $this->invoice->invoice_number, 'client_id' => $this->invoice->client_id]);
        }

        $this->dispatch('toast', type: 'success', message: 'Status updated to ' . ucfirst($status) . '.');
    }

    public function openSendEmail(): void
    {
        $this->dispatch('open-send-email', type: 'invoice', id: $this->invoice->id);
    }

    public function saveAsTemplate(): void
    {
        $this->validate(['templateName' => ['required', 'string', 'max:255']]);

        $this->invoice->loadMissing('items');

        DB::transaction(function () {
            $template = InvoiceTemplate::create([
                'name' => $this->templateName,
                'description' => "Saved from invoice {$this->invoice->invoice_number}",
                'type' => 'invoice',
                'tax_rate' => $this->invoice->tax_rate,
                'discount_amount' => $this->invoice->discount_amount,
                'notes' => $this->invoice->notes,
                'terms' => $this->invoice->terms,
                'currency_code' => $this->invoice->currency_code ?: 'LKR',
                'created_by' => auth()->user()?->name,
            ]);

            foreach ($this->invoice->items as $item) {
                $template->items()->create([
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                    'sort_order' => $item->order,
                ]);
            }
        });

        $this->showTemplateModal = false;
        $name = $this->templateName;
        $this->templateName = '';
        $this->dispatch('toast', type: 'success', message: "Saved as template “{$name}”.");
    }

    #[On('email-sent')]
    public function refreshAfterSend(): void
    {
        $this->invoice->refresh();
    }

    public function stopRecurring(): void
    {
        $this->invoice->update([
            'is_recurring' => false,
            'recurring_cycle' => null,
            'recurring_next_date' => null,
            'recurring_end_date' => null,
        ]);

        $this->invoice->refresh();
        $this->dispatch('toast', type: 'success', message: 'Recurring invoices stopped.');
    }

    #[On('payment-recorded')]
    public function refreshAfterPayment(): void
    {
        $this->invoice = Invoice::with('items', 'client', 'payments', 'recurringChildren')->findOrFail($this->invoice->id);
    }

    public function render()
    {
        return view('livewire.admin.invoices.invoice-show');
    }
}
