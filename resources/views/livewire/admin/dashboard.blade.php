<div>
    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $cards = [
                ['Total Revenue', money($totalRevenue), 'gold', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'],
                ['Outstanding', money($outstanding), 'amber', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Total Clients', number_format($totalClients), 'blue', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z'],
                ['Pending Estimates', number_format($pendingEstimates), 'green', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2'],
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

    {{-- Revenue chart --}}
    <div class="mt-6 card p-5">
        <h2 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Revenue</h2>
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Paid invoices over the last 6 months</p>
        <div class="h-72"
             x-data
             x-init="
                const ctx = $refs.revenueChart.getContext('2d');
                const dark = document.documentElement.classList.contains('dark');
                new window.Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @js($chartLabels),
                        datasets: [{
                            label: 'Revenue',
                            data: @js($chartValues),
                            backgroundColor: '#D4AF37',
                            borderRadius: 6,
                            maxBarThickness: 48,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: dark ? '#9ca3af' : '#6b7280' } },
                            y: { beginAtZero: true, grid: { color: dark ? '#2a2a2a' : '#e5e7eb' }, ticks: { color: dark ? '#9ca3af' : '#6b7280' } }
                        }
                    }
                });
             ">
            <canvas x-ref="revenueChart"></canvas>
        </div>
    </div>

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
</div>
