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
</div>
