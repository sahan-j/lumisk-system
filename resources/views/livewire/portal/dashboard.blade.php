<div>

{{-- Welcome header --}}
<div class="relative mb-6 overflow-hidden rounded-xl border p-6
            border-brand-purple/20 dark:border-brand-purple/30"
     style="background: linear-gradient(135deg, rgba(0,212,255,0.07) 0%, rgba(109,92,255,0.09) 100%);">
    <div class="pointer-events-none absolute -right-6 -top-6 h-32 w-32 rounded-full"
         style="background: radial-gradient(circle, rgba(109,92,255,0.12), transparent 70%);"></div>
    <div class="relative flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">{{ now()->format('l, F j, Y') }}</p>
            <h1 class="mb-1.5 text-2xl font-bold text-gray-900 dark:text-white">
                Welcome back, {{ $client->name }}! 👋
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @if ($stats['outstanding'] > 0)
                    You have <strong class="text-red-500">{{ money($stats['outstanding']) }}</strong> outstanding.
                @elseif ($stats['pending_estimates'] > 0)
                    You have <strong class="text-brand-purple">{{ $stats['pending_estimates'] }} estimate(s)</strong> awaiting your review.
                @elseif ($stats['open_tickets'] > 0)
                    You have <strong class="text-amber-500">{{ $stats['open_tickets'] }} open support ticket(s).</strong>
                @else
                    Everything looks good! 🎉
                @endif
            </p>
        </div>
        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl text-xl font-bold text-white"
             style="background: var(--brand-gradient, linear-gradient(135deg,#00d4ff,#6d5cff));">
            {{ strtoupper(substr($client->name, 0, 1)) }}
        </div>
    </div>
</div>

{{-- Alert banners --}}
@if ($stats['overdue_count'] > 0)
<div class="mb-4 flex items-center gap-3 rounded-lg border border-l-4 border-red-200 border-l-red-500 bg-red-50 p-3.5 dark:border-red-900/40 dark:border-l-red-500 dark:bg-red-900/10">
    <svg class="h-5 w-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-red-700 dark:text-red-400">{{ $stats['overdue_count'] }} overdue {{ Str::plural('invoice', $stats['overdue_count']) }}</p>
        <p class="text-xs text-red-600 dark:text-red-500">Please arrange payment to avoid service interruption.</p>
    </div>
    <a href="{{ route('portal.invoices.index') }}" class="flex-shrink-0 rounded-md border border-red-300 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-red-700 dark:bg-transparent dark:text-red-400">View →</a>
</div>
@endif

@if ($stats['pending_estimates'] > 0)
<div class="mb-4 flex items-center gap-3 rounded-lg border border-l-4 border-brand-purple/25 border-l-brand-purple bg-brand-purple/5 p-3.5 dark:bg-brand-purple/10">
    <svg class="h-5 w-5 flex-shrink-0 text-brand-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-brand-purple">{{ $stats['pending_estimates'] }} {{ Str::plural('estimate', $stats['pending_estimates']) }} awaiting your approval</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">Review and accept or reject to proceed.</p>
    </div>
    <a href="{{ route('portal.estimates.index') }}" class="flex-shrink-0 rounded-md border border-brand-purple/30 bg-white px-3 py-1.5 text-xs font-semibold text-brand-purple hover:bg-brand-purple/5 dark:border-brand-purple/40 dark:bg-transparent">Review →</a>
</div>
@endif

@if ($stats['unread_documents'] > 0)
<div class="mb-4 flex items-center gap-3 rounded-lg border border-l-4 border-emerald-200 border-l-emerald-500 bg-emerald-50 p-3.5 dark:border-emerald-900/40 dark:border-l-emerald-500 dark:bg-emerald-900/10">
    <svg class="h-5 w-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" /></svg>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">{{ $stats['unread_documents'] }} new {{ Str::plural('document', $stats['unread_documents']) }} shared with you</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">Lumisk Technology shared new files for you.</p>
    </div>
    <a href="{{ route('portal.documents.index') }}" class="flex-shrink-0 rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:bg-transparent dark:text-emerald-400">View →</a>
