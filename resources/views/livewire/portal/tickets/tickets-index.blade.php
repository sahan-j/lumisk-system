<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex gap-3 text-sm">
            <span class="rounded-lg bg-red-50 px-3 py-1.5 font-medium text-red-600 dark:bg-red-900/20 dark:text-red-400">{{ $openCount }} Open</span>
            <span class="rounded-lg bg-brand-purple/10 px-3 py-1.5 font-medium text-brand-purple">{{ $inProgressCount }} In Progress</span>
            <span class="rounded-lg bg-green-50 px-3 py-1.5 font-medium text-green-600 dark:bg-green-900/20 dark:text-green-400">{{ $resolvedCount }} Resolved</span>
        </div>
        <a href="{{ route('portal.tickets.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            New Ticket
        </a>
    </div>

    @if ($tickets->count())
        <div class="card divide-y divide-gray-100 dark:divide-ink-700">
            @foreach ($tickets as $ticket)
                <a href="{{ route('portal.tickets.show', $ticket) }}" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 dark:hover:bg-ink-800">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-semibold text-brand-purple">{{ $ticket->ticket_number }}</span>
                            <x-status-badge :color="$ticket->statusColor()" :label="$ticket->statusLabel()" />
                            <x-status-badge :color="$ticket->priorityColor()" :label="ucfirst($ticket->priority)" />
                        </div>
                        <p class="mt-1 truncate text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-400">{{ $ticket->typeLabel() }} · Updated {{ $ticket->updated_at->diffForHumans() }}</p>
                    </div>
                    <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>
            @endforeach
        </div>

        <div class="mt-4">{{ $tickets->links() }}</div>
    @else
        <div class="card p-12 text-center">
            <p class="text-sm text-gray-400">No tickets yet. Create your first support ticket.</p>
            <a href="{{ route('portal.tickets.create') }}" class="btn-primary mt-4 inline-flex">New Ticket</a>
        </div>
    @endif
</div>
