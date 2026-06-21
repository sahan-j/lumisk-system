<div>
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Audit Log</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Complete record of all data changes in the system.</p>
    </div>

    {{-- Filters --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <select wire:model.live="event" class="form-input-base">
            <option value="">All events</option>
            @foreach ($events as $e)
                <option value="{{ $e }}">{{ ucfirst($e) }}</option>
            @endforeach
        </select>
        <select wire:model.live="user" class="form-input-base">
            <option value="">All users</option>
            @foreach ($users as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="model" class="form-input-base">
            <option value="">All records</option>
            @foreach ($models as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <input wire:model.live="from" type="date" class="form-input-base" title="From date">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search record…" class="form-input-base pl-10">
        </div>
    </div>

    @if ($event || $user || $model || $from || $search)
        <div class="mb-3">
            <button wire:click="clearFilters" class="text-sm font-medium text-brand-purple hover:underline">Clear filters</button>
        </div>
    @endif

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Time</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Record</th>
                        <th class="px-5 py-3">Changes</th>
                        <th class="px-5 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($logs as $log)
                        <tr wire:key="audit-{{ $log->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="whitespace-nowrap px-5 py-3">
                                <div class="text-xs text-gray-900 dark:text-white">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="text-[11px] text-gray-400">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gradient-brand text-[10px] font-semibold text-white">
                                        {{ strtoupper(substr($log->user_name ?? 'S', 0, 1)) }}
                                    </span>
                                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ $log->user_name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-medium"
                                      style="background-color: {{ $log->event_color }}1a; color: {{ $log->event_color }};">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $log->event_icon }}" /></svg>
                                    {{ ucfirst($log->event) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="text-xs font-medium text-gray-900 dark:text-white">{{ class_basename($log->auditable_type) }}</div>
                                @if ($log->auditable_label)
                                    <div class="font-mono text-[11px] text-brand-purple">{{ $log->auditable_label }}</div>
                                @endif
                            </td>
                            <td class="max-w-xs px-5 py-3">
                                @if ($log->event === 'updated' && $log->new_values)
                                    @php $changes = array_keys($log->new_values); @endphp
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                        Changed: {{ implode(', ', array_slice($changes, 0, 3)) }}@if (count($changes) > 3) +{{ count($changes) - 3 }} more @endif
                                    </div>
                                @elseif ($log->event === 'created')
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">New record created</div>
                                @elseif ($log->event === 'deleted')
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Record deleted</div>
                                @elseif ($log->event === 'restored')
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Record restored</div>
                                @endif
                                <a href="{{ route('admin.audit-log.show', $log) }}" wire:navigate class="text-[11px] font-medium text-brand-purple hover:underline">View details →</a>
                            </td>
                            <td class="px-5 py-3 text-[11px] text-gray-400">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">No audit entries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</div>
