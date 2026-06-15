<div>
    <a href="{{ route('admin.reports.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Reports
    </a>

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Client Report</h2>
        <button wire:click="export" class="btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
        </button>
    </div>

    <x-report-filter :from="$from" :to="$to" />

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Clients</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalClients) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Invoiced</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ money($totalInvoiced) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Collected</span><p class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">{{ money($totalPaid) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Outstanding</span><p class="mt-2 text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ money($totalOutstanding) }}</p></div>
    </div>

    @if ($topClient && $topClient['total_paid'] > 0)
        <div class="card mb-6 border-l-4 border-l-gold p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gold">Top Client</p>
            <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $topClient['name'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $topClient['email'] }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-semibold text-green-600 dark:text-green-400">{{ money($topClient['total_paid']) }}</p>
                    <p class="text-xs text-gray-400">{{ $topClient['invoice_count'] }} invoice(s) in period</p>
                </div>
            </div>
        </div>
    @endif

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3 text-center">Invoices</th>
                        <th class="px-5 py-3 text-right">Invoiced</th>
                        <th class="px-5 py-3 text-right">Paid</th>
                        <th class="px-5 py-3 text-right">Outstanding</th>
                        <th class="px-5 py-3 w-40">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($clientStats as $c)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.clients.show', $c['id']) }}" class="font-medium text-gray-900 hover:text-gold dark:text-white">{{ $c['name'] }}</a>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $c['email'] }}</p>
                            </td>
                            <td class="px-5 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $c['invoice_count'] }}</td>
                            <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ money($c['total_invoiced'], false) }}</td>
                            <td class="px-5 py-3 text-right text-sm font-medium text-green-600 dark:text-green-400">{{ money($c['total_paid'], false) }}</td>
                            <td class="px-5 py-3 text-right text-sm text-amber-600 dark:text-amber-400">{{ money($c['outstanding'], false) }}</td>
                            <td class="px-5 py-3">
                                <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                                    <div class="h-full rounded-full bg-gradient-brand" style="width: {{ round($c['total_paid'] / $paidMax * 100) }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">No clients found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
