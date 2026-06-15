<?php

namespace App\Livewire\Admin\Reports;

use App\Livewire\Concerns\WithDateRange;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Profit & Loss')]
class ProfitLossReport extends Component
{
    use WithDateRange;

    /** @return \Illuminate\Support\Collection<int, array> */
    protected function monthlySeries()
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $revenue = (float) Payment::whereBetween('payment_date', [$start, $end])->sum('amount');
            $expenses = (float) Expense::whereBetween('expense_date', [$start, $end])->sum('amount');

            $months->push([
                'label' => $month->format('M Y'),
                'revenue' => round($revenue, 2),
                'expenses' => round($expenses, 2),
                'profit' => round($revenue - $expenses, 2),
            ]);
        }

        return $months;
    }

    public function export()
    {
        $months = $this->monthlySeries();

        return response()->streamDownload(function () use ($months) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Month', 'Revenue', 'Expenses', 'Profit', 'Margin %']);
            foreach ($months as $m) {
                $margin = $m['revenue'] > 0 ? round($m['profit'] / $m['revenue'] * 100, 1) : 0;
                fputcsv($handle, [
                    $m['label'],
                    number_format($m['revenue'], 2, '.', ''),
                    number_format($m['expenses'], 2, '.', ''),
                    number_format($m['profit'], 2, '.', ''),
                    $margin,
                ]);
            }
            fclose($handle);
        }, 'profit-loss-' . $this->rangeSuffix() . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        [$from, $to] = $this->dateRange();

        $months = $this->monthlySeries();

        $periodRevenue = (float) Payment::whereBetween('payment_date', [$from, $to])->sum('amount');
        $periodExpenses = (float) Expense::whereBetween('expense_date', [$from, $to])->sum('amount');
        $periodProfit = round($periodRevenue - $periodExpenses, 2);
        $profitMargin = $periodRevenue > 0 ? round($periodProfit / $periodRevenue * 100, 1) : 0;

        $expenseBreakdown = Expense::with('category')
            ->whereBetween('expense_date', [$from, $to])
            ->get()
            ->groupBy(fn ($e) => $e->category?->name ?? 'Uncategorized')
            ->map(fn ($group) => [
                'total' => (float) $group->sum('amount'),
                'color' => $group->first()->category?->color ?? '#94a3b8',
            ])
            ->sortByDesc('total');

        return view('livewire.admin.reports.profit-loss-report', [
            'from' => $from,
            'to' => $to,
            'months' => $months,
            'periodRevenue' => $periodRevenue,
            'periodExpenses' => $periodExpenses,
            'periodProfit' => $periodProfit,
            'profitMargin' => $profitMargin,
            'expenseBreakdown' => $expenseBreakdown,
            'chartLabels' => $months->pluck('label'),
            'revenueData' => $months->pluck('revenue'),
            'expenseData' => $months->pluck('expenses'),
            'profitData' => $months->pluck('profit'),
        ]);
    }
}
