<?php

namespace App\Livewire\Admin\CreditNotes;

use App\Mail\CreditNoteMail;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\CreditNote;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Credit Note')]
class CreditNoteShow extends Component
{
    public CreditNote $creditNote;

    // Apply-to-invoice form.
    public ?int $applyInvoiceId = null;
    public ?float $applyAmount = null;

    // Void confirmation.
    public bool $confirmingVoid = false;

    public function mount(CreditNote $creditNote): void
    {
        $this->creditNote = $creditNote;
    }

    public function issue(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('credit-notes.edit'), 403);

        if ($this->creditNote->status !== 'draft') {
            $this->dispatch('toast', type: 'error', message: 'Only draft credit notes can be issued.');

            return;
        }

        $this->creditNote->update(['status' => 'issued']);
        $this->creditNote->refresh();

        ActivityLog::log('credit_note_issued',
            "Credit note {$this->creditNote->credit_note_number} issued",
            ['subject_type' => 'CreditNote', 'subject_id' => $this->creditNote->id,
             'subject_label' => $this->creditNote->credit_note_number, 'client_id' => $this->creditNote->client_id]);

        $this->dispatch('toast', type: 'success', message: 'Credit note issued.');
    }

    public function void(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('credit-notes.edit'), 403);

        if ((float) $this->creditNote->amount_applied > 0) {
            $this->confirmingVoid = false;
            $this->dispatch('toast', type: 'error', message: 'Cannot void a credit note that has been applied.');

            return;
        }

        $this->creditNote->update(['status' => 'void']);
        $this->creditNote->refresh();
        $this->confirmingVoid = false;

        ActivityLog::log('credit_note_void',
            "Credit note {$this->creditNote->credit_note_number} voided",
            ['subject_type' => 'CreditNote', 'subject_id' => $this->creditNote->id,
             'subject_label' => $this->creditNote->credit_note_number, 'client_id' => $this->creditNote->client_id]);

        $this->dispatch('toast', type: 'success', message: 'Credit note voided.');
    }

    public function apply(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('credit-notes.edit'), 403);

        if ($this->creditNote->status !== 'issued') {
            $this->dispatch('toast', type: 'error', message: 'Only issued credit notes can be applied.');

            return;
        }

        $validated = $this->validate([
            'applyInvoiceId' => ['required', 'exists:invoices,id'],
            'applyAmount' => ['required', 'numeric', 'min:0.01', 'max:' . $this->creditNote->amount_remaining],
        ], [
            'applyInvoiceId.required' => 'Select an invoice to apply to.',
            'applyAmount.max' => 'Amount exceeds the remaining credit balance.',
        ]);

        $invoice = Invoice::findOrFail($validated['applyInvoiceId']);
        $this->creditNote->applyToInvoice($invoice, (float) $validated['applyAmount']);
        $this->creditNote->refresh();

        ActivityLog::log('credit_note_applied',
            "Credit note {$this->creditNote->credit_note_number} applied to {$invoice->invoice_number}",
            ['subject_type' => 'CreditNote', 'subject_id' => $this->creditNote->id,
             'subject_label' => $this->creditNote->credit_note_number, 'client_id' => $this->creditNote->client_id]);

        $this->reset('applyInvoiceId', 'applyAmount');
        $this->dispatch('toast', type: 'success', message: "Applied to {$invoice->invoice_number}.");
    }

    public function sendToClient(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('credit-notes.view'), 403);

        $email = $this->creditNote->client?->email;
        if (! $email) {
            $this->dispatch('toast', type: 'error', message: 'This client has no email address.');

            return;
        }

        Mail::to($email)->send(new CreditNoteMail($this->creditNote, Company::settings()));

        $this->dispatch('toast', type: 'success', message: 'Credit note emailed to ' . $email . '.');
    }

    public function render()
    {
        $this->creditNote->load(['client', 'invoice', 'items', 'applications.invoice']);

        // Invoices for this client that still have an outstanding balance.
        $openInvoices = Invoice::with('payments')
            ->where('client_id', $this->creditNote->client_id)
            ->whereIn('status', ['sent', 'overdue', 'draft'])
            ->latest()
            ->get()
            ->filter(fn ($inv) => $inv->outstanding_balance > 0)
            ->values();

        return view('livewire.admin.credit-notes.credit-note-show', [
            'openInvoices' => $openInvoices,
        ]);
    }
}
