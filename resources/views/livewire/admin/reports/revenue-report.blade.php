<div>
    <a href="{{ route('admin.reports.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Reports
    </a>

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Revenue Report</h2>
        <button wire:click="export" class="btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
        </button>
    </div>

    <x-report-filter :from="$from" :to="$to" />

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</span><p class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">{{ money($totalRevenue) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Invoices Issued</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($invoiceStats['total']) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Outstanding</span><p class="mt-2 text-2xl font-semibold text-brand-purple">{{ money($invoiceStats['outstanding']) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Overdue</span><p class="mt-2 text-2xl font-semibold text-red-600 dark:text-red-400">{{ number_format($invoiceStats['overdue']) }}</p></div>
    </div>

    {{-- Chart --}}
    <div class="card mb-6 p-5">
        <h3 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Revenue Trend</h3>
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Payments collected over the last 12 months</p>
        <div class="h-72" wire:ignore x-data x-init="
            new window.Chart($refs.c.getContext('2d'), {
                type: 'bar',
                data: { labels: @js($chartLabels), datasets: [{ label: 'Revenue', data: @js($chartValues), backgroundColor: '#6d5cff', borderRadius: 6, maxBarThickness: 40 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    scales: { x: { grid: { display: false }, ticks: { color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280' } },
                              y: { beginAtZero: true, grid: { color: document.documentElement.classList.contains('dark') ? '#2a2a2a' : '#e5e7eb' }, ticks: { color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280' } } } }
            });
        "><canvas x-ref="c"></canvas></div>
    </div>

    {{-- Top clients --}}
    <div class="card mb-6 p-5">
        <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Top Clients by Revenue</h3>
        @forelse ($revenueByClient as $name => $amount)
            <div class="mb-3">
                <div class="mb-1 flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ $name }}</span>
                    <span class="text-gray-900 dark:text-white">{{ money($amount) }} <span class="text-xs text-gray-400">· {{ $totalRevenue > 0 ? round($amount / $totalRevenue * 100, 1) : 0 }}%</span></span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                    <div class="h-full rounded-full bg-gradient-brand" style="width: {{ round($amount / $clientRevenueMax * 100) }}%"></div>
                </div>
            </div>
        @empty
            <p class="py-6 text-center text-sm text-gray-400">No revenue in this period.</p>
        @endforelse
    </div>

    {{-- Invoice list --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Invoice #</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Issue Date</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3 text-right">Paid</th>
                        <th class="px-5 py-3 text-right">Outstanding</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3"><a href="{{ route('admin.invoices.show', $invoice) }}" class="font-medium text-gray-900 hover:text-gold dark:text-white">{{ $invoice->invoice_number }}</a></td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $invoice->client?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->issue_date?->format('M d, Y') }}</td>
                            <td class="px-5 py-3"><x-status-badge :color="$invoice->statusColor()" :label="$invoice->status" /></td>
                            <td class="px-5 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ money($invoice->total) }}</td>
                            <td class="px-5 py-3 text-right text-sm text-green-600 dark:text-green-400">{{ money($invoice->total_paid, false) }}</td>
                            <td class="px-5 py-3 text-right text-sm text-amber-600 dark:text-amber-400">{{ money($invoice->outstanding_balance, false) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">No invoices in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
