<?php

namespace App\Livewire\Admin;

use App\Models\Estimate;
use App\Models\Invoice;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class ConvertModal extends Component
{
    public bool $show = false;
    public string $direction = '';        // 'estimate_to_invoice' | 'invoice_to_estimate'
    public ?int $sourceId = null;
    public ?string $sourceNumber = null;
    public string $newIssueDate = '';
    public string $newDueDate = '';
    public array $selectedItems = [];      // string ids of items to include
    public bool $partialConvert = false;
    public bool $markAccepted = true;      // for estimate_to_invoice only
    public array $items = [];              // [['id','name','total'], ...]

    #[On('open-convert')]
    public function handleOpen(string $direction, int $id): void
    {
        $this->resetValidation();
        $this->reset('partialConvert');
        $this->direction = $direction;
        $this->sourceId = $id;

        $source = $this->loadSource();
        $this->sourceNumber = $this->sourceType() === 'invoice'
            ? $source->invoice_number
            : $source->estimate_number;

        $this->items = $source->items->map(fn ($i) => [
            'id'    => $i->id,
            'name'  => $i->name,
            'total' => (float) $i->total,
        ])->values()->toArray();

        $this->selectedItems = $source->items->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        $this->newIssueDate = now()->format('Y-m-d');
        $this->newDueDate = $this->defaultDueDate();
        $this->markAccepted = true;

        $this->show = true;
    }

    public function toggleItem($itemId): void
    {
        $itemId = (string) $itemId;

        if (in_array($itemId, $this->selectedItems, true)) {
            $this->selectedItems = array_values(array_diff($this->selectedItems, [$itemId]));
        } else {
            $this->selectedItems[] = $itemId;
        }
    }

    public function getSelectedTotalProperty(): float
    {
        $ids = array_map('intval', $this->selectedItems);

        return round(collect($this->items)
            ->whereIn('id', $ids)
            ->sum('total'), 2);
    }

    public function convert()
    {
        $this->validate([
            'newIssueDate'  => ['required', 'date'],
            'newDueDate'    => ['nullable', 'date'],
            'selectedItems' => ['required', 'array', 'min:1'],
        ], [], ['selectedItems' => 'items']);

        $source = $this->loadSource();
        $target = $this->targetType();

        $itemIds = $this->partialConvert
            ? array_map('intval', $this->selectedItems)
            : $source->items->pluck('id')->all();

        $itemsToCopy = $source->items->whereIn('id', $itemIds);

        if ($itemsToCopy->isEmpty()) {
            $this->addError('selectedItems', 'Select at least one item to convert.');
            return;
        }

        $new = DB::transaction(function () use ($source, $target, $itemsToCopy) {
            if ($target === 'invoice') {
                $doc = new Invoice();
                $doc->invoice_number = DocumentNumberService::nextInvoiceNumber();
                $doc->due_date = $this->newDueDate ?: null;
            } else {
                $doc = new Estimate();
                $doc->estimate_number = DocumentNumberService::nextEstimateNumber();
                $doc->expiry_date = $this->newDueDate ?: null;
            }

            $doc->client_id = $source->client_id;
            $doc->status = 'draft';
            $doc->issue_date = $this->newIssueDate;
            $doc->tax_rate = $source->tax_rate;
            // A partial convert can't safely carry the full document discount.
            $doc->discount_amount = $this->partialConvert ? 0 : $source->discount_amount;
            $doc->notes = $source->notes;
            $doc->terms = $source->terms;
            $doc->converted_from = $this->sourceNumber;
            $doc->save();

            foreach ($itemsToCopy as $item) {
                $doc->items()->create($item->only(['name', 'description', 'quantity', 'unit_price', 'total', 'order']));
            }

            $doc->load('items');
            $doc->recalculateTotals();
            $doc->save();

            if ($this->direction === 'estimate_to_invoice' && $this->markAccepted) {
                $source->update(['status' => 'accepted']);
            }

            return $doc;
        });

        $number = $target === 'invoice' ? $new->invoice_number : $new->estimate_number;
        $route = $target === 'invoice' ? 'admin.invoices.edit' : 'admin.estimates.edit';

        if ($this->direction === 'estimate_to_invoice') {
            \App\Models\ActivityLog::log('estimate_converted',
                "Estimate {$this->sourceNumber} converted to invoice {$number}",
                ['subject_type' => 'Invoice', 'subject_id' => $new->id,
                 'subject_label' => $number, 'client_id' => $new->client_id]);
        }

        $this->show = false;
        $this->dispatch('toast', type: 'success', message: "Converted to {$number}");

        return $this->redirect(route($route, $new), navigate: true);
    }

    public function sourceType(): string
    {
        return $this->direction === 'estimate_to_invoice' ? 'estimate' : 'invoice';
    }

    public function targetType(): string
    {
        return $this->direction === 'estimate_to_invoice' ? 'invoice' : 'estimate';
    }

    public function title(): string
    {
        return $this->direction === 'estimate_to_invoice'
            ? 'Convert Estimate to Invoice'
            : 'Convert Invoice to Estimate';
    }

    protected function loadSource()
    {
        return $this->sourceType() === 'estimate'
            ? Estimate::with('items')->findOrFail($this->sourceId)
            : Invoice::with('items')->findOrFail($this->sourceId);
    }

    protected function defaultDueDate(): string
    {
        $days = $this->targetType() === 'invoice'
            ? 14
            : (int) company_settings()->estimate_expiry_days;

        return now()->addDays($days)->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.admin.convert-modal');
    }
}
