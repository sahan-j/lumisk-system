<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Estimates</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Create and manage estimates.</p>
        </div>
        @permission('estimates.create')
        <a href="{{ route('admin.estimates.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            New Estimate
        </a>
        @endpermission
    </div>

    {{-- Filters --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search…" class="form-input-base pl-10">
        </div>
        <select wire:model.live="status" class="form-input-base">
            <option value="">All statuses</option>
            @foreach ($statuses as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach
        </select>
        <input wire:model.live="from" type="date" class="form-input-base" title="From">
        <input wire:model.live="to" type="date" class="form-input-base" title="To">
    </div>

    {{-- Bulk action bar --}}
    @if (count($selected) > 0)
        <div class="mb-3 flex flex-wrap items-center gap-2 rounded-lg border border-brand-purple/50 bg-brand-purple/5 px-4 py-2.5">
            <span class="text-sm font-medium text-brand-purple">{{ count($selected) }} selected</span>
            <div class="flex-1"></div>
            <button wire:click="bulkMarkSent" class="btn-secondary !px-3 !py-1 text-xs">Mark Sent</button>
            <button wire:click="bulkExportCsv" class="btn-secondary !px-3 !py-1 text-xs">Export CSV</button>
            <button wire:click="bulkDelete" wire:confirm="Delete {{ count($selected) }} items?" class="btn-danger !px-3 !py-1 text-xs">Delete</button>
            <button wire:click="$set('selected', [])" class="px-2 py-1 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400">Clear</button>
        </div>
    @endif

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3"><input type="checkbox" wire:model.live="selectAll" class="rounded border-white/40 bg-white/20 text-brand-purple focus:ring-brand-purple"></th>
                        <th class="px-5 py-3">Estimate #</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Issue Date</th>
                        <th class="px-5 py-3">Expiry Date</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($estimates as $estimate)
                        <tr wire:key="estimate-{{ $estimate->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <input type="checkbox" wire:model.live="selected" value="{{ $estimate->id }}" class="rounded border-gray-300 text-brand-purple focus:ring-brand-purple dark:border-ink-600 dark:bg-ink-800">
                            </td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.estimates.show', $estimate) }}" class="font-medium text-gray-900 hover:text-brand-purple dark:text-white">{{ $estimate->estimate_number }}</a>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $estimate->client?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $estimate->issue_date?->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $estimate->expiry_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                {{ currency_amount($estimate, $estimate->total) }}
                                @if ($estimate->currency_code !== 'LKR')<div class="text-[10px] font-normal text-gray-400">≈ {{ money($estimate->total_lkr) }}</div>@endif
                            </td>
                            <td class="px-5 py-3"><x-status-badge :color="$estimate->statusColor()" :label="$estimate->status" /></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.estimates.edit', $estimate) }}" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                    <a href="{{ route('admin.estimates.pdf', $estimate) }}" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Download PDF">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    </a>
                                    <button wire:click="$dispatch('open-duplicate', { type: 'estimate', id: {{ $estimate->id }} })" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Duplicate">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                    </button>
                                    <button wire:click="$dispatch('open-convert', { direction: 'estimate_to_invoice', id: {{ $estimate->id }} })" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Convert to Invoice">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m4 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $estimate->id }})" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400">No estimates found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $estimates->links() }}</div>

    @if ($confirmingDelete)
        <x-app-modal title="Delete estimate?" close="$set('confirmingDelete', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">This will soft-delete the estimate. This action can be reversed from the database.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </x-app-modal>
    @endif
</div>
