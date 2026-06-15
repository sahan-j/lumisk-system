<div>
    <a href="{{ route('admin.reports.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Reports
    </a>

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Profit &amp; Loss</h2>
        <button wire:click="export" class="btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
        </button>
    </div>

    <x-report-filter :from="$from" :to="$to" />

    {{-- Summary --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</span><p class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">{{ money($periodRevenue) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</span><p class="mt-2 text-2xl font-semibold text-red-600 dark:text-red-400">{{ money($periodExpenses) }}</p></div>
        <div class="card p-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Net Profit</span>
            <p class="mt-2 text-2xl font-semibold {{ $periodProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ money($periodProfit) }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $profitMargin }}% margin</p>
        </div>
    </div>

    {{-- Chart --}}
    <div class="card mb-6 p-5">
        <h3 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Monthly P&amp;L</h3>
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Revenue, expenses and profit over the last 12 months</p>
        <div class="h-80" wire:ignore x-data x-init="
            new window.Chart($refs.c.getContext('2d'), {
                type: 'bar',
                data: { labels: @js($chartLabels), datasets: [
                    { label: 'Revenue', data: @js($revenueData), backgroundColor: '#10b981', borderRadius: 4, maxBarThickness: 18 },
                    { label: 'Expenses', data: @js($expenseData), backgroundColor: '#ef4444', borderRadius: 4, maxBarThickness: 18 },
                    { label: 'Profit', data: @js($profitData), backgroundColor: '#6d5cff', borderRadius: 4, maxBarThickness: 18 }
                ] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, labels: { color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280' } } },
                    scales: { x: { grid: { display: false }, ticks: { color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280' } },
                              y: { beginAtZero: true, grid: { color: document.documentElement.classList.contains('dark') ? '#2a2a2a' : '#e5e7eb' }, ticks: { color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280' } } } }
            });
        "><canvas x-ref="c"></canvas></div>
    </div>

    {{-- Monthly table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Month</th>
                        <th class="px-5 py-3 text-right">Revenue</th>
                        <th class="px-5 py-3 text-right">Expenses</th>
                        <th class="px-5 py-3 text-right">Profit</th>
                        <th class="px-5 py-3 text-right">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @foreach ($months as $m)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $m['label'] }}</td>
                            <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ money($m['revenue'], false) }}</td>
                            <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ money($m['expenses'], false) }}</td>
                            <td class="px-5 py-3 text-right text-sm font-medium {{ $m['profit'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ money($m['profit'], false) }}</td>
                            <td class="px-5 py-3 text-right text-sm text-gray-500 dark:text-gray-400">{{ $m['revenue'] > 0 ? round($m['profit'] / $m['revenue'] * 100, 1) : 0 }}%</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50 font-semibold dark:border-ink-600 dark:bg-ink-800">
                        <td class="px-5 py-3 text-sm text-gray-900 dark:text-white">Total (12 mo)</td>
                        <td class="px-5 py-3 text-right text-sm text-gray-900 dark:text-white">{{ money($months->sum('revenue'), false) }}</td>
                        <td class="px-5 py-3 text-right text-sm text-gray-900 dark:text-white">{{ money($months->sum('expenses'), false) }}</td>
                        <td class="px-5 py-3 text-right text-sm {{ $months->sum('profit') >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ money($months->sum('profit'), false) }}</td>
                        <td class="px-5 py-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Expense breakdown for the selected period --}}
    @if ($expenseBreakdown->count())
        <div class="card mt-6 p-5">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Expense Breakdown ({{ $from->format('M d') }} – {{ $to->format('M d, Y') }})</h3>
            @foreach ($expenseBreakdown as $name => $data)
                <div class="mb-3">
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="inline-flex items-center gap-2 text-gray-700 dark:text-gray-200"><span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $data['color'] }}"></span>{{ $name }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ money($data['total']) }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                        <div class="h-full rounded-full" style="width: {{ $periodExpenses > 0 ? round($data['total'] / $periodExpenses * 100) : 0 }}%; background-color: {{ $data['color'] }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