</div>
@endif

{{-- Stats grid --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    {{-- Outstanding --}}
    <div class="card p-4 {{ $stats['outstanding'] > 0 ? 'border-t-2 border-t-red-500' : 'border-t-2 border-t-emerald-500' }}">
        <p class="text-xs text-gray-500 dark:text-gray-400">Outstanding</p>
        <p class="mt-1.5 text-lg font-bold {{ $stats['outstanding'] > 0 ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">
            {{ money($stats['outstanding']) }}
        </p>
        <p class="mt-0.5 text-[11px] text-gray-400">
            {{ $stats['outstanding'] > 0 ? 'Awaiting payment' : 'All paid up ✓' }}
        </p>
    </div>
    {{-- Total Invoiced --}}
    <div class="card border-t-2 border-t-brand-purple p-4">
        <p class="text-xs text-gray-500 dark:text-gray-400">Total Invoiced</p>
        <p class="mt-1.5 bg-gradient-to-r from-brand-cyan to-brand-purple bg-clip-text text-lg font-bold text-transparent">
            {{ money($stats['total_invoiced']) }}
        </p>
        <p class="mt-0.5 text-[11px] text-gray-400">All time</p>
    </div>
    {{-- Active Projects --}}
    <div class="card border-t-2 border-t-brand-cyan p-4">
        <p class="text-xs text-gray-500 dark:text-gray-400">Active Projects</p>
        <p class="mt-1.5 text-2xl font-bold text-brand-cyan">{{ $stats['active_projects'] }}</p>
        <p class="mt-0.5 text-[11px] text-gray-400">In progress</p>
    </div>
    {{-- Open Tickets --}}
    <div class="card p-4 {{ $stats['open_tickets'] > 0 ? 'border-t-2 border-t-amber-400' : '' }}">
        <p class="text-xs text-gray-500 dark:text-gray-400">Open Tickets</p>
        <p class="mt-1.5 text-2xl font-bold {{ $stats['open_tickets'] > 0 ? 'text-amber-500' : 'text-gray-400 dark:text-gray-500' }}">{{ $stats['open_tickets'] }}</p>
        <p class="mt-0.5 text-[11px] text-gray-400">{{ $stats['open_tickets'] > 0 ? 'Awaiting response' : 'No open issues ✓' }}</p>
    </div>
</div>

{{-- Quick actions --}}
<div class="card mb-6 p-4">
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Quick Actions</p>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <a href="{{ route('portal.invoices.index') }}"
           class="flex flex-col items-center gap-1.5 rounded-lg border border-brand-purple/20 bg-brand-purple/5 p-3 text-center transition hover:bg-brand-purple/10 dark:border-brand-purple/25 dark:bg-brand-purple/10 dark:hover:bg-brand-purple/20">
            <svg class="h-6 w-6 text-brand-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">My Invoices</span>
        </a>
        <a href="{{ route('portal.estimates.index') }}"
           class="flex flex-col items-center gap-1.5 rounded-lg border border-brand-cyan/20 bg-brand-cyan/5 p-3 text-center transition hover:bg-brand-cyan/10 dark:border-brand-cyan/25 dark:bg-brand-cyan/10 dark:hover:bg-brand-cyan/20">
            <svg class="h-6 w-6 text-brand-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Estimates</span>
        </a>
        @if (Route::has('portal.tickets.create'))
        <a href="{{ route('portal.tickets.create') }}"
           class="flex flex-col items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 p-3 text-center transition hover:bg-amber-100 dark:border-amber-800/30 dark:bg-amber-900/10 dark:hover:bg-amber-900/20">
            <svg class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">New Ticket</span>
        </a>
        @endif
        @if (Route::has('portal.documents.index'))
        <a href="{{ route('portal.documents.index') }}"
           class="relative flex flex-col items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-center transition hover:bg-emerald-100 dark:border-emerald-800/30 dark:bg-emerald-900/10 dark:hover:bg-emerald-900/20">
            @if ($stats['unread_documents'] > 0)
                <span class="absolute right-2 top-2 rounded-full bg-emerald-500 px-1.5 py-0.5 text-[9px] font-bold text-white">{{ $stats['unread_documents'] }}</span>
            @endif
            <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414A1 1 0 0120 8.414V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Documents</span>
        </a>
        @endif
    </div>
</div>

{{-- Main 2-col grid --}}
<div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-2">

    {{-- Recent Invoices --}}
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3.5 dark:border-ink-600">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Recent Invoices</span>
            <a href="{{ route('portal.invoices.index') }}" class="text-xs font-medium text-brand-purple hover:underline">View all →</a>
        </div>
        @forelse ($recentInvoices as $invoice)
            <a href="{{ route('portal.invoices.show', $invoice) }}"
               class="flex items-center gap-3 border-b border-gray-100 px-4 py-2.5 hover:bg-gray-50 dark:border-ink-700 dark:hover:bg-ink-800">
                <div class="min-w-0 flex-1">
                    <p class="font-mono text-xs font-semibold text-brand-purple">{{ $invoice->invoice_number }}</p>
                    <p class="text-[11px] text-gray-400">{{ $invoice->issue_date?->format('M d, Y') }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ currency_amount($invoice, $invoice->total) }}</p>
                    <x-status-badge :color="$invoice->statusColor()" :label="$invoice->status" class="mt-0.5" />
                </div>
            </a>
        @empty
            <p class="px-4 py-8 text-center text-sm text-gray-400">No invoices yet.</p>
        @endforelse
    </div>

    {{-- Right column: estimates + renewals + projects --}}
    <div class="flex flex-col gap-4">

        {{-- Pending estimates --}}
        @if ($pendingEstimates->count() > 0)
        <div class="card overflow-hidden border-t-2 border-t-brand-purple">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-ink-600">
                <span class="text-sm font-semibold text-brand-purple">⏳ Awaiting Your Review</span>
            </div>
            @foreach ($pendingEstimates as $estimate)
                <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-2.5 dark:border-ink-700">
                    <div class="min-w-0 flex-1">
                        <p class="font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ $estimate->estimate_number }}</p>
                        <p class="text-[11px] text-gray-400">Expires {{ $estimate->expiry_date?->format('M d') }}</p>
                    </div>
                    <p class="shrink-0 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ currency_amount($estimate, $estimate->total) }}</p>
                    <a href="{{ route('portal.estimates.show', $estimate) }}"
                       class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-semibold text-white"
                       style="background: var(--brand-gradient, linear-gradient(135deg,#00d4ff,#6d5cff));">
                        Review
                    </a>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Upcoming renewals --}}
        @if ($upcomingRenewals->count() > 0)
        <div class="card overflow-hidden border-t-2 border-t-amber-400">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-ink-600">
                <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">🔄 Upcoming Renewals</span>
            </div>
            @foreach ($upcomingRenewals as $sub)
                <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-2.5 dark:border-ink-700">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-gray-900 dark:text-white">{{ $sub->name }}</p>
                        <p class="text-[11px] text-gray-400">
                            {{ $sub->next_billing_date->format('M d, Y') }}
                            · in {{ today()->diffInDays($sub->next_billing_date) }}d
                        </p>
                    </div>
                    <p class="shrink-0 text-sm font-semibold text-amber-600 dark:text-amber-400">{{ money($sub->amount) }}</p>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Active projects --}}
        @if ($activeProjects->count() > 0)
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-ink-600">
                <span class="text-sm font-semibold text-gray-900 dark:text-white">Active Projects</span>
                @if (Route::has('portal.projects.index'))
                    <a href="{{ route('portal.projects.index') }}" class="text-xs font-medium text-brand-purple hover:underline">View all →</a>
                @endif
            </div>
            @foreach ($activeProjects as $project)
                @php $pct = $project->completion_percentage; @endphp
                <div class="border-b border-gray-100 px-4 py-3 dark:border-ink-700">
                    <div class="mb-1.5 flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $project->name }}</span>
                        <span class="text-xs font-semibold text-brand-purple">{{ $pct }}%</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-600">
                        <div class="h-1.5 rounded-full transition-all"
                             style="width:{{ $pct }}%; background: var(--brand-gradient, linear-gradient(90deg,#00d4ff,#6d5cff));"></div>
                    </div>
                    <p class="mt-1 text-[11px] text-gray-400">
                        {{ $project->tasks->where('status', 'done')->count() }}/{{ $project->tasks->count() }} tasks done
                    </p>
                </div>
            @endforeach
        </div>
        @endif

    </div>
