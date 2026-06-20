<div>
    <a href="{{ route('admin.subscriptions.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Subscriptions
    </a>

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="font-mono text-xl font-semibold text-brand-purple">{{ $subscription->subscription_number }}</h2>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white" style="background-color: {{ $subscription->status_color }}">{{ $subscription->status_label }}</span>
                <x-status-badge color="gray" :label="$subscription->billing_cycle_label" />
            </div>
            <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $subscription->name }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('admin.clients.show', $subscription->client) }}" class="hover:text-gold">{{ $subscription->client->name }}</a>
            </p>
        </div>
        @permission('subscriptions.edit')
        <a href="{{ route('admin.subscriptions.edit', $subscription) }}" wire:navigate class="btn-secondary self-start">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            Edit
        </a>
        @endpermission
    </div>

    {{-- Quick actions --}}
    @permission('subscriptions.edit')
    <div class="mb-6 flex flex-wrap items-center gap-2">
        @if (in_array($subscription->status, ['active', 'trial', 'past_due']))
            <button wire:click="generateInvoiceNow" wire:loading.attr="disabled" class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Generate Invoice Now
            </button>
        @endif
        @if ($subscription->status === 'active')
            <button wire:click="pause" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Pause
            </button>
        @endif
        @if ($subscription->status === 'paused')
            <button wire:click="resume" class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Resume
            </button>
        @endif
        @if (! in_array($subscription->status, ['cancelled', 'expired']))
            <button wire:click="$set('confirmingCancel', true)" class="btn-danger">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                Cancel
            </button>
        @endif
    </div>
    @endpermission

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Timeline / details --}}
            <div class="card p-6">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Billing Details</h3>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Start Date</dt>
                        <dd class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ $subscription->start_date?->format('M d, Y') }}</dd>
                    </div>
                    @if ($subscription->trial_end_date)
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Trial Ends</dt>
                            <dd class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ $subscription->trial_end_date->format('M d, Y') }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Next Billing</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-brand-purple">{{ $subscription->next_billing_date?->format('M d, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Last Billed</dt>
                        <dd class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ $subscription->last_billed_date?->format('M d, Y') ?? 'Never' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">End Date</dt>
                        <dd class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ $subscription->end_date?->format('M d, Y') ?? 'Ongoing' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Total Billed</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white">{{ money($subscription->total_billed) }}</dd>
                    </div>
                </dl>
                @if ($subscription->status === 'cancelled' && $subscription->cancellation_reason)
                    <div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-300">
                        <span class="font-medium">Cancellation reason:</span> {{ $subscription->cancellation_reason }}
                    </div>
                @endif
            </div>

            {{-- Linked invoices --}}
            <div class="card overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-ink-600">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Generated Invoices</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                        <thead>
                            <tr class="table-head">
                                <th class="px-5 py-3">Invoice</th>
                                <th class="px-5 py-3">Period</th>
                                <th class="px-5 py-3 text-right">Amount</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">PDF</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                            @forelse ($invoices as $inv)
                                <tr wire:key="inv-{{ $inv->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('admin.invoices.show', $inv) }}" wire:navigate class="font-mono text-sm font-medium text-brand-purple hover:underline">{{ $inv->invoice_number }}</a>
                                        <p class="text-xs text-gray-400">{{ $inv->issue_date?->format('M d, Y') }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Illuminate\Support\Carbon::parse($inv->pivot->billing_period_start)->format('M d') }} –
                                        {{ \Illuminate\Support\Carbon::parse($inv->pivot->billing_period_end)->format('M d, Y') }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm font-medium text-gray-900 dark:text-white">{{ money($inv->total) }}</td>
                                    <td class="px-5 py-3"><x-status-badge :color="$inv->statusColor()" :label="ucfirst($inv->status)" /></td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('admin.invoices.pdf', $inv) }}" class="text-gray-400 hover:text-gold" title="Download PDF">
                                            <svg class="ml-auto h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">No invoices generated yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right --}}
        <div class="space-y-6">
            <div class="card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Subscription</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Plan</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $subscription->plan?->name ?? 'Custom' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Amount</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ money($subscription->amount) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Yearly Value</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ money($subscription->yearly_value) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Auto Invoice</dt>
                        <dd class="font-medium {{ $subscription->auto_invoice ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">{{ $subscription->auto_invoice ? 'On' : 'Off' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Auto Send</dt>
                        <dd class="font-medium {{ $subscription->auto_send_invoice ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">{{ $subscription->auto_send_invoice ? 'On' : 'Off' }}</dd>
                    </div>
                </dl>
                @if ($subscription->notes)
                    <div class="mt-4 border-t border-gray-100 pt-4 dark:border-ink-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Notes</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $subscription->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="card p-6">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Client</h3>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $subscription->client->name }}</p>
                @if ($subscription->client->email)<p class="text-sm text-gray-500 dark:text-gray-400">{{ $subscription->client->email }}</p>@endif
                @if ($subscription->client->phone)<p class="text-sm text-gray-500 dark:text-gray-400">{{ $subscription->client->phone }}</p>@endif
                <a href="{{ route('admin.clients.show', $subscription->client) }}" class="mt-2 inline-block text-sm text-brand-purple hover:underline">View client →</a>
            </div>

            @if (! empty($forecast))
                <div class="card p-6">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Billing Forecast</h3>
                    <ul class="space-y-2">
                        @foreach ($forecast as $date)
                            <li class="flex items-center justify-between border-b border-gray-100 pb-2 text-sm last:border-0 dark:border-ink-700">
                                <span class="text-gray-600 dark:text-gray-300">{{ $date->format('M d, Y') }}</span>
                                <span class="font-mono text-gray-900 dark:text-white">{{ money($subscription->amount) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    @if ($confirmingCancel)
        <x-app-modal title="Cancel subscription?" close="$set('confirmingCancel', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">This stops all future billing for {{ $subscription->subscription_number }}. Existing invoices are kept.</p>
            <div class="mt-4">
                <label class="form-label">Reason <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                <textarea wire:model="cancellationReason" rows="2" class="form-input-base" placeholder="Reason for cancellation…"></textarea>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingCancel', false)" class="btn-secondary">Keep Subscription</button>
                <button wire:click="cancel" class="btn-danger">Confirm Cancel</button>
            </div>
        </x-app-modal>
    @endif
</div>
