<div>
    <a href="{{ route('admin.reports.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Reports
    </a>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Project Financials</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Revenue, expenses and profit per project (lifetime).</p>
        </div>
        <button wire:click="export" class="btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
        </button>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <div class="card p-4"><span class="text-xs text-gray-500 dark:text-gray-400">Projects</span><p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($totalProjects) }}</p></div>
        <div class="card p-4"><span class="text-xs text-gray-500 dark:text-gray-400">Invoiced</span><p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ money($totalInvoiced, false) }}</p></div>
        <div class="card p-4"><span class="text-xs text-gray-500 dark:text-gray-400">Collected</span><p class="mt-1 text-lg font-semibold text-green-600 dark:text-green-400">{{ money($totalPaid, false) }}</p></div>
        <div class="card p-4"><span class="text-xs text-gray-500 dark:text-gray-400">Expenses</span><p class="mt-1 text-lg font-semibold text-red-600 dark:text-red-400">{{ money($totalExpenses, false) }}</p></div>
        <div class="card p-4"><span class="text-xs text-gray-500 dark:text-gray-400">Net Profit</span><p class="mt-1 text-lg font-semibold {{ $totalProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ money($totalProfit, false) }}</p></div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Project</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 w-32">Progress</th>
                        <th class="px-5 py-3 text-center">Tasks</th>
                        <th class="px-5 py-3 text-right">Invoiced</th>
                        <th class="px-5 py-3 text-right">Paid</th>
                        <th class="px-5 py-3 text-right">Expenses</th>
                        <th class="px-5 py-3 text-right">Profit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($projectStats as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.projects.show', $p['id']) }}" class="font-medium text-gray-900 hover:text-gold dark:text-white">{{ $p['name'] }}</a>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $p['client'] }}</p>
                            </td>
                            <td class="px-5 py-3"><x-status-badge :color="$p['status_color']" :label="$p['status_label']" /></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                                        <div class="h-full rounded-full bg-gradient-brand" style="width: {{ $p['completion'] }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $p['completion'] }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $p['done_tasks'] }}/{{ $p['total_tasks'] }}</td>
                            <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ money($p['total_invoiced'], false) }}</td>
                            <td class="px-5 py-3 text-right text-sm text-green-600 dark:text-green-400">{{ money($p['total_paid'], false) }}</td>
                            <td class="px-5 py-3 text-right text-sm text-red-600 dark:text-red-400">{{ money($p['total_expenses'], false) }}</td>
                            <td class="px-5 py-3 text-right text-sm font-medium {{ $p['profit'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ money($p['profit'], false) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400">No projects found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
