<div>
    <a href="{{ route('admin.reports.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Reports
    </a>

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Tax Report</h2>
        <button wire:click="export" class="btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
        </button>
    </div>

    <x-report-filter :from="$from" :to="$to" />

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Tax Collected</span><p class="mt-2 text-2xl font-semibold text-brand-purple">{{ money($totalTaxCollected) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Taxable Amount</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ money($totalTaxableAmount) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Effective Tax Rate</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $effectiveRate }}%</p></div>
    </div>

    <div class="card mb-6 p-5">
        <h3 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Tax Collected</h3>
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Over the last 12 months</p>
        <div class="h-72" wire:ignore x-data x-init="
            new window.Chart($refs.c.getContext('2d'), {
                type: 'bar',
                data: { labels: @js($chartLabels), datasets: [{ label: 'Tax', data: @js($chartValues), backgroundColor: '#8b5cf6', borderRadius: 6, maxBarThickness: 40 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    scales: { x: { grid: { display: false }, ticks: { color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280' } },
                              y: { beginAtZero: true, grid: { color: document.documentElement.classList.contains('dark') ? '#2a2a2a' : '#e5e7eb' }, ticks: { color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280' } } } }
            });
        "><canvas x-ref="c"></canvas></div>
    </div>

    {{-- Tax by rate --}}
    <div class="card mb-6 overflow-hidden">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-ink-600"><h3 class="font-semibold text-gray-900 dark:text-white">Tax by Rate</h3></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Tax Rate</th>
                        <th class="px-5 py-3 text-center">Invoices</th>
                        <th class="px-5 py-3 text-right">Taxable Amount</th>
                        <th class="px-5 py-3 text-right">Tax Collected</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($taxByRate as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ rtrim(rtrim(number_format((float) $row->tax_rate, 2), '0'), '.') }}%</td>
                            <td class="px-5 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $row->invoice_count }}</td>
                            <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ money($row->total_subtotal, false) }}</td>
                            <td class="px-5 py-3 text-right text-sm font-medium text-brand-purple">{{ money($row->total_tax, false) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-gray-400">No taxed invoices in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tax invoices --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Invoice #</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3 text-right">Subtotal</th>
                        <th class="px-5 py-3 text-right">Tax</th>
                        <th class="px-5 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($taxInvoices as $inv)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3"><a href="{{ route('admin.invoices.show', $inv) }}" class="font-medium text-gray-900 hover:text-gold dark:text-white">{{ $inv->invoice_number }}</a></td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $inv->client?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $inv->issue_date?->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ money($inv->subtotal, false) }}</td>
                            <td class="px-5 py-3 text-right text-sm font-medium text-brand-purple">{{ money($inv->tax_amount, false) }}</td>
                            <td class="px-5 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ money($inv->total, false) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">No taxed invoices in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $taxInvoices->links() }}</div>
</div>
