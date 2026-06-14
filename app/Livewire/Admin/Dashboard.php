<?php

namespace App\Livewire\Admin;

use App\Models\Client;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
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
            'chartLabels' => $months->pluck('label'),
            'chartValues' => $months->pluck('value'),
            'recentInvoices' => Invoice::with('client')->latest()->take(5)->get(),
            'recentEstimates' => Estimate::with('client')->latest()->take(5)->get(),
        ]);
    }
}
