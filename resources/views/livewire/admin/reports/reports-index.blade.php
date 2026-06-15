<div>
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Reports &amp; Analytics</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Financial and operational reports with CSV export.</p>
    </div>

    @php
        $reports = [
            ['admin.reports.revenue', 'Revenue Report', 'Invoice revenue, payment tracking, client breakdown', '#10b981', 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
            ['admin.reports.expenses', 'Expense Report', 'Business costs, category breakdown, payment methods', '#ef4444', 'M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 011-1z'],
            ['admin.reports.profit-loss', 'Profit &amp; Loss', 'Monthly P&amp;L, net profit, margin analysis', '#6d5cff', 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
            ['admin.reports.clients', 'Client Report', 'Revenue per client, top clients, outstanding balances', '#00d4ff', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z'],
            ['admin.reports.invoice-aging', 'Invoice Aging', 'Overdue analysis, aging buckets, collection tracking', '#f59e0b', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['admin.reports.tax', 'Tax Report', 'Tax collected by rate, monthly breakdown, tax invoices', '#8b5cf6', 'M9 7h6m-6 4h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z'],
            ['admin.reports.projects', 'Project Financials', 'Per-project revenue, expenses, profit tracking', '#06b6d4', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($reports as [$route, $title, $desc, $color, $icon])
            <a href="{{ route($route) }}" wire:navigate
               class="card block p-5 transition hover:shadow-md" style="border-top: 3px solid {{ $color }}">
                <span class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg" style="background-color: {{ $color }}1a; color: {{ $color }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                </span>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{!! $title !!}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{!! $desc !!}</p>
            </a>
        @endforeach
    </div>
</div>
