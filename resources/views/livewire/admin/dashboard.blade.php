<div>
    {{-- Overdue warning --}}
    @if($overdueCount > 0)
    <div class="mb-5 flex items-center justify-between rounded-lg border border-red-200 border-l-4 border-l-red-500 bg-red-50 px-4 py-3 dark:border-red-800 dark:border-l-red-500 dark:bg-red-900/20">
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-red-700 dark:text-red-400">
                    {{ $overdueCount }} {{ Str::plural('Invoice', $overdueCount) }} Overdue
                </p>
                <p class="text-xs text-red-600 dark:text-red-500">
                    Total outstanding: {{ money($overdueTotal) }}
                </p>
            </div>
        </div>
        <a href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}"
           class="rounded-md border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-700 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-900/30">
            View All &rarr;
        </a>
    </div>
    @endif

    {{-- Overdue projects warning --}}
    @if($overdueProjects > 0)
    <div class="mb-5 flex items-center justify-between rounded-lg border border-amber-200 border-l-4 border-l-amber-500 bg-amber-50 px-4 py-3 dark:border-amber-800 dark:border-l-amber-500 dark:bg-amber-900/20">
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5 flex-shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">
                {{ $overdueProjects }} {{ Str::plural('Project', $overdueProjects) }} past due date
            </p>
        </div>
        <a href="{{ route('admin.projects.index') }}"
           class="rounded-md border border-amber-200 bg-white px-3 py-1.5 text-xs font-medium text-amber-600 hover:bg-amber-50 dark:border-amber-700 dark:bg-transparent dark:text-amber-400 dark:hover:bg-amber-900/30">
            View All &rarr;
        </a>
    </div>
    @endif

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $cards = [
                ['Total Revenue', money($totalRevenue), 'gold', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'],
                ['Outstanding', money($outstanding), 'amber', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Total Clients', number_format($totalClients), 'blue', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z'],
                ['Pending Estimates', number_format($pendingEstimates), 'green', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2'],
                ['Active Projects', number_format($activeProjects), 'blue', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                ['Open Tickets', number_format($openTickets), $openTickets > 0 ? 'red' : 'green', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                ['Expenses (YTD)', money($expensesThisYear), 'red', 'M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 011-1z'],
                ['Net Profit (YTD)', money($netProfit), $netProfit >= 0 ? 'green' : 'red', 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                ['MRR', money($mrr), 'blue', 'M7 7h10v10M17 7L7 17'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $color, $icon])
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</span>
                    <span @class([
                        'flex h-9 w-9 items-center justify-center rounded-lg',
                        'bg-gold/15 text-gold' => $color === 'gold',
                        'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400' => $color === 'amber',
                        'bg-brand-purple/10 text-brand-purple dark:bg-brand-purple/20 dark:text-brand-purple' => $color === 'blue',
                        'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' => $color === 'green',
                        'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' => $color === 'red',
                    ])>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    {{-- Revenue vs Expenses chart --}}
    <div class="mt-6 card p-5">
        <h2 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Revenue vs Expenses</h2>
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Paid invoices and expenses over the last 6 months</p>
        <div class="h-72"
             wire:ignore
             x-data
             x-init="
                const ctx = $refs.revenueChart.getContext('2d');
                const dark = document.documentElement.classList.contains('dark');
                new window.Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @js($chartLabels),
                        datasets: [
                            {
                                label: 'Revenue',
                                data: @js($chartValues),
                                backgroundColor: '#D4AF37',
                                borderRadius: 6,
                                maxBarThickness: 36,
                            },
                            {
                                label: 'Expenses',
                                data: @js($expenseValues),
                                backgroundColor: '#ef4444',
                                borderRadius: 6,
                                maxBarThickness: 36,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: true, labels: { color: dark ? '#9ca3af' : '#6b7280' } } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: dark ? '#9ca3af' : '#6b7280' } },
                            y: { beginAtZero: true, grid: { color: dark ? '#2a2a2a' : '#e5e7eb' }, ticks: { color: dark ? '#9ca3af' : '#6b7280' } }
                        }
                    }
                });
             ">
            <canvas x-ref="revenueChart"></canvas>
        </div>

        {{-- Financial summary (year to date) --}}
        <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-5 dark:border-ink-600 dark:bg-ink-800">
            <p class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Financial Summary — {{ now()->format('Y') }}</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Revenue</p>
                    <p class="mt-1 text-xl font-bold text-green-600 dark:text-green-400">{{ money($revenueThisYear) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Expenses</p>
                    <p class="mt-1 text-xl font-bold text-red-600 dark:text-red-400">{{ money($expensesThisYear) }}</p>
                </div>
                <div class="sm:border-l sm:border-gray-200 sm:pl-4 dark:sm:border-ink-600">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Net Profit</p>
                    <p class="mt-1 text-xl font-bold {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ money($netProfit) }}
                        <span class="text-sm">{{ $netProfit >= 0 ? '▲' : '▼' }}</span>
                    </p>
                    @if ($revenueThisYear > 0)
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ round(($netProfit / $revenueThisYear) * 100, 1) }}% margin</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Upcoming renewals (next 7 days) --}}
    @if ($upcomingRenewals->isNotEmpty())
        <div class="mt-6 card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Upcoming Renewals</h2>
                <a href="{{ route('admin.subscriptions.index') }}" class="text-sm font-medium text-gold hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-ink-700">
                @foreach ($upcomingRenewals as $sub)
                    <a href="{{ route('admin.subscriptions.show', $sub) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-ink-800">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-brand-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10v10M17 7L7 17" /></svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $sub->subscription_number }} · {{ $sub->client?->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $sub->name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ money($sub->amount) }}</p>
                            <p class="text-xs {{ $sub->days_until_next_billing <= 0 ? 'text-amber-500' : 'text-green-500' }}">
                                {{ $sub->days_until_next_billing <= 0 ? 'due today' : 'in ' . $sub->days_until_next_billing . 'd' }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Recent activity --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Recent invoices --}}
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Invoices</h2>
                <a href="{{ route('admin.invoices.index') }}" class="text-sm font-medium text-gold hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-ink-700">
                @forelse ($recentInvoices as $invoice)
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-ink-800">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice->client?->name ?? '—' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ money($invoice->total) }}</p>
                            <x-status-badge :color="$invoice->statusColor()" :label="$invoice->status" class="mt-1" />
                        </div>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-400">No invoices yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent estimates --}}
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Estimates</h2>
                <a href="{{ route('admin.estimates.index') }}" class="text-sm font-medium text-gold hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-ink-700">
                @forelse ($recentEstimates as $estimate)
                    <a href="{{ route('admin.estimates.show', $estimate) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-ink-800">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $estimate->estimate_number }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $estimate->client?->name ?? '—' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ money($estimate->total) }}</p>
                            <x-status-badge :color="$estimate->statusColor()" :label="$estimate->status" class="mt-1" />
                        </div>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-400">No estimates yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent projects --}}
    @if ($recentProjects->count())
    <div class="mt-6 card overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Active Projects</h2>
            <a href="{{ route('admin.projects.index') }}" class="text-sm font-medium text-gold hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-ink-700">
            @foreach ($recentProjects as $project)
                @php $pct = $project->tasks_count ? (int) round($project->done_tasks_count / $project->tasks_count * 100) : 0; @endphp
                <a href="{{ route('admin.projects.show', $project) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 dark:hover:bg-ink-800">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $project->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $project->client?->name ?? 'No client' }}</p>
                    </div>
                    <div class="hidden w-40 sm:block">
                        <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                            <div class="h-full rounded-full bg-gradient-brand" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    <span class="w-10 text-right text-xs text-gray-400">{{ $pct }}%</span>
                    <x-status-badge :color="$project->statusColor()" :label="$project->statusLabel()" />
                </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent tickets --}}
    @if ($recentTickets->count())
    <div class="mt-6 card overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Tickets</h2>
            <a href="{{ route('admin.tickets.index') }}" class="text-sm font-medium text-gold hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-ink-700">
            @foreach ($recentTickets as $ticket)
                <a href="{{ route('admin.tickets.show', $ticket) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 dark:hover:bg-ink-800">
                    <span class="font-mono text-xs font-semibold text-brand-purple">{{ $ticket->ticket_number }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->client?->name ?? '—' }} · {{ $ticket->created_at->diffForHumans() }}</p>
                    </div>
                    <x-status-badge :color="$ticket->statusColor()" :label="$ticket->statusLabel()" />
                </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Activity feed --}}
    <div class="mt-6 card overflow-hidden" wire:poll.30s="loadActivities">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 dark:border-ink-600 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                <svg class="h-5 w-5 text-brand-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l3 8 4-16 3 8h4" /></svg>
                Recent Activity
            </h2>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400">Auto-refreshes every 30s</span>
                <button wire:click="loadActivities" class="inline-flex items-center gap-1 rounded-md border border-brand-purple/30 px-2.5 py-1 text-xs font-medium text-brand-purple hover:bg-brand-purple/5">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Refresh
                </button>
            </div>
        </div>

        {{-- Filter chips --}}
        <div class="flex flex-wrap gap-2 border-b border-gray-100 px-5 py-3 dark:border-ink-700">
            @foreach (['all' => 'All', 'invoices' => 'Invoices', 'payments' => 'Payments', 'tickets' => 'Tickets', 'clients' => 'Clients', 'projects' => 'Projects'] as $key => $label)
                <button wire:click="filterActivity('{{ $key }}')"
                        @class([
                            'rounded-full px-3 py-1 text-xs font-medium transition',
                            'bg-gradient-brand text-white' => $activityFilter === $key,
                            'border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-ink-600 dark:text-gray-300 dark:hover:bg-ink-700' => $activityFilter !== $key,
                        ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Activity list --}}
        <div class="max-h-[480px] divide-y divide-gray-100 overflow-y-auto dark:divide-ink-700">
            @forelse ($activities as $activity)
                <div wire:key="activity-{{ $activity->id }}" class="flex items-start gap-3 px-5 py-3">
                    <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg" style="background-color: {{ $activity->color }}1a; color: {{ $activity->color }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $activity->icon_path }}" /></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-900 dark:text-white">{{ $activity->description }}</p>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span @class([
                                'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium',
                                'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' => $activity->causer_type === 'client',
                                'bg-brand-purple/10 text-brand-purple dark:bg-brand-purple/20' => $activity->causer_type !== 'client',
                            ])>
                                {{ $activity->causer_type === 'client' ? '👤' : '⚡' }} {{ $activity->causer_name ?? 'System' }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                            @if ($activity->subject_label)
                                <span class="rounded bg-brand-purple/8 px-1.5 py-0.5 font-mono text-[10px] text-brand-purple dark:bg-brand-purple/15">{{ $activity->subject_label }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="mt-0.5 flex-shrink-0 whitespace-nowrap text-[10px] text-gray-400">{{ $activity->created_at->format('M d, H:i') }}</span>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <svg class="mx-auto mb-2 h-8 w-8 text-gray-300 dark:text-ink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l3 8 4-16 3 8h4" /></svg>
                    <p class="text-sm text-gray-400">No activity yet</p>
                    <p class="mt-1 text-xs text-gray-400">Actions will appear here as you use the system</p>
                </div>
            @endforelse
        </div>

        {{-- Load more --}}
        @if ($hasMoreActivity)
            <div class="border-t border-gray-100 px-5 py-3 text-center dark:border-ink-700">
                <button wire:click="loadMore" class="text-xs font-medium text-brand-purple hover:underline">Load more activity →</button>
            </div>
        @endif
    </div>

    {{-- Report quick links --}}
    <div class="mt-4 flex flex-wrap gap-2">
        @foreach (['revenue' => 'Revenue Report', 'profit-loss' => 'P&L', 'invoice-aging' => 'Invoice Aging', 'tax' => 'Tax Summary'] as $route => $label)
            <a href="{{ route('admin.reports.' . $route) }}"
               class="rounded-md border border-brand-purple/30 bg-brand-purple/5 px-3 py-1.5 text-xs font-medium text-brand-purple hover:bg-brand-purple/10">
                {{ $label }} →
            </a>
        @endforeach
    </div>
</div>
