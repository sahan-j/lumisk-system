<?php

namespace App\Livewire\Admin\Invoices;

use App\Models\Invoice;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Invoices')]
class InvoicesIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updating($name): void
    {
        if (in_array($name, ['search', 'status', 'from', 'to'])) {
            $this->resetPage();
        }
    }

    public function duplicate(int $id): void
    {
        $original = Invoice::with('items')->findOrFail($id);

        DB::transaction(function () use ($original) {
            $copy = $original->replicate(['invoice_number']);
            $copy->invoice_number = DocumentNumberService::nextInvoiceNumber();
            $copy->status = 'draft';
            $copy->issue_date = now();
            $copy->due_date = now()->addDays(14);
            $copy->save();

            foreach ($original->items as $item) {
                $copy->items()->create($item->only(['name', 'description', 'quantity', 'unit_price', 'total', 'order']));
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Invoice duplicated.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            Invoice::findOrFail($this->deleteId)->delete();
            $this->dispatch('toast', type: 'success', message: 'Invoice deleted.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $invoices = Invoice::query()
            ->with('client', 'payments')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('invoice_number', 'like', "%{$this->search}%")
                        ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->from, fn ($q) => $q->whereDate('issue_date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('issue_date', '<=', $this->to))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.invoices.invoices-index', [
            'invoices' => $invoices,
            'statuses' => Invoice::STATUSES,
        ]);
    }
}
