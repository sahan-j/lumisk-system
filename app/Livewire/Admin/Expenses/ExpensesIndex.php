<?php

namespace App\Livewire\Admin\Expenses;

use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Expenses')]
class ExpensesIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $client = '';

    #[Url]
    public string $method = '';

    #[Url]
    public string $billable = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updating($name): void
    {
        if (in_array($name, ['search', 'category', 'client', 'method', 'billable', 'from', 'to'])) {
            $this->resetPage();
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            $expense = Expense::find($this->deleteId);
            if ($expense) {
                if ($expense->receipt) {
                    Storage::disk('public')->delete($expense->receipt);
                }
                $expense->delete();
                $this->dispatch('toast', type: 'success', message: 'Expense deleted.');
            }
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    /** Apply the active filters to a query builder. */
    protected function applyFilters($query)
    {
        return $query
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('title', 'like', "%{$this->search}%")
                        ->orWhere('reference_number', 'like', "%{$this->search}%");
                });
            })
            ->when($this->category !== '', fn ($q) => $q->where('category_id', $this->category))
            ->when($this->client !== '', fn ($q) => $q->where('client_id', $this->client))
            ->when($this->method !== '', fn ($q) => $q->where('payment_method', $this->method))
            ->when($this->billable === 'billable', fn ($q) => $q->where('is_billable', true))
            ->when($this->billable === 'non_billable', fn ($q) => $q->where('is_billable', false))
            ->when($this->from, fn ($q) => $q->whereDate('expense_date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('expense_date', '<=', $this->to));
    }

    public function exportCsv()
    {
        $records = $this->applyFilters(
            Expense::with(['category', 'client', 'project'])
        )->latest('expense_date')->get();

        $filename = 'expenses-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Title', 'Category', 'Amount', 'Payment Method', 'Client', 'Project', 'Billable', 'Reference', 'Notes']);
            foreach ($records as $r) {
                fputcsv($out, [
                    $r->expense_date?->format('Y-m-d'),
                    $r->title,
                    $r->category?->name ?? '',
                    number_format((float) $r->amount, 2, '.', ''),
                    $r->payment_method_label,
                    $r->client?->name ?? '',
                    $r->project?->name ?? '',
                    $r->is_billable ? ($r->is_billed ? 'Billed' : 'Billable') : 'No',
                    $r->reference_number ?? '',
                    $r->notes ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        $expenses = $this->applyFilters(
            Expense::with(['category', 'client', 'project'])
        )->latest('expense_date')->paginate(20);

        // Summary stats (unaffected by filters).
        $totalThisMonth = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)->sum('amount');
        $totalThisYear = Expense::whereYear('expense_date', now()->year)->sum('amount');
        $totalAll = Expense::sum('amount');
        $unbilledBillable = Expense::where('is_billable', true)->where('is_billed', false)->sum('amount');

        // Category breakdown (all-time).
        $categories = ExpenseCategory::withCount('expenses')
            ->withSum('expenses as expenses_sum_amount', 'amount')
            ->orderByDesc('expenses_sum_amount')
            ->get();
        $breakdownTotal = (float) $categories->sum('expenses_sum_amount');

        return view('livewire.admin.expenses.expenses-index', [
            'expenses' => $expenses,
            'totalThisMonth' => $totalThisMonth,
            'totalThisYear' => $totalThisYear,
            'totalAll' => $totalAll,
            'unbilledBillable' => $unbilledBillable,
            'categories' => $categories,
            'breakdownTotal' => $breakdownTotal,
            'filterCategories' => ExpenseCategory::orderBy('name')->get(),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'methods' => Expense::PAYMENT_METHODS,
        ]);
    }
}
