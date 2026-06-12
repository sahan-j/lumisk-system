<?php

namespace App\Livewire\Admin\Estimates;

use App\Models\Estimate;
use App\Models\Invoice;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Estimate')]
class EstimateShow extends Component
{
    public Estimate $estimate;

    public function mount(Estimate $estimate): void
    {
        $this->estimate = $estimate->load('items', 'client');
    }

    public function setStatus(string $status): void
    {
        if (! in_array($status, Estimate::STATUSES)) {
            return;
        }

        $this->estimate->update(['status' => $status]);
        $this->estimate->refresh();
        $this->dispatch('toast', type: 'success', message: 'Status updated to ' . ucfirst($status) . '.');
    }

    public function convertToInvoice()
    {
        $invoice = DB::transaction(function () {
            $invoice = new Invoice();
            $invoice->invoice_number = DocumentNumberService::nextInvoiceNumber();
            $invoice->fill([
                'client_id' => $this->estimate->client_id,
                'status' => 'draft',
                'issue_date' => now(),
                'due_date' => now()->addDays(14),
                'tax_rate' => $this->estimate->tax_rate,
                'discount_amount' => $this->estimate->discount_amount,
                'notes' => $this->estimate->notes,
                'terms' => $this->estimate->terms,
            ]);
            $invoice->save();

            foreach ($this->estimate->items as $item) {
                $invoice->items()->create($item->only(['name', 'description', 'quantity', 'unit_price', 'total', 'order']));
            }

            $invoice->load('items');
            $invoice->recalculateTotals();
            $invoice->save();

            return $invoice;
        });

        $this->dispatch('toast', type: 'success', message: 'Converted to invoice ' . $invoice->invoice_number . '.');

        return $this->redirect(route('admin.invoices.show', $invoice), navigate: true);
    }

    public function duplicate()
    {
        $copy = DB::transaction(function () {
            $copy = $this->estimate->replicate(['estimate_number', 'client_note']);
            $copy->estimate_number = DocumentNumberService::nextEstimateNumber();
            $copy->status = 'draft';
            $copy->issue_date = now();
            $copy->expiry_date = now()->addDays((int) company_settings()->estimate_expiry_days);
            $copy->client_note = null;
            $copy->save();

            foreach ($this->estimate->items as $item) {
                $copy->items()->create($item->only(['name', 'description', 'quantity', 'unit_price', 'total', 'order']));
            }

            return $copy;
        });

        $this->dispatch('toast', type: 'success', message: 'Estimate duplicated.');

        return $this->redirect(route('admin.estimates.edit', $copy), navigate: true);
    }

    public function openSendEmail(): void
    {
        $this->dispatch('open-send-email', type: 'estimate', id: $this->estimate->id);
    }

    #[On('email-sent')]
    public function refreshAfterSend(): void
    {
        $this->estimate->refresh();
    }

    public function render()
    {
        return view('livewire.admin.estimates.estimate-show');
    }
}
