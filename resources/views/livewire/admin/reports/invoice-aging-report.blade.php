<div>
    <a href="{{ route('admin.reports.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Reports
    </a>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Invoice Aging</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Outstanding invoices grouped by days past due.</p>
        </div>
        <button wire:click="export" class="btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
        </button>
    </div>

    @php
        $bucketMeta = [
            'current' => ['Current', 'text-gray-600 dark:text-gray-300', 'border-l-gray-400'],
            '1_30' => ['1–30 days', 'text-amber-600 dark:text-amber-400', 'border-l-amber-400'],
            '31_60' => ['31–60 days', 'text-orange-600 dark:text-orange-400', 'border-l-orange-500'],
            '61_90' => ['61–90 days', 'text-red-600 dark:text-red-400', 'border-l-red-500'],
            '90_plus' => ['90+ days', 'text-red-700 dark:text-red-300', 'border-l-red-700'],
        ];
    @endphp

    {{-- Aging summary cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        @foreach ($bucketMeta as $key => [$label, $textClass, $borderClass])
            <div class="card border-l-4 {{ $borderClass }} p-4">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</span>
                <p class="mt-1 text-lg font-semibold {{ $textClass }}">{{ money($summary[$key]['total'], false) }}</p>
                <p class="text-xs text-gray-400">{{ $summary[$key]['count'] }} invoice(s)</p>
            </div>
        @endforeach
    </div>

    {{-- Combined aging table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Invoice #</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Issue Date</th>
                        <th class="px-5 py-3">Due Date</th>
                        <th class="px-5 py-3 text-center">Days Overdue</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3">Bucket</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @php $any = false; @endphp
                    @foreach ($bucketMeta as $key => [$label, $textClass, $borderClass])
                        @foreach ($buckets[$key] as $inv)
                            @php $any = true; $days = $inv->due_date && $inv->due_date->lt(today()) ? (int) today()->diffInDays($inv->due_date) : 0; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                                <td class="px-5 py-3"><a href="{{ route('admin.invoices.show', $inv) }}" class="font-medium text-gray-900 hover:text-gold dark:text-white">{{ $inv->invoice_number }}</a></td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $inv->client?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $inv->issue_date?->format('M d, Y') }}</td>
                                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $inv->due_date?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-5 py-3 text-center text-sm {{ $days > 0 ? $textClass : 'text-gray-400' }}">{{ $days > 0 ? $days : '—' }}</td>
                                <td class="px-5 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ money($inv->total) }}</td>
                                <td class="px-5 py-3 text-xs font-medium {{ $textClass }}">{{ $label }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    @if (! $any)
                        <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">No outstanding invoices. 🎉</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
