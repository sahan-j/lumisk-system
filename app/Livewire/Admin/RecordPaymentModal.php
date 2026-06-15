<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Attributes\On;
use Livewire\Component;

class RecordPaymentModal extends Component
{
    public bool $show = false;
    public ?int $invoiceId = null;
    public ?Invoice $invoice = null;

    public ?float $amount = null;
    public string $paymentMethod = 'bank_transfer';
    public string $paymentDate = '';
    public string $referenceNumber = '';
    public string $note = '';
    public bool $isSaving = false;

    #[On('open-record-payment')]
    public function handleOpen(int $invoiceId): void
    {
        $this->open($invoiceId);
    }

    public function open(int $invoiceId): void
    {
        $this->resetValidation();
        $this->reset(['amount', 'referenceNumber', 'note']);
        $this->paymentMethod = 'bank_transfer';

        $this->invoiceId = $invoiceId;
        $this->loadInvoice();

        // Pre-fill with the outstanding balance.
        $this->amount = $this->invoice->outstanding_balance > 0
            ? $this->invoice->outstanding_balance
            : null;
        $this->paymentDate = now()->format('Y-m-d');
        $this->show = true;
    }

    protected function loadInvoice(): void
    {
        $this->invoice = Invoice::with(['payments', 'client'])->findOrFail($this->invoiceId);
    }

    protected function rules(): array
    {
        // Allow paying up to the outstanding balance (guard against tiny float drift).
        $max = round($this->invoice->outstanding_balance + 0.001, 2);

        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $max],
            'paymentMethod' => ['required', 'in:' . implode(',', Payment::METHODS)],
            'paymentDate' => ['required', 'date'],
            'referenceNumber' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected array $messages = [
        'amount.max' => 'Amount cannot exceed the outstanding balance.',
    ];

    public function save(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('payments.record'), 403);

        $this->loadInvoice();
        $validated = $this->validate();

        $this->isSaving = true;

        try {
            $payment = $this->invoice->payments()->create([
                'amount' => $validated['amount'],
                'payment_method' => $validated['paymentMethod'],
                'payment_date' => $validated['paymentDate'],
                'reference_number' => $validated['referenceNumber'] ?: null,
                'note' => $validated['note'] ?: null,
            ]);

            ActivityLog::log('payment_recorded',
                'Payment of ' . money($validated['amount']) . " recorded for {$this->invoice->invoice_number}",
                ['subject_type' => 'Invoice', 'subject_id' => $this->invoice->id,
                 'subject_label' => $this->invoice->invoice_number, 'client_id' => $this->invoice->client_id,
                 'meta' => ['amount' => $validated['amount'], 'method' => $payment->payment_method_label]]);

            $this->loadInvoice();

            if ($this->invoice->is_fully_paid) {
                $this->invoice->update(['status' => 'paid']);
                ActivityLog::log('invoice_paid',
                    "Invoice {$this->invoice->invoice_number} fully paid",
                    ['subject_type' => 'Invoice', 'subject_id' => $this->invoice->id,
                     'subject_label' => $this->invoice->invoice_number, 'client_id' => $this->invoice->client_id]);
            } elseif ($this->invoice->status === 'draft') {
                $this->invoice->update(['status' => 'sent']);
            }

            $this->dispatch('payment-recorded');
            $this->dispatch('toast', type: 'success', message: 'Payment of ' . money($validated['amount']) . ' recorded.');

            $this->reset(['amount', 'referenceNumber', 'note']);
            $this->show = false;
        } finally {
            $this->isSaving = false;
        }
    }

    public function deletePayment(int $paymentId): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('payments.delete'), 403);

        $this->loadInvoice();

        $payment = $this->invoice->payments->firstWhere('id', $paymentId);
        if (! $payment) {
            return;
        }

        $payment->delete();
        $this->loadInvoice();

        // If it was marked paid but is no longer fully covered, revert to sent.
        if ($this->invoice->status === 'paid' && ! $this->invoice->is_fully_paid) {
            $this->invoice->update(['status' => 'sent']);
        }

        $this->dispatch('payment-recorded');
        $this->dispatch('toast', type: 'success', message: 'Payment removed.');
    }

    public function render()
    {
        return view('livewire.admin.record-payment-modal');
    }
}
