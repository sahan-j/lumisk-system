<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Subscriptions</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Recurring service plans and retainer billing.</p>
        </div>
        <div class="flex items-center gap-2">
            @permission('subscriptions.manage_plans')
            <a href="{{ route('admin.subscription-plans.index') }}" wire:navigate class="btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                Manage Plans
            </a>
            @endpermission
            @permission('subscriptions.create')
            <a href="{{ route('admin.subscriptions.create') }}" wire:navigate class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                New Subscription
            </a>
            @endpermission
        </div>
    </div>

    {{-- MRR / ARR stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card border-t-[3px] border-t-green-500 p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Active</span>
            <p class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">{{ $stats['active'] }}</p>
            <span class="text-xs text-gray-400">subscriptions</span>
        </div>
        <div class="card border-t-[3px] border-t-brand-purple p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">MRR</span>
            <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ money($stats['mrr']) }}</p>
            <span class="text-xs text-gray-400">monthly recurring</span>
        </div>
        <div class="card border-t-[3px] border-t-cyan-400 p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">ARR</span>
            <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ money($stats['arr']) }}</p>
            <span class="text-xs text-gray-400">annual recurring</span>
        </div>
        <div class="card border-t-[3px] border-t-red-500 p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Past Due</span>
            <p class="mt-1 text-2xl font-semibold {{ $stats['past_due'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">{{ $stats['past_due'] }}</p>
            <span class="text-xs text-gray-400">need attention</span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search number, name or client…" class="form-input-base pl-10">
        </div>
        <select wire:model.live="status" class="form-input-base">
            <option value="">All statuses</option>
            @foreach ($statuses as $s)<option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>@endforeach
        </select>
        <select wire:model.live="cycle" class="form-input-base">
            <option value="">All cycles</option>
            @foreach ($cycles as $c)<option value="{{ $c }}">{{ ucwords(str_replace('_', ' ', $c)) }}</option>@endforeach
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
                        <th class="px-5 py-3">#</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Plan</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3">Cycle</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Next Billing</th>
                        <th class="px-5 py-3 text-center">Auto</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($subscriptions as $sub)
                        <tr wire:key="sub-{{ $sub->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.subscriptions.show', $sub) }}" wire:navigate class="font-mono text-sm font-medium text-brand-purple hover:underline">{{ $sub->subscription_number }}</a>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                <a href="{{ route('admin.clients.show', $sub->client) }}" class="hover:text-gold">{{ $sub->client->name }}</a>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $sub->name }}</td>
                            <td class="px-5 py-3 text-right font-mono text-sm font-medium text-gray-900 dark:text-white">{{ money($sub->amount) }}</td>
                            <td class="px-5 py-3"><x-status-badge color="gray" :label="ucwords(str_replace('_', ' ', $sub->billing_cycle))" /></td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white" style="background-color: {{ $sub->status_color }}">{{ $sub->status_label }}</span>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap text-sm">
                                <span class="text-gray-700 dark:text-gray-300">{{ $sub->next_billing_date?->format('M d, Y') }}</span>
                                @if (in_array($sub->status, ['active', 'trial']))
                                    @if ($sub->days_until_next_billing < 0)
                                        <span class="block text-xs text-red-500">{{ abs($sub->days_until_next_billing) }}d overdue</span>
                                    @elseif ($sub->days_until_next_billing === 0)
                                        <span class="block text-xs text-amber-500">due today</span>
                                    @else
                                        <span class="block text-xs text-green-500">in {{ $sub->days_until_next_billing }}d</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if ($sub->auto_invoice)
                                    <svg class="mx-auto h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.subscriptions.show', $sub) }}" wire:navigate class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="View">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </a>
                                    @permission('subscriptions.edit')
                                    <a href="{{ route('admin.subscriptions.edit', $sub) }}" wire:navigate class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                    @endpermission
                                    @permission('subscriptions.delete')
                                    <button wire:click="confirmDelete({{ $sub->id }})" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-5 py-12 text-center text-sm text-gray-400">
                            No subscriptions yet.
                            @permission('subscriptions.create')
                                <a href="{{ route('admin.subscriptions.create') }}" wire:navigate class="ml-1 text-brand-purple hover:underline">Create your first subscription →</a>
                            @endpermission
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $subscriptions->links() }}</div>

    @if ($confirmingDelete)
        <x-app-modal title="Delete subscription?" close="$set('confirmingDelete', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">This soft-deletes the subscription. Generated invoices are kept. This cannot be undone from the UI.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </x-app-modal>
    @endif
</div>
