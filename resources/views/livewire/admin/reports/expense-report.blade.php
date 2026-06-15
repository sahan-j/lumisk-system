<div>
    <a href="{{ route('admin.reports.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Reports
    </a>

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Expense Report</h2>
        <button wire:click="export" class="btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
        </button>
    </div>

    <x-report-filter :from="$from" :to="$to" />

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</span><p class="mt-2 text-2xl font-semibold text-red-600 dark:text-red-400">{{ money($totalExpenses) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Count</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($count) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Avg per Expense</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ money($avgPerExpense) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Unbilled (billable)</span><p class="mt-2 text-2xl font-semibold text-cyan-600 dark:text-cyan-400">{{ money($unbilledBillable) }}</p></div>
    </div>

    <div class="card mb-6 p-5">
        <h3 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Expense Trend</h3>
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Expenses over the last 12 months</p>
        <div class="h-72" wire:ignore x-data x-init="
            new window.Chart($refs.c.getContext('2d'), {
                type: 'line',
                data: { labels: @js($chartLabels), datasets: [{ label: 'Expenses', data: @js($chartValues), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)', fill: true, tension: 0.3 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    scales: { x: { grid: { display: false }, ticks: { color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280' } },
                              y: { beginAtZero: true, grid: { color: document.documentElement.classList.contains('dark') ? '#2a2a2a' : '#e5e7eb' }, ticks: { color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280' } } } }
            });
        "><canvas x-ref="c"></canvas></div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Category breakdown --}}
        <div class="card p-5">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">By Category</h3>
            @forelse ($byCategory as $name => $data)
                <div class="mb-3">
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="inline-flex items-center gap-2 text-gray-700 dark:text-gray-200">
                            <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $data['color'] }}"></span>{{ $name }}
                            <span class="text-xs text-gray-400">({{ $data['count'] }})</span>
                        </span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ money($data['total']) }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                        <div class="h-full rounded-full" style="width: {{ $totalExpenses > 0 ? round($data['total'] / $totalExpenses * 100) : 0 }}%; background-color: {{ $data['color'] }}"></div>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-gray-400">No expenses in this period.</p>
            @endforelse
        </div>

        {{-- By payment method --}}
        <div class="card p-5">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">By Payment Method</h3>
            <div class="divide-y divide-gray-100 dark:divide-ink-700">
                @forelse ($byPaymentMethod as $row)
                    <div class="flex items-center justify-between py-2.5 text-sm">
                        <span class="text-gray-700 dark:text-gray-200">{{ ucwords(str_replace('_', ' ', $row->payment_method)) }} <span class="text-xs text-gray-400">({{ $row->count }})</span></span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ money($row->total) }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">No expenses in this period.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Expense list --}}
    <div class="card mt-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Title</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Payment</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($expenses as $expense)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $expense->expense_date?->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $expense->title }}</td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $expense->category?->name ?? '—' }}</td>
                            <td class="px-5 py-3"><x-status-badge color="gray" :label="$expense->payment_method_label" /></td>
                            <td class="px-5 py-3 text-right font-mono text-sm font-medium text-gray-900 dark:text-white">{{ money($expense->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400">No expenses in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $expenses->links() }}</div>
</div>
