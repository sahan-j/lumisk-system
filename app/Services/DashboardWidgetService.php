<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\PipelineStage;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\TimeEntry;
use Illuminate\Support\Str;

/**
 * Definitions + data resolution for the customizable admin dashboard widgets.
 * Icons are inline-SVG path strings (this project has no icon font).
 */
class DashboardWidgetService
{
    public static function getAvailableWidgets(): array
    {
        return [
            'revenue_stat' => ['name' => 'Total Revenue', 'description' => 'Total payments received', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1', 'size' => 'small', 'category' => 'Finance'],
            'outstanding_stat' => ['name' => 'Outstanding', 'description' => 'Unpaid invoice total', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'size' => 'small', 'category' => 'Finance'],
            'net_profit_stat' => ['name' => 'Net Profit (YTD)', 'description' => 'Revenue minus expenses', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'size' => 'small', 'category' => 'Finance'],
            'mrr_stat' => ['name' => 'MRR', 'description' => 'Monthly recurring revenue', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'size' => 'small', 'category' => 'Finance'],
            'clients_stat' => ['name' => 'Total Clients', 'description' => 'Active client count', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z', 'size' => 'small', 'category' => 'CRM'],
            'pipeline_stat' => ['name' => 'Pipeline Value', 'description' => 'Total active leads value', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'size' => 'small', 'category' => 'CRM'],
            'open_tickets_stat' => ['name' => 'Open Tickets', 'description' => 'Unresolved support tickets', 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z', 'size' => 'small', 'category' => 'Support'],
            'active_projects_stat' => ['name' => 'Active Projects', 'description' => 'Projects in progress', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'size' => 'small', 'category' => 'Operations'],
            'time_today' => ['name' => 'Time Tracked Today', 'description' => "Today's logged time", 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'size' => 'small', 'category' => 'Operations'],
            'revenue_chart' => ['name' => 'Revenue vs Expenses Chart', 'description' => 'Last 6 months bar chart', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'size' => 'large', 'category' => 'Finance'],
            'profit_loss_chart' => ['name' => 'Profit & Loss Chart', 'description' => 'Monthly P&L line chart', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'size' => 'large', 'category' => 'Finance'],
            'invoice_status_chart' => ['name' => 'Invoice Status Breakdown', 'description' => 'Donut chart by status', 'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z', 'size' => 'medium', 'category' => 'Finance'],
            'recent_invoices' => ['name' => 'Recent Invoices', 'description' => 'Last 5 invoices', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'size' => 'medium', 'category' => 'Finance'],
            'recent_estimates' => ['name' => 'Recent Estimates', 'description' => 'Last 5 estimates', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'size' => 'medium', 'category' => 'Finance'],
            'overdue_invoices' => ['name' => 'Overdue Invoices', 'description' => 'Invoices past due date', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'size' => 'medium', 'category' => 'Finance'],
            'upcoming_renewals' => ['name' => 'Upcoming Renewals', 'description' => 'Subscriptions due soon', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'size' => 'medium', 'category' => 'Finance'],
            'pipeline_funnel' => ['name' => 'Pipeline Funnel', 'description' => 'Leads per stage', 'icon' => 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z', 'size' => 'medium', 'category' => 'CRM'],
            'top_clients' => ['name' => 'Top Clients', 'description' => 'Clients by revenue', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.98 9.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'size' => 'medium', 'category' => 'CRM'],
            'recent_tickets' => ['name' => 'Recent Tickets', 'description' => 'Latest support tickets', 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z', 'size' => 'medium', 'category' => 'Support'],
            'project_progress' => ['name' => 'Project Progress', 'description' => 'Active projects completion', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'size' => 'medium', 'category' => 'Operations'],
            'expense_breakdown' => ['name' => 'Expense Breakdown', 'description' => 'Expenses by category', 'icon' => 'M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 011-1z', 'size' => 'medium', 'category' => 'Finance'],
            'activity_feed' => ['name' => 'Activity Feed', 'description' => 'Recent system activity', 'icon' => 'M3 12h4l3 8 4-16 3 8h4', 'size' => 'large', 'category' => 'General'],
            'quick_actions' => ['name' => 'Quick Actions', 'description' => 'Common action shortcuts', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'size' => 'medium', 'category' => 'General'],
        ];
    }

    public static function getDefaultLayout(): array
    {
        $ids = [
            ['revenue_stat', 'small'], ['outstanding_stat', 'small'], ['clients_stat', 'small'], ['net_profit_stat', 'small'],
            ['active_projects_stat', 'small'], ['open_tickets_stat', 'small'], ['mrr_stat', 'small'], ['pipeline_stat', 'small'],
            ['revenue_chart', 'large'], ['recent_invoices', 'medium'], ['recent_estimates', 'medium'],
            ['upcoming_renewals', 'medium'], ['overdue_invoices', 'medium'], ['activity_feed', 'large'],
        ];

        $layout = [];
        foreach ($ids as $position => [$id, $size]) {
            $layout[] = ['id' => $id, 'visible' => true, 'position' => $position, 'size' => $size];
        }

        return $layout;
    }

    public static function getWidgetData(string $widgetId): array
    {
        return match ($widgetId) {
            'revenue_stat' => self::stat(
                (float) Payment::sum('amount'),
                self::money(Payment::sum('amount')),
                self::monthlyRevenueChange(),
                'up'
            ),

            'outstanding_stat' => (function () {
                $v = Invoice::whereIn('status', ['sent', 'overdue'])->with('payments')->get()->sum('outstanding_balance');

                return self::stat($v, self::money($v), null, 'neutral');
            })(),

            'net_profit_stat' => (function () {
                $rev = (float) Payment::whereYear('payment_date', now()->year)->sum('amount');
                $exp = (float) Expense::whereYear('expense_date', now()->year)->sum('amount');

                return self::stat($rev - $exp, self::money($rev - $exp), null, $rev - $exp >= 0 ? 'up' : 'down');
            })(),

            'mrr_stat' => (function () {
                $mrr = Subscription::where('status', 'active')->get()->sum('monthly_value');

                return self::stat($mrr, self::money($mrr), null, 'up');
            })(),

            'clients_stat' => self::stat(
                Client::count(),
                number_format(Client::count()),
                Client::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() . ' this month',
                'up'
            ),

            'pipeline_stat' => (function () {
                $q = Lead::whereHas('stage', fn ($s) => $s->where('is_lost', false)->where('is_won', false));

                return self::stat($q->sum('value'), self::money($q->sum('value')), $q->count() . ' active leads', 'neutral');
            })(),

            'open_tickets_stat' => self::stat(
                Ticket::where('status', 'open')->count(),
                number_format(Ticket::where('status', 'open')->count()),
                Ticket::where('status', 'in_progress')->count() . ' in progress',
                'down'
            ),

            'active_projects_stat' => self::stat(
                Project::where('status', 'active')->count(),
                number_format(Project::where('status', 'active')->count()),
                Project::where('status', 'completed')->whereMonth('completed_at', now()->month)->count() . ' done this month',
                'up'
            ),

            'time_today' => (function () {
                $mins = (int) TimeEntry::where('user_id', auth()->id())
                    ->whereDate('date', today())->whereNotNull('duration_minutes')->sum('duration_minutes');

                return self::stat($mins, sprintf('%dh %02dm', intdiv($mins, 60), $mins % 60), 'logged today', 'neutral');
            })(),

            'recent_invoices' => ['items' => Invoice::with('client')->latest()->take(5)->get()->map(fn ($inv) => [
                'number' => $inv->invoice_number,
                'client' => $inv->client?->name ?? '—',
                'amount' => self::money($inv->total),
                'status' => $inv->status,
                'date' => $inv->issue_date?->format('M d'),
                'url' => route('admin.invoices.show', $inv),
            ])],

            'recent_estimates' => ['items' => Estimate::with('client')->latest()->take(5)->get()->map(fn ($est) => [
                'number' => $est->estimate_number,
                'client' => $est->client?->name ?? '—',
                'amount' => self::money($est->total),
                'status' => $est->status,
                'date' => $est->issue_date?->format('M d'),
                'url' => route('admin.estimates.show', $est),
            ])],

            'overdue_invoices' => [
                'items' => Invoice::with('client', 'payments')->where('status', 'overdue')->orderBy('due_date')->take(5)->get()->map(fn ($inv) => [
                    'number' => $inv->invoice_number,
                    'client' => $inv->client?->name ?? '—',
                    'amount' => self::money($inv->outstanding_balance),
                    'days_overdue' => $inv->due_date ? (int) today()->diffInDays($inv->due_date, false) * -1 : 0,
                    'url' => route('admin.invoices.show', $inv),
                ]),
                'total' => Invoice::where('status', 'overdue')->count(),
            ],

            'upcoming_renewals' => ['items' => Subscription::with('client')->where('status', 'active')
                ->whereBetween('next_billing_date', [today(), today()->addDays(14)])
                ->orderBy('next_billing_date')->take(5)->get()->map(fn ($sub) => [
                    'name' => $sub->name,
                    'client' => $sub->client?->name ?? '—',
                    'amount' => self::money($sub->amount),
                    'date' => $sub->next_billing_date?->format('M d'),
                    'days_until' => $sub->next_billing_date ? (int) today()->diffInDays($sub->next_billing_date, false) : 0,
                    'url' => route('admin.subscriptions.show', $sub),
                ])],

            'pipeline_funnel' => ['stages' => PipelineStage::withCount('leads')->orderBy('sort_order')->get()->map(fn ($s) => [
                'name' => $s->name,
                'count' => $s->leads_count,
                'color' => $s->color ?: '#6d5cff',
            ])],

            'top_clients' => ['items' => Client::withSum('invoices as total_invoiced', 'total')
                ->orderByDesc('total_invoiced')->take(5)->get()->map(fn ($c) => [
                    'name' => $c->name,
                    'total' => self::money($c->total_invoiced ?? 0),
                    'url' => route('admin.clients.show', $c),
                ])],

            'recent_tickets' => ['items' => Ticket::with('client')->whereIn('status', ['open', 'in_progress'])
                ->latest()->take(5)->get()->map(fn ($t) => [
                    'number' => $t->ticket_number,
                    'subject' => Str::limit($t->subject, 35),
                    'client' => $t->client?->name ?? '—',
                    'status' => $t->statusLabel(),
                    'status_color' => self::namedHex($t->statusColor()),
                    'priority' => ucfirst($t->priority),
                    'priority_color' => self::namedHex($t->priorityColor()),
                    'url' => route('admin.tickets.show', $t),
                ])],

            'project_progress' => ['items' => Project::with('tasks')->where('status', 'active')->take(4)->get()->map(fn ($p) => [
                'name' => $p->name,
                'completion' => $p->completion_percentage,
                'tasks_done' => $p->tasks->where('status', 'done')->count(),
                'tasks_total' => $p->tasks->count(),
                'url' => route('admin.projects.show', $p),
            ])],

            'expense_breakdown' => [
                'items' => Expense::with('category')->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->get()
                    ->groupBy(fn ($e) => $e->category?->name ?? 'Other')
                    ->map(fn ($group, $name) => [
                        'name' => $name,
                        'total' => (float) $group->sum('amount'),
                        'color' => $group->first()->category?->color ?? '#94a3b8',
                    ])->sortByDesc('total')->take(5)->values(),
                'total' => (float) Expense::whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount'),
            ],

            'revenue_chart' => [
                'labels' => self::monthSeries(fn ($m) => $m->format('M')),
                'revenue' => self::monthSeries(fn ($m) => (float) Payment::whereYear('payment_date', $m->year)->whereMonth('payment_date', $m->month)->sum('amount')),
                'expenses' => self::monthSeries(fn ($m) => (float) Expense::whereYear('expense_date', $m->year)->whereMonth('expense_date', $m->month)->sum('amount')),
            ],

            'invoice_status_chart' => [
                'labels' => ['Draft', 'Sent', 'Paid', 'Overdue'],
                'data' => [
                    Invoice::where('status', 'draft')->count(),
                    Invoice::where('status', 'sent')->count(),
                    Invoice::where('status', 'paid')->count(),
                    Invoice::where('status', 'overdue')->count(),
                ],
                'colors' => ['#94a3b8', '#6d5cff', '#10b981', '#ef4444'],
            ],

            'profit_loss_chart' => [
                'labels' => self::monthSeries(fn ($m) => $m->format('M Y')),
                'profit' => self::monthSeries(fn ($m) => round(
                    (float) Payment::whereYear('payment_date', $m->year)->whereMonth('payment_date', $m->month)->sum('amount')
                    - (float) Expense::whereYear('expense_date', $m->year)->whereMonth('expense_date', $m->month)->sum('amount'), 2)),
            ],

            'quick_actions' => ['actions' => [
                ['label' => 'New Invoice', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'url' => route('admin.invoices.create'), 'color' => 'gradient'],
                ['label' => 'New Estimate', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'url' => route('admin.estimates.create'), 'color' => 'gradient'],
                ['label' => 'New Client', 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'url' => route('admin.clients.index'), 'color' => 'gradient'],
                ['label' => 'Add Expense', 'icon' => 'M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 011-1z', 'url' => route('admin.expenses.create'), 'color' => 'outline'],
                ['label' => 'New Project', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'url' => route('admin.projects.create'), 'color' => 'outline'],
                ['label' => 'View Reports', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'url' => route('admin.reports.index'), 'color' => 'outline'],
            ]],

            'activity_feed' => ['items' => ActivityLog::latest()->take(10)->get()->map(fn ($a) => [
                'description' => $a->description,
                'icon' => $a->icon_path,
                'color' => $a->color,
                'causer' => $a->causer_name,
                'label' => $a->subject_label,
                'time' => $a->created_at->diffForHumans(),
            ])],

            default => [],
        };
    }

    private static function stat(float|int $value, string $formatted, ?string $change, string $trend): array
    {
        return ['value' => $value, 'formatted' => $formatted, 'change' => $change, 'trend' => $trend];
    }

    /** @return array<int, mixed> oldest → newest over the last 6 months */
    private static function monthSeries(callable $fn): array
    {
        $out = [];
        for ($i = 5; $i >= 0; $i--) {
            $out[] = $fn(now()->subMonths($i));
        }

        return $out;
    }

    private static function money(float|int $amount): string
    {
        return 'LKR ' . number_format((float) $amount, 2);
    }

    private static function namedHex(string $named): string
    {
        return match ($named) {
            'green' => '#10b981',
            'red' => '#ef4444',
            'amber' => '#f59e0b',
            'blue' => '#3b82f6',
            'purple' => '#6d5cff',
            default => '#94a3b8',
        };
    }

    private static function monthlyRevenueChange(): string
    {
        $thisMonth = (float) Payment::whereYear('payment_date', now()->year)->whereMonth('payment_date', now()->month)->sum('amount');
        $lastMonth = (float) Payment::whereYear('payment_date', now()->subMonth()->year)->whereMonth('payment_date', now()->subMonth()->month)->sum('amount');

        if ($lastMonth == 0.0) {
            return $thisMonth > 0 ? '+100% vs last month' : 'no change vs last month';
        }

        $change = round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);

        return ($change >= 0 ? '+' : '') . $change . '% vs last month';
    }
}
