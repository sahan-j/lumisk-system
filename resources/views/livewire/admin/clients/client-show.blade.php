<div>
    <a href="{{ route('admin.clients.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Back to clients
    </a>

    {{-- Client header --}}
    <div class="card mb-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-navy text-xl font-semibold text-white dark:bg-gold dark:text-ink-900">
                    {{ strtoupper(substr($client->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $client->name }}</h2>
                    @if ($client->company_name)<p class="text-sm text-gray-500 dark:text-gray-400">{{ $client->company_name }}</p>@endif
                    <div class="mt-1">
                        @if ($client->portal_enabled)
                            <x-status-badge color="green" label="Portal enabled" />
                        @else
                            <x-status-badge color="gray" label="Portal disabled" />
                        @endif
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm">
                <span class="text-gray-500 dark:text-gray-400">Email</span><span class="text-gray-900 dark:text-white">{{ $client->email }}</span>
                <span class="text-gray-500 dark:text-gray-400">Phone</span><span class="text-gray-900 dark:text-white">{{ $client->phone ?: '—' }}</span>
                <span class="text-gray-500 dark:text-gray-400">Address</span><span class="text-gray-900 dark:text-white">{{ $client->address ?: '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Paid</span><p class="mt-1 text-xl font-semibold text-green-600 dark:text-green-400">{{ money($totalPaid) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Outstanding</span><p class="mt-1 text-xl font-semibold text-amber-600 dark:text-amber-400">{{ money($totalOutstanding) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Documents</span><p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $invoices->count() + $estimates->count() }}</p></div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Invoices --}}
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
                <h3 class="font-semibold text-gray-900 dark:text-white">Invoices</h3>
                <a href="{{ route('admin.invoices.create', ['client' => $client->id]) }}" class="text-sm font-medium text-gold hover:underline">New invoice</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-ink-700">
                @forelse ($invoices as $invoice)
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-ink-800">
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
                    <p class="px-5 py-8 text-center text-sm text-gray-400">No invoices.</p>
                @endforelse
            </div>
        </div>

        {{-- Estimates --}}
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
                <h3 class="font-semibold text-gray-900 dark:text-white">Estimates</h3>
                <a href="{{ route('admin.estimates.create', ['client' => $client->id]) }}" class="text-sm font-medium text-gold hover:underline">New estimate</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-ink-700">
                @forelse ($estimates as $estimate)
                    <a href="{{ route('admin.estimates.show', $estimate) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-ink-800">
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
                    <p class="px-5 py-8 text-center text-sm text-gray-400">No estimates.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Projects --}}
    <div class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
            <h3 class="font-semibold text-gray-900 dark:text-white">Projects</h3>
            <a href="{{ route('admin.projects.create', ['client' => $client->id]) }}" class="text-sm font-medium text-gold hover:underline">New project</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-ink-700">
            @forelse ($projects as $project)
                @php $pct = $project->tasks_count ? (int) round($project->done_tasks_count / $project->tasks_count * 100) : 0; @endphp
                <a href="{{ route('admin.projects.show', $project) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 dark:hover:bg-ink-800">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $project->name }}</p>
                        <div class="mt-1.5 h-1.5 w-full max-w-xs overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                            <div class="h-full rounded-full bg-gradient-brand" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    <div class="flex flex-shrink-0 items-center gap-3">
                        <span class="text-xs text-gray-400">{{ $pct }}%</span>
                        <x-status-badge :color="$project->statusColor()" :label="$project->statusLabel()" />
                    </div>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-400">No projects.</p>
            @endforelse
        </div>
    </div>

    {{-- Subscriptions --}}
    @permission('subscriptions.view')
    <div class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Subscriptions</h3>
                @php $clientMrr = $subscriptions->where('status', 'active')->sum('monthly_value'); @endphp
                @if ($clientMrr > 0)<p class="text-xs text-gray-500 dark:text-gray-400">{{ money($clientMrr) }} MRR from this client</p>@endif
            </div>
            @permission('subscriptions.create')
            <a href="{{ route('admin.subscriptions.create', ['client' => $client->id]) }}" class="text-sm font-medium text-gold hover:underline">Add subscription</a>
            @endpermission
        </div>
        <div class="divide-y divide-gray-100 dark:divide-ink-700">
            @forelse ($subscriptions as $sub)
                <a href="{{ route('admin.subscriptions.show', $sub) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-ink-800">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $sub->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $sub->subscription_number }} · {{ $sub->billing_cycle_label }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ money($sub->amount) }}</span>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white" style="background-color: {{ $sub->status_color }}">{{ $sub->status_label }}</span>
                    </div>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-400">No subscriptions.</p>
            @endforelse
        </div>
    </div>
    @endpermission

    {{-- Billable expenses --}}
    <div class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Billable Expenses</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Unbilled expenses marked billable for this client.</p>
            </div>
            @if ($billableExpenses->count())
                <button wire:click="createInvoiceFromExpenses"
                        wire:confirm="Create a draft invoice from {{ $billableExpenses->count() }} billable expense(s)? They will be marked as billed."
                        class="btn-primary !px-3 !py-1.5 text-xs">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Create Invoice from Expenses
                </button>
            @endif
        </div>
        <div class="divide-y divide-gray-100 dark:divide-ink-700">
            @forelse ($billableExpenses as $expense)
                <div class="flex items-center justify-between px-5 py-3">
                    <div class="flex items-center gap-3">
                        @if ($expense->category)
                            <span class="h-2.5 w-2.5 flex-shrink-0 rounded-full" style="background-color: {{ $expense->category->color }}"></span>
                        @endif
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $expense->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $expense->expense_date?->format('M d, Y') }}{{ $expense->category ? ' · ' . $expense->category->name : '' }}</p>
                        </div>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ money($expense->amount) }}</p>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-400">No unbilled billable expenses.</p>
            @endforelse
            @if ($billableExpenses->count())
                <div class="flex items-center justify-between bg-gray-50 px-5 py-3 dark:bg-ink-800">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Total billable</span>
                    <span class="text-sm font-semibold text-cyan-600 dark:text-cyan-400">{{ money($billableExpenses->sum('amount')) }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
