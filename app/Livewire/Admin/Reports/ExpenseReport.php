<?php

namespace App\Livewire\Admin\Reports;

use App\Livewire\Concerns\WithDateRange;
use App\Models\Expense;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Expense Report')]
class ExpenseReport extends Component
{
    use WithDateRange, WithPagination;

    public function export()
    {
        [$from, $to] = $this->dateRange();

        return response()->streamDownload(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Title', 'Category', 'Amount', 'Payment Method', 'Client', 'Project', 'Billable', 'Reference']);

            Expense::with(['category', 'client', 'project'])
                ->whereBetween('expense_date', [$from, $to])
                ->orderBy('expense_date')
                ->chunk(100, function ($expenses) use ($handle) {
                    foreach ($expenses as $e) {
                        fputcsv($handle, [
                            $e->expense_date?->format('Y-m-d'),
                            $e->title,
                            $e->category?->name ?? '',
                            number_format((float) $e->amount, 2, '.', ''),
                            $e->payment_method_label,
                            $e->client?->name ?? '',
                            $e->project?->name ?? '',
                            $e->is_billable ? 'Yes' : 'No',
                            $e->reference_number ?? '',
                        ]);
                    }
                });

            fclose($handle);
        }, 'expense-report-' . $this->rangeSuffix() . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        [$from, $to] = $this->dateRange();

        $rangeExpenses = Expense::with('category')->whereBetween('expense_date', [$from, $to])->get();
        $totalExpenses = (float) $rangeExpenses->sum('amount');
        $count = $rangeExpenses->count();

        $byCategory = $rangeExpenses
            ->groupBy(fn ($e) => $e->category?->name ?? 'Uncategorized')
            ->map(fn ($group) => [
                'total' => (float) $group->sum('amount'),
                'count' => $group->count(),
                'color' => $group->first()->category?->color ?? '#94a3b8',
            ])
            ->sortByDesc('total');

        $byPaymentMethod = Expense::whereBetween('expense_date', [$from, $to])
            ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get();

        // Rolling last-12-months expense trend.
        $monthly = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $monthly->push([
                'label' => $month->format('M'),
                'value' => round((float) Expense::whereYear('expense_date', $month->year)
                    ->whereMonth('expense_date', $month->month)->sum('amount'), 2),
            ]);
        }

        $unbilledBillable = Expense::where('is_billable', true)->where('is_billed', false)->sum('amount');

        $expenses = Expense::with(['category', 'client', 'project'])
            ->whereBetween('expense_date', [$from, $to])
            ->orderByDesc('expense_date')
            ->paginate(20);

        return view('livewire.admin.reports.expense-report', [
            'from' => $from,
            'to' => $to,
            'totalExpenses' => $totalExpenses,
            'count' => $count,
            'avgPerExpense' => $count > 0 ? $totalExpenses / $count : 0,
            'unbilledBillable' => $unbilledBillable,
            'byCategory' => $byCategory,
            'byPaymentMethod' => $byPaymentMethod,
            'chartLabels' => $monthly->pluck('label'),
            'chartValues' => $monthly->pluck('value'),
            'expenses' => $expenses,
        ]);
    }
}
