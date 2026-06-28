<div>
    @php
        $spanFor = fn ($size) => match ($size) {
            'medium' => 'sm:col-span-2 xl:col-span-2',
            'large' => 'sm:col-span-2 xl:col-span-4',
            default => '',
        };
        $statusBg = ['paid' => 'rgba(16,185,129,0.12)', 'sent' => 'rgba(109,92,255,0.12)', 'overdue' => 'rgba(239,68,68,0.12)', 'accepted' => 'rgba(16,185,129,0.12)', 'rejected' => 'rgba(239,68,68,0.12)'];
        $statusFg = ['paid' => '#10b981', 'sent' => '#6d5cff', 'overdue' => '#ef4444', 'accepted' => '#10b981', 'rejected' => '#ef4444'];
    @endphp

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="refreshWidgets" wire:loading.attr="disabled" class="btn-secondary !py-1.5 text-sm">
                <svg wire:loading.class="animate-spin" wire:target="refreshWidgets" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                Refresh
            </button>
            <button wire:click="toggleEditMode" @class(['btn !py-1.5 text-sm', 'btn-primary' => $editMode, 'btn-secondary' => ! $editMode])>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 17a1 1 0 011-1h6a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM14 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z" /></svg>
                {{ $editMode ? 'Done' : 'Customize' }}
            </button>
        </div>
    </div>

    {{-- Edit-mode panel --}}
    @if ($editMode)
        <div class="card mb-6 p-5">
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900 dark:text-white">Show or hide widgets</p>
                <button wire:click="resetLayout" class="btn-secondary !py-1.5 text-xs">Reset to Default</button>
            </div>
            @php $grouped = collect($availableWidgets)->groupBy('category'); @endphp
            @foreach ($grouped as $category => $widgets)
                <div class="mb-3 last:mb-0">
                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $category }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($widgets as $widgetId => $widget)
                            @php $isVisible = collect($layout)->where('id', $widgetId)->where('visible', true)->isNotEmpty(); @endphp
                            <button type="button" wire:click="toggleWidget('{{ $widgetId }}')"
                                    @class([
                                        'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition',
                                        'text-white' => $isVisible,
                                        'border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 dark:border-ink-600 dark:bg-ink-800 dark:text-gray-300' => ! $isVisible,
                                    ])
                                    @style(['background: var(--brand-gradient)' => $isVisible])>
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $widget['icon'] }}" /></svg>
                                {{ $widget['name'] }}
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $isVisible ? 'M6 18L18 6M6 6l12 12' : 'M12 4v16m8-8H4' }}" /></svg>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Widget grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($layout as $widget)
            @continue(! ($widget['visible'] ?? false))
            @php $data = $widgetData[$widget['id']] ?? []; $def = $availableWidgets[$widget['id']] ?? []; @endphp

            <div wire:key="w-{{ $widget['id'] }}" class="{{ $spanFor($widget['size'] ?? 'small') }}">
                @switch($widget['id'])

                    @case('revenue_stat')
                    @case('outstanding_stat')
                    @case('net_profit_stat')
                    @case('mrr_stat')
                    @case('clients_stat')
                    @case('pipeline_stat')
                    @case('open_tickets_stat')
                    @case('active_projects_stat')
                    @case('time_today')
                        @php $isNeg = $widget['id'] === 'open_tickets_stat' && ($data['value'] ?? 0) > 0; @endphp
                        <div class="card relative overflow-hidden p-5">
                            <div class="flex items-start justify-between">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $def['name'] ?? '' }}</span>
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg" style="background: var(--brand-gradient); opacity:0.14;"></span>
                                <svg class="absolute right-[22px] top-[22px] h-5 w-5" style="color: var(--brand-2);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $def['icon'] ?? '' }}" /></svg>
                            </div>
                            <p class="mt-3 text-2xl font-semibold {{ $isNeg ? 'text-red-500' : 'text-gradient-brand' }}">{{ $data['formatted'] ?? '—' }}</p>
                            @if (! empty($data['change']))
                                <p class="mt-1 text-xs text-gray-400">{{ $data['change'] }}</p>
                            @endif
                        </div>
                        @break

                    @case('revenue_chart')
                        <div class="card p-5">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Revenue vs Expenses</h3>
                            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Last 6 months</p>
                            <div class="h-72" wire:ignore x-data x-init="
                                const dark = document.documentElement.classList.contains('dark');
                                const c1 = getComputedStyle(document.documentElement).getPropertyValue('--brand-1').trim() || '#00d4ff';
                                const d = @js($data);
                                new window.Chart($refs.canvas.getContext('2d'), {
                                    type: 'bar',
                                    data: { labels: d.labels || [], datasets: [
                                        { label: 'Revenue', data: d.revenue || [], backgroundColor: c1, borderRadius: 6, maxBarThickness: 36 },
                                        { label: 'Expenses', data: d.expenses || [], backgroundColor: '#ef4444', borderRadius: 6, maxBarThickness: 36 },
                                    ]},
                                    options: { responsive: true, maintainAspectRatio: false,
                                        plugins: { legend: { labels: { color: dark ? '#9ca3af' : '#6b7280' } } },
                                        scales: { x: { grid: { display: false }, ticks: { color: dark ? '#9ca3af' : '#6b7280' } },
                                                  y: { beginAtZero: true, grid: { color: dark ? '#2a2a2a' : '#e5e7eb' }, ticks: { color: dark ? '#9ca3af' : '#6b7280', callback: v => 'LKR ' + (v/1000).toFixed(0) + 'k' } } } }
                                });
                            ">
                                <canvas x-ref="canvas"></canvas>
                            </div>
                        </div>
                        @break

                    @case('profit_loss_chart')
                        <div class="card p-5">
                            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Profit &amp; Loss — Last 6 Months</h3>
                            <div class="h-72" wire:ignore x-data x-init="
                                const dark = document.documentElement.classList.contains('dark');
                                const c2 = getComputedStyle(document.documentElement).getPropertyValue('--brand-2').trim() || '#6d5cff';
                                const d = @js($data);
                                new window.Chart($refs.canvas.getContext('2d'), {
                                    type: 'line',
                                    data: { labels: d.labels || [], datasets: [
                                        { label: 'Net Profit', data: d.profit || [], borderColor: c2, backgroundColor: c2 + '22', fill: true, tension: 0.4, pointBackgroundColor: c2, pointRadius: 3 },
                                    ]},
                                    options: { responsive: true, maintainAspectRatio: false,
                                        plugins: { legend: { display: false } },
                                        scales: { x: { grid: { display: false }, ticks: { color: dark ? '#9ca3af' : '#6b7280' } },
                                                  y: { grid: { color: dark ? '#2a2a2a' : '#e5e7eb' }, ticks: { color: dark ? '#9ca3af' : '#6b7280', callback: v => 'LKR ' + (v/1000).toFixed(0) + 'k' } } } }
                                });
                            ">
                                <canvas x-ref="canvas"></canvas>
                            </div>
                        </div>
                        @break

                    @case('invoice_status_chart')
                        <div class="card p-5">
                            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Invoice Status</h3>
                            <div class="h-64" wire:ignore x-data x-init="
                                const dark = document.documentElement.classList.contains('dark');
                                const d = @js($data);
                                new window.Chart($refs.canvas.getContext('2d'), {
                                    type: 'doughnut',
                                    data: { labels: d.labels || [], datasets: [{ data: d.data || [], backgroundColor: d.colors || [], borderWidth: 0, hoverOffset: 6 }] },
                                    options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { color: dark ? '#9ca3af' : '#6b7280', padding: 14 } } } }
                                });
                            ">
                                <canvas x-ref="canvas"></canvas>
                            </div>
                        </div>
                        @break

                    @case('quick_actions')
                        <div class="card p-5">
                            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($data['actions'] ?? [] as $action)
                                    <a href="{{ $action['url'] }}" wire:navigate
                                       @class([
                                           'flex flex-col items-center gap-2 rounded-lg p-3 text-center text-xs font-medium',
                                           'text-white' => $action['color'] === 'gradient',
                                           'bg-gray-50 text-gray-600 ring-1 ring-gray-200 hover:bg-gray-100 dark:bg-ink-800 dark:text-gray-300 dark:ring-ink-600' => $action['color'] !== 'gradient',
                                       ])
                                       @style(['background: var(--brand-gradient)' => $action['color'] === 'gradient'])>
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $action['icon'] }}" /></svg>
                                        {{ $action['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @break

                    @case('recent_invoices')
                    @case('recent_estimates')
                        @php $isInv = $widget['id'] === 'recent_invoices'; @endphp
                        <div class="card overflow-hidden">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-ink-700">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $isInv ? 'Recent Invoices' : 'Recent Estimates' }}</span>
                                <a href="{{ route($isInv ? 'admin.invoices.index' : 'admin.estimates.index') }}" wire:navigate class="text-xs font-medium text-brand-purple hover:underline">View all →</a>
                            </div>
                            @forelse ($data['items'] ?? [] as $item)
                                <a href="{{ $item['url'] }}" wire:navigate class="flex items-center gap-3 border-b border-gray-50 px-4 py-2.5 last:border-0 hover:bg-gray-50 dark:border-ink-800 dark:hover:bg-ink-800">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-mono text-xs font-medium text-brand-purple">{{ $item['number'] }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $item['client'] }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $item['amount'] }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $item['date'] }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium" style="background: {{ $statusBg[$item['status']] ?? 'rgba(148,163,184,0.15)' }}; color: {{ $statusFg[$item['status']] ?? '#64748b' }};">{{ ucfirst($item['status']) }}</span>
                                </a>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-gray-400">No items yet</p>
                            @endforelse
                        </div>
                        @break

                    @case('overdue_invoices')
                        <div class="card overflow-hidden" style="border-top:3px solid #ef4444;">
                            <div class="flex items-center justify-between border-b border-red-100 px-4 py-3 dark:border-red-900/30">
                                <span class="flex items-center gap-1.5 text-sm font-semibold text-red-600 dark:text-red-400">
                                    Overdue Invoices
                                    @if (($data['total'] ?? 0) > 0)<span class="rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] text-white">{{ $data['total'] }}</span>@endif
                                </span>
                                <a href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}" wire:navigate class="text-xs font-medium text-red-500 hover:underline">View all →</a>
                            </div>
                            @forelse ($data['items'] ?? [] as $item)
                                <a href="{{ $item['url'] }}" wire:navigate class="flex items-center gap-3 border-b border-red-50 px-4 py-2.5 last:border-0 hover:bg-red-50/50 dark:border-red-900/20 dark:hover:bg-red-900/10">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-mono text-xs font-medium text-red-500">{{ $item['number'] }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $item['client'] }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-xs font-semibold text-red-600 dark:text-red-400">{{ $item['amount'] }}</p>
                                        <p class="text-[10px] text-red-500">{{ $item['days_overdue'] }}d overdue</p>
                                    </div>
                                </a>
                            @empty
                                <p class="px-4 py-6 text-center text-sm text-green-600 dark:text-green-400">✓ No overdue invoices</p>
                            @endforelse
                        </div>
                        @break

                    @case('upcoming_renewals')
                        <div class="card overflow-hidden">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-ink-700">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Upcoming Renewals</span>
                                <a href="{{ route('admin.subscriptions.index') }}" wire:navigate class="text-xs font-medium text-brand-purple hover:underline">View all →</a>
                            </div>
                            @forelse ($data['items'] ?? [] as $item)
                                <a href="{{ $item['url'] }}" wire:navigate class="flex items-center gap-3 border-b border-gray-50 px-4 py-2.5 last:border-0 hover:bg-gray-50 dark:border-ink-800 dark:hover:bg-ink-800">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-medium text-gray-900 dark:text-white">{{ $item['name'] }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $item['client'] }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-xs font-semibold text-brand-purple">{{ $item['amount'] }}</p>
                                        <p class="text-[10px] {{ $item['days_until'] <= 3 ? 'text-red-500' : 'text-gray-400' }}">in {{ $item['days_until'] }}d · {{ $item['date'] }}</p>
                                    </div>
                                </a>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-gray-400">No renewals in next 14 days</p>
                            @endforelse
                        </div>
                        @break

                    @case('pipeline_funnel')
                        <div class="card p-5">
                            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Pipeline Funnel</h3>
                            @php $maxCount = collect($data['stages'] ?? [])->max('count') ?: 1; @endphp
                            @forelse ($data['stages'] ?? [] as $stage)
                                <div class="mb-3 last:mb-0">
                                    <div class="mb-1 flex justify-between text-xs">
                                        <span class="text-gray-500 dark:text-gray-400">{{ $stage['name'] }}</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $stage['count'] }} leads</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                                        <div class="h-full rounded-full" style="width: {{ round(($stage['count'] / $maxCount) * 100) }}%; background: {{ $stage['color'] }};"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="py-6 text-center text-sm text-gray-400">No pipeline stages</p>
                            @endforelse
                        </div>
                        @break

                    @case('top_clients')
                        <div class="card p-5">
                            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Top Clients by Revenue</h3>
                            @forelse ($data['items'] ?? [] as $i => $client)
                                <div class="flex items-center gap-3 border-b border-gray-50 py-2 last:border-0 dark:border-ink-800">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-bold text-white" style="background: var(--brand-gradient);">{{ $i + 1 }}</span>
                                    <a href="{{ $client['url'] }}" wire:navigate class="flex-1 truncate text-sm font-medium text-gray-900 hover:text-brand-purple dark:text-white">{{ $client['name'] }}</a>
                                    <span class="shrink-0 font-mono text-xs font-semibold text-brand-purple">{{ $client['total'] }}</span>
                                </div>
                            @empty
                                <p class="py-6 text-center text-sm text-gray-400">No client revenue yet</p>
                            @endforelse
                        </div>
                        @break

                    @case('recent_tickets')
                        <div class="card overflow-hidden">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-ink-700">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Recent Tickets</span>
                                <a href="{{ route('admin.tickets.index') }}" wire:navigate class="text-xs font-medium text-brand-purple hover:underline">View all →</a>
                            </div>
                            @forelse ($data['items'] ?? [] as $item)
                                <a href="{{ $item['url'] }}" wire:navigate class="flex items-center gap-3 border-b border-gray-50 px-4 py-2.5 last:border-0 hover:bg-gray-50 dark:border-ink-800 dark:hover:bg-ink-800">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-medium text-gray-900 dark:text-white">{{ $item['subject'] }}</p>
                                        <p class="font-mono text-[10px] text-gray-400">{{ $item['number'] }} · {{ $item['client'] }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium" style="background: {{ $item['priority_color'] }}1a; color: {{ $item['priority_color'] }};">{{ $item['priority'] }}</span>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium" style="background: {{ $item['status_color'] }}1a; color: {{ $item['status_color'] }};">{{ $item['status'] }}</span>
                                </a>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-gray-400">No open tickets</p>
                            @endforelse
                        </div>
                        @break

                    @case('project_progress')
                        <div class="card p-5">
                            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Project Progress</h3>
                            @forelse ($data['items'] ?? [] as $project)
                                <div class="mb-3 last:mb-0">
                                    <div class="mb-1 flex justify-between text-xs">
                                        <a href="{{ $project['url'] }}" wire:navigate class="font-medium text-gray-900 hover:text-brand-purple dark:text-white">{{ $project['name'] }}</a>
                                        <span class="font-semibold text-brand-purple">{{ $project['completion'] }}%</span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                                        <div class="h-full rounded-full" style="width: {{ $project['completion'] }}%; background: var(--brand-gradient);"></div>
                                    </div>
                                    <p class="mt-1 text-[10px] text-gray-400">{{ $project['tasks_done'] }}/{{ $project['tasks_total'] }} tasks</p>
                                </div>
                            @empty
                                <p class="py-6 text-center text-sm text-gray-400">No active projects</p>
                            @endforelse
                        </div>
                        @break

                    @case('expense_breakdown')
                        <div class="card p-5">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Expenses This Month</h3>
                                <span class="text-sm font-semibold text-red-500">LKR {{ number_format($data['total'] ?? 0, 0) }}</span>
                            </div>
                            @php $maxExp = collect($data['items'] ?? [])->max('total') ?: 1; @endphp
                            @forelse ($data['items'] ?? [] as $exp)
                                <div class="mb-2.5 last:mb-0">
                                    <div class="mb-1 flex justify-between text-xs">
                                        <span class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300"><span class="h-2 w-2 rounded-full" style="background: {{ $exp['color'] }};"></span>{{ $exp['name'] }}</span>
                                        <span class="font-medium text-gray-900 dark:text-white">LKR {{ number_format($exp['total'], 0) }}</span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                                        <div class="h-full rounded-full" style="width: {{ round(($exp['total'] / $maxExp) * 100) }}%; background: {{ $exp['color'] }};"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="py-6 text-center text-sm text-gray-400">No expenses this month</p>
                            @endforelse
                        </div>
                        @break

                    @case('activity_feed')
                        <div class="card overflow-hidden" wire:poll.30s="refreshWidgets">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-ink-700">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Recent Activity</span>
                                <span class="text-[11px] text-gray-400">Auto-refreshes every 30s</span>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse ($data['items'] ?? [] as $activity)
                                    <div class="flex items-start gap-3 border-b border-gray-50 px-4 py-2.5 last:border-0 dark:border-ink-800">
                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg" style="background: {{ $activity['color'] }}1a;">
                                            <svg class="h-3.5 w-3.5" style="color: {{ $activity['color'] }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $activity['icon'] }}" /></svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs leading-snug text-gray-700 dark:text-gray-200">{{ $activity['description'] }}</p>
                                            <p class="mt-0.5 text-[10px] text-gray-400">
                                                {{ $activity['causer'] }} · {{ $activity['time'] }}
                                                @if ($activity['label'])<span class="ml-1 rounded px-1 font-mono" style="color: var(--brand-2); background: var(--brand-2-soft);">{{ $activity['label'] }}</span>@endif
                                            </p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="px-4 py-8 text-center text-sm text-gray-400">No recent activity</p>
                                @endforelse
                            </div>
                        </div>
                        @break

                @endswitch
            </div>
        @endforeach
    </div>
</div>
