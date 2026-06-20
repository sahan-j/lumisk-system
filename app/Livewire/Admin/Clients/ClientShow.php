<?php

namespace App\Livewire\Admin\Clients;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Client')]
class ClientShow extends Component
{
    public Client $client;

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function createInvoiceFromExpenses()
    {
        $expenses = $this->client->expenses()
            ->where('is_billable', true)
            ->where('is_billed', false)
            ->orderBy('expense_date')
            ->get();

        if ($expenses->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No unbilled billable expenses for this client.');

            return;
        }

        $invoice = DB::transaction(function () use ($expenses) {
            $invoice = new Invoice();
            $invoice->invoice_number = DocumentNumberService::nextInvoiceNumber();
            $invoice->client_id = $this->client->id;
            $invoice->status = 'draft';
            $invoice->issue_date = now();
            $invoice->due_date = now()->addDays(14);
            $invoice->tax_rate = 0;
            $invoice->discount_amount = 0;
            $invoice->save();

            foreach ($expenses->values() as $order => $expense) {
                $invoice->items()->create([
                    'name' => $expense->title,
                    'description' => $expense->description,
                    'quantity' => 1,
                    'unit_price' => (float) $expense->amount,
                    'total' => (float) $expense->amount,
                    'order' => $order,
                ]);
            }

            $invoice->load('items');
            $invoice->recalculateTotals();
            $invoice->save();

            Expense::whereIn('id', $expenses->pluck('id'))->update(['is_billed' => true]);

            return $invoice;
        });

        $this->dispatch('toast', type: 'success', message: 'Invoice created from billable expenses.');

        return $this->redirect(route('admin.invoices.show', $invoice), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.clients.client-show', [
            'invoices' => $this->client->invoices()->latest()->get(),
            'estimates' => $this->client->estimates()->latest()->get(),
            'projects' => $this->client->projects()
                ->withCount(['tasks', 'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done')])
                ->latest()->get(),
            'billableExpenses' => $this->client->expenses()
                ->where('is_billable', true)->where('is_billed', false)
                ->with('category')->latest('expense_date')->get(),
            'totalPaid' => $this->client->invoices()->where('status', 'paid')->sum('total'),
            'totalOutstanding' => $this->client->invoices()->whereIn('status', ['sent', 'overdue'])->sum('total'),
            'subscriptions' => $this->client->subscriptions()->with('plan')->latest()->get(),
        ]);
    }
}