</div>

{{-- Bottom 2-col grid --}}
<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

    {{-- Open Tickets --}}
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3.5 dark:border-ink-600">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Support Tickets</span>
            @if (Route::has('portal.tickets.index'))
                <a href="{{ route('portal.tickets.index') }}" class="text-xs font-medium text-brand-purple hover:underline">View all →</a>
            @endif
        </div>
        @forelse ($openTickets as $ticket)
            @php
                $dotHex = match($ticket->statusColor()) {
                    'red'   => '#ef4444',
                    'blue'  => '#3b82f6',
                    'amber' => '#f59e0b',
                    'green' => '#10b981',
                    default => '#94a3b8',
                };
            @endphp
            <a href="{{ route('portal.tickets.show', $ticket) }}"
               class="flex items-center gap-3 border-b border-gray-100 px-4 py-2.5 hover:bg-gray-50 dark:border-ink-700 dark:hover:bg-ink-800">
                <span class="h-2 w-2 flex-shrink-0 rounded-full" style="background:{{ $dotHex }};"></span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-medium text-gray-900 dark:text-white">{{ $ticket->subject }}</p>
                    <p class="text-[11px] text-gray-400">{{ $ticket->ticket_number }} · {{ $ticket->created_at->diffForHumans() }}</p>
                </div>
                <x-status-badge :color="$ticket->statusColor()" :label="$ticket->statusLabel()" class="shrink-0" />
            </a>
        @empty
            <div class="px-4 py-8 text-center">
                <svg class="mx-auto mb-2 h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="text-sm text-gray-400">No open tickets</p>
                @if (Route::has('portal.tickets.create'))
                    <a href="{{ route('portal.tickets.create') }}" class="mt-2 inline-block text-xs text-brand-purple hover:underline">Create a ticket →</a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Recent Activity --}}
    <div class="card overflow-hidden">
        <div class="border-b border-gray-200 px-4 py-3.5 dark:border-ink-600">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Recent Activity</span>
        </div>
        <div class="max-h-72 overflow-y-auto">
            @forelse ($activities as $activity)
                <div class="flex items-start gap-3 border-b border-gray-100 px-4 py-2.5 dark:border-ink-700">
                    <div class="mt-0.5 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg"
                         style="background: {{ $activity->color }}1a; color: {{ $activity->color }};">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $activity->icon_path }}" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-gray-800 dark:text-gray-200">{{ $activity->description }}</p>
                        <div class="mt-0.5 flex flex-wrap items-center gap-2">
                            <span class="text-[11px] text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                            @if ($activity->subject_label)
                                <span class="rounded bg-brand-purple/8 px-1.5 py-0.5 font-mono text-[10px] text-brand-purple dark:bg-brand-purple/15">{{ $activity->subject_label }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="px-4 py-8 text-center text-sm text-gray-400">No recent activity.</p>
            @endforelse
        </div>
    </div>
</div>

</div>
