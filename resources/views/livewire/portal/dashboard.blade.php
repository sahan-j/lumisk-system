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
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ currency_amount($invoice, $invoice->total) }}</p>
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
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ currency_amount($estimate, $estimate->total) }}</p>
                            <x-status-badge :color="$estimate->statusColor()" :label="$estimate->status" class="mt-1" />
                        </div>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-400">No estimates yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Your recent activity --}}
    @if ($activities->count())
    <div class="mt-6 card overflow-hidden">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-ink-600">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Your Recent Activity</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-ink-700">
            @foreach ($activities as $activity)
                <div class="flex items-start gap-3 px-5 py-3">
                    <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg" style="background-color: {{ $activity->color }}1a; color: {{ $activity->color }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $activity->icon_path }}" /></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-900 dark:text-white">{{ $activity->description }}</p>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                            @if ($activity->subject_label)
                                <span class="rounded bg-brand-purple/8 px-1.5 py-0.5 font-mono text-[10px] text-brand-purple dark:bg-brand-purple/15">{{ $activity->subject_label }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
