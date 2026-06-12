<div>
    <div class="mb-6 rounded-xl bg-navy p-6 text-white dark:bg-ink-850">
        <p class="text-sm text-gray-300">Welcome back,</p>
        <h2 class="text-2xl font-semibold">{{ $client->name }}</h2>
        @if ($client->company_name)
            <p class="mt-1 text-sm text-gray-300">{{ $client->company_name }}</p>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Invoices</span>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalInvoices) }}</p>
        </div>
        <div class="card p-5">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Paid Amount</span>
            <p class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">{{ money($paidAmount) }}</p>
        </div>
        <div class="card p-5">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Unpaid Amount</span>
            <p class="mt-2 text-2xl font-semibold text-brand-purple">{{ money($unpaidAmount) }}</p>
        </div>
        <div class="card p-5">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Estimates</span>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($pendingEstimates) }}</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Invoices</h2>
                <a href="{{ route('portal.invoices.index') }}" class="text-sm font-medium text-brand-purple hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-ink-700">
                @forelse ($recentInvoices as $invoice)
                    <a href="{{ route('portal.invoices.show', $invoice) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-ink-800">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice->issue_date?->format('M d, Y') }}</p>
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

        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Estimates</h2>
                <a href="{{ route('portal.estimates.index') }}" class="text-sm font-medium text-brand-purple hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-ink-700">
                @forelse ($recentEstimates as $estimate)
                    <a href="{{ route('portal.estimates.show', $estimate) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-ink-800">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $estimate->estimate_number }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $estimate->issue_date?->format('M d, Y') }}</p>
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
</div>
