<?php

namespace App\Livewire\Admin;

use App\Models\Client;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class DuplicateModal extends Component
{
    public bool $show = false;
    public string $type = '';          // source type: 'invoice' | 'estimate'
    public ?int $sourceId = null;
    public ?string $sourceNumber = null;
    public string $newIssueDate = '';
    public string $newDueDate = '';
    public ?int $newClientId = null;
    public string $duplicateAs = '';   // target type: 'invoice' | 'estimate'
    public array $clients = [];

    #[On('open-duplicate')]
    public function handleOpen(string $type, int $id): void
    {
        $this->resetValidation();
        $this->type = $type;
        $this->sourceId = $id;

        $source = $this->loadSource();
        $this->sourceNumber = $type === 'invoice' ? $source->invoice_number : $source->estimate_number;
        $this->newClientId = $source->client_id;
        $this->duplicateAs = $type;
        $this->newIssueDate = now()->format('Y-m-d');
        $this->newDueDate = $this->defaultDueDate($type);
        $this->clients = Client::orderBy('name')->get(['id', 'name'])->toArray();

        $this->show = true;
    }

    public function updatedDuplicateAs(string $value): void
    {
        $this->newDueDate = $this->defaultDueDate($value);
    }

    public function duplicate()
    {
        $this->validate([
            'newIssueDate' => ['required', 'date'],
            'newDueDate'   => ['nullable', 'date'],
            'newClientId'  => ['required', 'exists:clients,id'],
            'duplicateAs'  => ['required', 'in:invoice,estimate'],
        ]);

        $source = $this->loadSource();

        $new = DB::transaction(function () use ($source) {
            if ($this->duplicateAs === 'invoice') {
                $doc = new Invoice();
                $doc->invoice_number = DocumentNumberService::nextInvoiceNumber();
                $doc->due_date = $this->newDueDate ?: null;
            } else {
                $doc = new Estimate();
                $doc->estimate_number = DocumentNumberService::nextEstimateNumber();
                $doc->expiry_date = $this->newDueDate ?: null;
            }

            $doc->client_id = $this->newClientId;
            $doc->status = 'draft';
            $doc->issue_date = $this->newIssueDate;
            $doc->tax_rate = $source->tax_rate;
            $doc->discount_amount = $source->discount_amount;
            $doc->notes = $source->notes;
            $doc->terms = $source->terms;
            $doc->save();

            foreach ($source->items as $item) {
                $doc->items()->create($item->only(['name', 'description', 'quantity', 'unit_price', 'total', 'order']));
            }

            $doc->load('items');
            $doc->recalculateTotals();
            $doc->save();

            return $doc;
        });

        $number = $this->duplicateAs === 'invoice' ? $new->invoice_number : $new->estimate_number;
        $route = $this->duplicateAs === 'invoice' ? 'admin.invoices.edit' : 'admin.estimates.edit';

        $this->show = false;
        $this->dispatch('toast', type: 'success', message: "Duplicated as {$number}");

        return $this->redirect(route($route, $new), navigate: true);
    }

    protected function loadSource()
    {
        return $this->type === 'invoice'
            ? Invoice::with('items')->findOrFail($this->sourceId)
            : Estimate::with('items')->findOrFail($this->sourceId);
    }

    protected function defaultDueDate(string $targetType): string
    {
        $days = $targetType === 'invoice' ? 14 : (int) company_settings()->estimate_expiry_days;

        return now()->addDays($days)->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.admin.duplicate-modal');
    }
}
