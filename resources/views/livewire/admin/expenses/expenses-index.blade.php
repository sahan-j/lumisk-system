<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Expenses</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track business spending and profitability.</p>
        </div>
        @permission('expenses.create')
        <a href="{{ route('admin.expenses.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            New Expense
        </a>
        @endpermission
    </div>

    {{-- Summary stats --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">This Month</span>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ money($totalThisMonth) }}</p>
        </div>
        <div class="card p-5">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">This Year</span>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ money($totalThisYear) }}</p>
        </div>
        <div class="card p-5">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">All Time</span>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ money($totalAll) }}</p>
        </div>
        <div class="card p-5">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Unbilled (billable)</span>
            <p class="mt-2 text-2xl font-semibold text-cyan-600 dark:text-cyan-400">{{ money($unbilledBillable) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div class="relative xl:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search title or reference…" class="form-input-base pl-10">
        </div>
        <select wire:model.live="category" class="form-input-base">
            <option value="">All categories</option>
            @foreach ($filterCategories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        <select wire:model.live="client" class="form-input-base">
            <option value="">All clients</option>
            @foreach ($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        <select wire:model.live="method" class="form-input-base">
            <option value="">All methods</option>
            @foreach ($methods as $m)<option value="{{ $m }}">{{ ucwords(str_replace('_', ' ', $m)) }}</option>@endforeach
        </select>
        <select wire:model.live="billable" class="form-input-base">
            <option value="">All expenses</option>
            <option value="billable">Billable</option>
            <option value="non_billable">Non-billable</option>
        </select>
        <input wire:model.live="from" type="date" class="form-input-base" title="From">
        <input wire:model.live="to" type="date" class="form-input-base" title="To">
        <button wire:click="exportCsv" class="btn-secondary justify-center">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
        </button>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Title</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3">Payment</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Billable</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($expenses as $expense)
                        <tr wire:key="expense-{{ $expense->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $expense->expense_date?->format('M d, Y') }}</td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $expense->title }}</p>
                                @if ($expense->description)<p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($expense->description, 50) }}</p>@endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($expense->category)
                                    <span class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <span class="h-2.5 w-2.5 flex-shrink-0 rounded-full" style="background-color: {{ $expense->category->color }}"></span>
                                        {{ $expense->category->name }}
                                    </span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-sm font-medium text-gray-900 dark:text-white">{{ money($expense->amount) }}</td>
                            <td class="px-5 py-3"><x-status-badge color="gray" :label="$expense->payment_method_label" /></td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                @if ($expense->client)
                                    <a href="{{ route('admin.clients.show', $expense->client) }}" class="hover:text-gold">{{ $expense->client->name }}</a>
                                @elseif ($expense->project)
                                    <a href="{{ route('admin.projects.show', $expense->project) }}" class="hover:text-gold">{{ $expense->project->name }}</a>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($expense->is_billable && $expense->is_billed)
                                    <x-status-badge color="green" label="Billed" />
                                @elseif ($expense->is_billable)
                                    <x-status-badge color="blue" label="Billable" />
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($expense->receipt)
                                        <a href="{{ Storage::url($expense->receipt) }}" target="_blank" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="View receipt">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.expenses.edit', $expense) }}" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                    <button wire:click="confirmDelete({{ $expense->id }})" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400">No expenses found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $expenses->links() }}</div>

    {{-- Category breakdown --}}
    @if ($categories->where('expenses_sum_amount', '>', 0)->count())
        <div class="card mt-6 p-6">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Spending by Category</h3>
            <div class="space-y-4">
                @foreach ($categories as $cat)
                    @continue(! $cat->expenses_sum_amount)
                    @php $pct = $breakdownTotal > 0 ? round($cat->expenses_sum_amount / $breakdownTotal * 100, 1) : 0; @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="inline-flex items-center gap-2 font-medium text-gray-700 dark:text-gray-200">
                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $cat->color }}"></span>
                                {{ $cat->name }}
                                <span class="text-xs font-normal text-gray-400">({{ $cat->expenses_count }})</span>
                            </span>
                            <span class="text-gray-900 dark:text-white">{{ money($cat->expenses_sum_amount) }} <span class="text-xs text-gray-400">· {{ $pct }}%</span></span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                            <div class="h-full rounded-full" style="width: {{ $pct }}%; background-color: {{ $cat->color }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($confirmingDelete)
        <x-app-modal title="Delete expense?" close="$set('confirmingDelete', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">This will soft-delete the expense and remove its receipt file. This action cannot be undone from the UI.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </x-app-modal>
    @endif
</div>
