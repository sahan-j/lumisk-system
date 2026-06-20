<?php

namespace App\Livewire\Admin\CreditNotes;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\CreditNote;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Credit Note')]
class CreditNoteForm extends Component
{
    public ?CreditNote $creditNote = null;

    public ?int $client_id = null;
    public ?int $invoice_id = null;
    public string $issue_date = '';
    public string $reason = '';
    public ?float $tax_rate = 0;
    public ?string $notes = null;

    /** @var array<int, array{name:string, description:?string, quantity:float, unit_price:float}> */
    public array $items = [];

    public function mount(?CreditNote $creditNote = null): void
    {
        if ($creditNote && $creditNote->exists) {
            $this->creditNote = $creditNote->load('items');
            $this->client_id = $creditNote->client_id;
            $this->invoice_id = $creditNote->invoice_id;
            $this->issue_date = $creditNote->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d');
            $this->reason = $creditNote->reason;
            $this->tax_rate = (float) $creditNote->tax_rate;
            $this->notes = $creditNote->notes;
            $this->items = $creditNote->items->map(fn ($i) => [
                'name' => $i->name,
                'description' => $i->description,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
            ])->toArray();

            return;
        }

        $this->issue_date = now()->format('Y-m-d');
        $this->tax_rate = (float) company_settings()->default_tax_rate;
        $this->items = [$this->blankItem()];

        // Shortcut: /admin/invoices/{invoice}/credit-note pre-fills from the invoice.
        $invoiceId = (int) request('invoice');
        if ($invoiceId) {
            $invoice = Invoice::with('items')->find($invoiceId);
            if ($invoice) {
                $this->invoice_id = $invoice->id;
                $this->client_id = $invoice->client_id;
                $this->tax_rate = (float) $invoice->tax_rate;
                $this->importInvoiceItems($invoice);
            }
        }
    }

    private function blankItem(): array
    {
        return ['name' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0];
    }

    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if (empty($this->items)) {
            $this->items = [$this->blankItem()];
        }
    }

    /** When an invoice is chosen, default the client to match. */
    public function updatedInvoiceId($value): void
    {
        if ($value && $invoice = Invoice::find($value)) {
            $this->client_id = $invoice->client_id;
        }
    }

    /** Copy the referenced invoice's line items into the credit note. */
    public function importFromInvoice(): void
    {
        if (! $this->invoice_id) {
            $this->dispatch('toast', type: 'error', message: 'Select a reference invoice first.');

            return;
        }

        $invoice = Invoice::with('items')->find($this->invoice_id);
        if ($invoice) {
            $this->client_id = $invoice->client_id;
            $this->tax_rate = (float) $invoice->tax_rate;
            $this->importInvoiceItems($invoice);
            $this->dispatch('toast', type: 'success', message: 'Items imported from ' . $invoice->invoice_number . '.');
        }
    }

    private function importInvoiceItems(Invoice $invoice): void
    {
        $imported = $invoice->items->map(fn ($i) => [
            'name' => $i->name,
            'description' => $i->description,
            'quantity' => (float) $i->quantity,
            'unit_price' => (float) $i->unit_price,
        ])->toArray();

        $this->items = $imported ?: [$this->blankItem()];
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn ($i) => (float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0));
    }

    public function getTaxAmountProperty(): float
    {
        return round($this->subtotal * (($this->tax_rate ?? 0) / 100), 2);
    }

    public function getTotalProperty(): float
    {
        return round($this->subtotal + $this->taxAmount, 2);
    }

    public function save()
    {
        abort_unless((bool) auth()->user()?->hasPermission($this->creditNote ? 'credit-notes.edit' : 'credit-notes.create'), 403);

        $validated = $this->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'issue_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:500'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.*.name.required' => 'Item name is required.',
            'client_id.required' => 'Please select a client.',
            'reason.required' => 'Please give a reason for the credit note.',
        ]);

        $isNew = ! ($this->creditNote && $this->creditNote->exists);

        $creditNote = DB::transaction(function () use ($validated) {
            $creditNote = $this->creditNote ?? new CreditNote(['status' => 'draft']);

            if (! $creditNote->exists) {
                $creditNote->credit_note_number = CreditNote::generateNumber();
                $creditNote->status = 'draft';
            }

            $creditNote->fill([
                'client_id' => $validated['client_id'],
                'invoice_id' => $validated['invoice_id'] ?? null,
                'issue_date' => $validated['issue_date'],
                'reason' => $validated['reason'],
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'notes' => $this->notes,
            ]);
            $creditNote->save();

            $creditNote->items()->delete();
            foreach (array_values($this->items) as $order => $item) {
                $creditNote->items()->create([
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => round((float) $item['quantity'] * (float) $item['unit_price'], 2),
                    'sort_order' => $order,
                ]);
            }

            $creditNote->load('items');
            $creditNote->recalculateTotals();
            $creditNote->save();

            return $creditNote;
        });

        if ($isNew) {
            $creditNote->loadMissing('client');
            ActivityLog::log('credit_note_created',
                "Credit note {$creditNote->credit_note_number} created for " . ($creditNote->client?->name ?? 'a client'),
                ['subject_type' => 'CreditNote', 'subject_id' => $creditNote->id,
                 'subject_label' => $creditNote->credit_note_number, 'client_id' => $creditNote->client_id]);
        }

        $this->dispatch('toast', type: 'success', message: 'Credit note saved.');

        return $this->redirect(route('admin.credit-notes.show', $creditNote), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.credit-notes.credit-note-form', [
            'clients' => Client::orderBy('name')->get(['id', 'name', 'company_name']),
            'invoices' => Invoice::with('client')
                ->whereIn('status', ['sent', 'paid', 'overdue'])
                ->latest()
                ->get(['id', 'invoice_number', 'client_id', 'total', 'tax_rate']),
            'reasons' => CreditNote::REASONS,
        ]);
    }
}
