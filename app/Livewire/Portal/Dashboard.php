<?php

namespace App\Livewire\Portal;

use App\Models\ActivityLog;
use App\Models\ClientDocument;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $client = Auth::guard('client')->user();

        // Outstanding balance = sum of unpaid invoice balances.
        $outstanding = $client->invoices()
            ->whereIn('status', ['sent', 'overdue'])
            ->with('payments')
            ->get()
            ->sum('outstanding_balance');

        $stats = [
            'total_invoiced'      => (float) $client->invoices()->sum('total'),
            'outstanding'         => $outstanding,
            'overdue_count'       => $client->invoices()->where('status', 'overdue')->count(),
            'active_projects'     => $client->projects()->where('status', 'active')->count(),
            'open_tickets'        => $client->tickets()->whereIn('status', ['open', 'in_progress', 'waiting_client'])->count(),
            'pending_estimates'   => $client->estimates()->where('status', 'sent')->count(),
            'active_subscriptions' => $client->subscriptions()->where('status', 'active')->count(),
            'unread_documents'    => ClientDocument::where('client_id', $client->id)
                ->where('uploaded_by', 'admin')
                ->where('viewed_by_client', false)
                ->count(),
        ];

        $recentInvoices   = $client->invoices()->latest()->take(5)->get();
        $pendingEstimates = $client->estimates()->where('status', 'sent')->latest()->take(3)->get();

        // Load tasks so completion_percentage accessor doesn't trigger N+1.
        $activeProjects = $client->projects()
            ->where('status', 'active')
            ->with('tasks')
            ->latest()
            ->take(3)
            ->get();

        $openTickets = $client->tickets()
            ->whereIn('status', ['open', 'in_progress', 'waiting_client'])
            ->latest()
            ->take(4)
            ->get();

        $upcomingRenewals = $client->subscriptions()
            ->where('status', 'active')
            ->whereBetween('next_billing_date', [today(), today()->addDays(30)])
            ->orderBy('next_billing_date')
            ->take(3)
            ->get();

        $activities = ActivityLog::where('client_id', $client->id)->latest()->take(8)->get();

        return view('livewire.portal.dashboard', compact(
            'client', 'stats', 'recentInvoices', 'pendingEstimates',
            'activeProjects', 'openTickets', 'upcomingRenewals', 'activities',
        ));
    }
}
