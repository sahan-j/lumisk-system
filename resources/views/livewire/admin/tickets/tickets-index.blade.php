<div>
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Support Tickets</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage client support requests and SLA.</p>
    </div>

    {{-- Stat cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-5">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Open</span>
            <p class="mt-2 text-2xl font-semibold text-red-600 dark:text-red-400">{{ $openCount }}</p>
        </div>
        <div class="card p-5">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">In Progress</span>
            <p class="mt-2 text-2xl font-semibold text-brand-purple">{{ $inProgressCount }}</p>
        </div>
        <div class="card p-5 {{ $slaOverdueCount > 0 ? 'ring-1 ring-red-300 dark:ring-red-700' : '' }}">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">SLA Overdue</span>
            <p class="mt-2 text-2xl font-semibold text-red-600 dark:text-red-400">{{ $slaOverdueCount }}</p>
        </div>
        <div class="card p-5">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Resolved Today</span>
            <p class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">{{ $resolvedTodayCount }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div class="relative lg:col-span-1">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search…" class="form-input-base pl-10">
        </div>
        <select wire:model.live="status" class="form-input-base">
            <option value="">All statuses</option>
            @foreach ($statuses as $s)<option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach
        </select>
        <select wire:model.live="priority" class="form-input-base">
            <option value="">All priorities</option>
            @foreach ($priorities as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
        </select>
        <select wire:model.live="type" class="form-input-base">
            <option value="">All types</option>
            @foreach ($types as $t)<option value="{{ $t }}">{{ ucfirst(str_replace('_', ' ', $t)) }}</option>@endforeach
        </select>
        <select wire:model.live="client" class="form-input-base">
            <option value="">All clients</option>
            @foreach ($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Ticket</th>
                        <th class="px-5 py-3">Subject</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Priority</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">SLA</th>
                        <th class="px-5 py-3">Created</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800 {{ $ticket->isSlaOverdue() ? 'bg-red-50/60 dark:bg-red-900/10' : '' }}">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="font-mono text-sm font-medium text-brand-purple hover:underline">{{ $ticket->ticket_number }}</a>
                            </td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-sm font-medium text-gray-900 hover:text-gold dark:text-white">{{ $ticket->subject }}</a>
                                <p class="text-xs text-gray-400">{{ $ticket->typeLabel() }}@if ($ticket->project) · {{ $ticket->project->name }}@endif</p>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $ticket->client?->name ?? '—' }}</td>
                            <td class="px-5 py-3"><x-status-badge :color="$ticket->priorityColor()" :label="ucfirst($ticket->priority)" /></td>
                            <td class="px-5 py-3"><x-status-badge :color="$ticket->statusColor()" :label="$ticket->statusLabel()" /></td>
                            <td class="px-5 py-3 text-xs">
                                @if (in_array($ticket->status, ['resolved', 'closed']))
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @elseif ($ticket->isSlaOverdue())
                                    <span class="font-semibold text-red-500">{{ $ticket->sla_due_at->diffForHumans(null, true) }} overdue</span>
                                @elseif ($ticket->sla_due_at)
                                    <span class="text-green-600 dark:text-green-400">{{ $ticket->sla_due_at->diffForHumans(null, true) }} left</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $ticket->created_at->diffForHumans() }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-sm font-medium text-brand-purple hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400">No tickets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $tickets->links() }}</div>
</div>
