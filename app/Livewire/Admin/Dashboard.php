<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public string $activityFilter = 'all';
    public int $activityLimit = 15;

    /** Re-render hook for the Refresh button / wire:poll. */
    public function loadActivities(): void
    {
        // Intentionally empty — invoking any action re-renders and re-queries.
    }

    public function filterActivity(string $filter): void
    {
        $this->activityFilter = $filter;
        $this->activityLimit = 15;
    }

    public function loadMore(): void
    {
        $this->activityLimit += 15;
    }

    public function render()
    {
        // Revenue = everything actually collected across all payments.
        $totalRevenue = Payment::sum('amount');

        // Outstanding = unpaid remainder of open invoices.
        $outstanding = Invoice::whereIn('status', ['sent', 'overdue'])
            ->with('payments')
            ->get()
            ->sum('outstanding_balance');

        $totalClients = Client::count();
        $pendingEstimates = Estimate::whereIn('status', ['draft', 'sent'])->count();

        // Last 6 months revenue (paid invoices by issue month).
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereYear('issue_date', $month->year)
                ->whereMonth('issue_date', $month->month)
                ->sum('total');
            $months->push([
                'label' => $month->format('M'),
                'value' => round((float) $revenue, 2),
            ]);
        }

        // Last 6 months expenses (by expense_date) — aligned with the revenue series above.
        $expenseSeries = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $expenseSeries->push(round((float) Expense::whereYear('expense_date', $month->year)
                ->whereMonth('expense_date', $month->month)
                ->sum('amount'), 2));
        }

        // Year-to-date profit & loss (revenue collected vs expenses incurred this year).
        $revenueThisYear = (float) Payment::whereYear('payment_date', now()->year)->sum('amount');
        $expensesThisYear = (float) Expense::whereYear('expense_date', now()->year)->sum('amount');
        $netProfit = round($revenueThisYear - $expensesThisYear, 2);

        // Activity feed — filtered by type group, limited (load-more grows the limit).
        $activityQuery = ActivityLog::latest();
        if (isset(ActivityLog::GROUPS[$this->activityFilter])) {
            $activityQuery->whereIn('type', ActivityLog::GROUPS[$this->activityFilter]);
        }
        $activities = $activityQuery->limit($this->activityLimit + 1)->get();
        $hasMoreActivity = $activities->count() > $this->activityLimit;
        $activities = $activities->take($this->activityLimit);

        $overdueInvoices = Invoice::where('status', 'overdue')->get();

        $activeProjects = Project::where('status', 'active')->count();
        $overdueProjects = Project::whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->count();
        $recentProjects = Project::with('client')
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done')])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->take(3)
            ->get();

        return view('livewire.admin.dashboard', [
            'totalRevenue' => $totalRevenue,
            'outstanding' => $outstanding,
            'totalClients' => $totalClients,
            'pendingEstimates' => $pendingEstimates,
            'overdueCount' => $overdueInvoices->count(),
            'overdueTotal' => $overdueInvoices->sum('total'),
            'activeProjects' => $activeProjects,
            'overdueProjects' => $overdueProjects,
            'recentProjects' => $recentProjects,
            'openTickets' => Ticket::where('status', 'open')->count(),
            'recentTickets' => Ticket::with('client')->whereNotIn('status', ['closed'])->latest()->take(3)->get(),
            'expensesThisYear' => $expensesThisYear,
            'revenueThisYear' => $revenueThisYear,
            'netProfit' => $netProfit,
            'activities' => $activities,
            'hasMoreActivity' => $hasMoreActivity,
            'chartLabels' => $months->pluck('label'),
            'chartValues' => $months->pluck('value'),
            'expenseValues' => $expenseSeries,
            'recentInvoices' => Invoice::with('client')->latest()->take(5)->get(),
            'recentEstimates' => Estimate::with('client')->latest()->take(5)->get(),
        ]);
    }
}
