<?php

namespace App\Livewire\Admin\Estimates;

use App\Models\Client;
use App\Models\Estimate;
use App\Models\SavedItem;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Estimate')]
class EstimateForm extends Component
{
    public ?Estimate $estimate = null;

    public ?int $client_id = null;
    public string $status = 'draft';
    public string $issue_date = '';
    public ?string $expiry_date = null;
    public ?float $tax_rate = 0;
    public ?float $discount_amount = 0;
    public ?string $notes = null;
    public ?string $terms = null;

    /** @var array<int, array{name:string, description:?string, quantity:float, unit_price:float}> */
    public array $items = [];

    public bool $showSavedItems = false;

    public function mount(?Estimate $estimate = null): void
    {
        $company = company_settings();

        if ($estimate && $estimate->exists) {
            $this->estimate = $estimate->load('items');
            $this->client_id = $estimate->client_id;
            $this->status = $estimate->status;
            $this->issue_date = $estimate->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d');
            $this->expiry_date = $estimate->expiry_date?->format('Y-m-d');
            $this->tax_rate = (float) $estimate->tax_rate;
            $this->discount_amount = (float) $estimate->discount_amount;
            $this->notes = $estimate->notes;
            $this->terms = $estimate->terms;
            $this->items = $estimate->items->map(fn ($i) => [
                'name' => $i->name,
                'description' => $i->description,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
            ])->toArray();
        } else {
            $this->issue_date = now()->format('Y-m-d');
            $this->expiry_date = now()->addDays((int) $company->estimate_expiry_days)->format('Y-m-d');
            $this->tax_rate = (float) $company->default_tax_rate;
            $this->notes = $company->default_notes;
            $this->terms = $company->default_terms;
            $this->client_id = (int) request('client') ?: null;
            $this->items = [$this->blankItem()];
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

    public function addFromSaved(int $savedItemId): void
    {
        $item = SavedItem::find($savedItemId);
        if (! $item) {
            return;
        }

        $new = [
            'name' => $item->name,
            'description' => $item->description,
            'quantity' => 1,
            'unit_price' => (float) $item->unit_price,
        ];

        if (count($this->items) === 1 && $this->items[0]['name'] === '') {
            $this->items[0] = $new;
        } else {
            $this->items[] = $new;
        }
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
        return round(max($this->subtotal + $this->taxAmount - ($this->discount_amount ?? 0), 0), 2);
    }

    public function save()
    {
        $isNew = ! ($this->estimate && $this->estimate->exists);

        $validated = $this->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'status' => ['required', 'in:' . implode(',', Estimate::STATUSES)],
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.*.name.required' => 'Item name is required.',
            'client_id.required' => 'Please select a client.',
        ]);

        $estimate = DB::transaction(function () use ($validated) {
            $estimate = $this->estimate ?? new Estimate();

            if (! $estimate->exists) {
                $estimate->estimate_number = DocumentNumberService::nextEstimateNumber();
            }

            $estimate->fill([
                'client_id' => $validated['client_id'],
                'status' => $validated['status'],
                'issue_date' => $validated['issue_date'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'notes' => $this->notes,
                'terms' => $this->terms,
            ]);
            $estimate->save();

            $estimate->items()->delete();
            foreach (array_values($this->items) as $order => $item) {
                $estimate->items()->create([
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => round((float) $item['quantity'] * (float) $item['unit_price'], 2),
                    'order' => $order,
                ]);
            }

            $estimate->load('items');
            $estimate->recalculateTotals();
            $estimate->save();

            return $estimate;
        });

        if ($isNew) {
            $estimate->loadMissing('client');
            \App\Models\ActivityLog::log('estimate_created',
                "Estimate {$estimate->estimate_number} created for " . ($estimate->client?->name ?? 'a client'),
                ['subject_type' => 'Estimate', 'subject_id' => $estimate->id,
                 'subject_label' => $estimate->estimate_number, 'client_id' => $estimate->client_id]);
        }

        $this->dispatch('toast', type: 'success', message: 'Estimate saved.');

        return $this->redirect(route('admin.estimates.show', $estimate), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.estimates.estimate-form', [
            'clients' => Client::orderBy('name')->get(['id', 'name', 'company_name']),
            'savedItems' => $this->showSavedItems ? SavedItem::orderBy('name')->get() : collect(),
        ]);
    }
}
