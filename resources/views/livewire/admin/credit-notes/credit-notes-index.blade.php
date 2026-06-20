<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Credit Notes</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Refunds and credits issued against invoices.</p>
        </div>
        @permission('credit-notes.create')
        <a href="{{ route('admin.credit-notes.create') }}" wire:navigate class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            New Credit Note
        </a>
        @endpermission
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card border-t-[3px] border-t-red-500 p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Issued</span>
            <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ money($stats['total_issued']) }}</p>
        </div>
        <div class="card border-t-[3px] border-t-green-500 p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Applied</span>
            <p class="mt-1 text-xl font-semibold text-green-600 dark:text-green-400">{{ money($stats['total_applied']) }}</p>
        </div>
        <div class="card border-t-[3px] border-t-brand-purple p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Remaining Balance</span>
            <p class="mt-1 text-xl font-semibold text-brand-purple">{{ money($stats['total_remaining']) }}</p>
        </div>
        <div class="card border-t-[3px] border-t-cyan-400 p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">This Month</span>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['count_this_month'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search number, reason or client…" class="form-input-base pl-10">
        </div>
        <select wire:model.live="status" class="form-input-base">
            <option value="">All statuses</option>
            @foreach ($statuses as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach
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
                        <th class="px-5 py-3">CN #</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Invoice</th>
                        <th class="px-5 py-3">Reason</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-right">Applied</th>
                        <th class="px-5 py-3 text-right">Remaining</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($creditNotes as $cn)
                        <tr wire:key="cn-{{ $cn->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.credit-notes.show', $cn) }}" wire:navigate class="font-mono text-sm font-medium text-red-500 hover:underline">{{ $cn->credit_note_number }}</a>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                <a href="{{ route('admin.clients.show', $cn->client) }}" class="hover:text-gold">{{ $cn->client->name }}</a>
                            </td>
                            <td class="px-5 py-3 text-sm">
                                @if ($cn->invoice)
                                    <a href="{{ route('admin.invoices.show', $cn->invoice) }}" class="font-mono text-brand-purple hover:underline">{{ $cn->invoice->invoice_number }}</a>
                                @else
                                    <span class="text-xs text-gray-400">Standalone</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400"><span class="block max-w-[180px] truncate" title="{{ $cn->reason }}">{{ $cn->reason }}</span></td>
                            <td class="px-5 py-3 text-right font-mono text-sm font-medium text-gray-900 dark:text-white">{{ money($cn->total) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-sm text-green-600 dark:text-green-400">{{ money($cn->amount_applied) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-sm {{ $cn->amount_remaining > 0 ? 'text-brand-purple' : 'text-gray-400' }}">{{ money($cn->amount_remaining) }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white" style="background-color: {{ $cn->status_color }}">{{ $cn->status_label }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.credit-notes.show', $cn) }}" wire:navigate class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="View">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </a>
                                    <a href="{{ route('admin.credit-notes.pdf', $cn) }}" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Download PDF">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    </a>
                                    @permission('credit-notes.edit')
                                    @if ($cn->amount_applied <= 0 && $cn->status !== 'void')
                                        <button wire:click="confirmVoid({{ $cn->id }})" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Void">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        </button>
                                    @endif
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-5 py-12 text-center text-sm text-gray-400">
                            No credit notes yet.
                            @permission('credit-notes.create')
                                <a href="{{ route('admin.credit-notes.create') }}" wire:navigate class="ml-1 text-brand-purple hover:underline">Create your first credit note →</a>
                            @endpermission
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $creditNotes->links() }}</div>

    @if ($confirmingVoid)
        <x-app-modal title="Void credit note?" close="$set('confirmingVoid', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">Voiding marks the credit note as cancelled. This can't be undone from the UI.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingVoid', false)" class="btn-secondary">Cancel</button>
                <button wire:click="voidCreditNote" class="btn-danger">Void</button>
            </div>
        </x-app-modal>
    @endif
</div>
