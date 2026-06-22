<?php

namespace App\Livewire\Admin\Templates;

use App\Models\InvoiceTemplate;
use App\Models\SavedItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Template')]
class TemplateForm extends Component
{
    public ?InvoiceTemplate $template = null;

    public string $name = '';
    public ?string $description = null;
    public string $type = 'both';
    public string $currencyCode = 'LKR';
    public ?float $tax_rate = 0;
    public ?float $discount_amount = 0;
    public ?string $notes = null;
    public ?string $terms = null;

    /** @var array<int, array{name:string, description:?string, quantity:float, unit_price:float}> */
    public array $items = [];

    public bool $showSavedItems = false;

    public function mount(?InvoiceTemplate $template = null): void
    {
        if ($template && $template->exists) {
            $this->template = $template->load('items');
            $this->name = $template->name;
            $this->description = $template->description;
            $this->type = $template->type;
            $this->currencyCode = $template->currency_code ?: 'LKR';
            $this->tax_rate = (float) $template->tax_rate;
            $this->discount_amount = (float) $template->discount_amount;
            $this->notes = $template->notes;
            $this->terms = $template->terms;
            $this->items = $template->items->map(fn ($i) => [
                'name' => $i->name,
                'description' => $i->description,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
            ])->toArray();
        } else {
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
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:' . implode(',', InvoiceTemplate::TYPES)],
            'currencyCode' => ['required', 'string', 'size:3'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.*.name.required' => 'Item name is required.',
        ]);

        DB::transaction(function () use ($validated) {
            $template = $this->template ?? new InvoiceTemplate();

            $template->fill([
                'name' => $validated['name'],
                'description' => $this->description,
                'type' => $validated['type'],
                'currency_code' => $this->currencyCode,
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'notes' => $this->notes,
                'terms' => $this->terms,
            ]);

            if (! $template->exists) {
                $template->created_by = auth()->user()?->name;
            }
            $template->save();

            $template->items()->delete();
            foreach (array_values($this->items) as $order => $item) {
                $template->items()->create([
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => round((float) $item['quantity'] * (float) $item['unit_price'], 2),
                    'sort_order' => $order,
                ]);
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Template saved.');

        return $this->redirect(route('admin.invoice-templates.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.templates.template-form', [
            'savedItems' => $this->showSavedItems ? SavedItem::orderBy('name')->get() : collect(),
        ]);
    }
}
