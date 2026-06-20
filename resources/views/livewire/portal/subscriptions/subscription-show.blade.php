<div>
    <a href="{{ route('portal.subscriptions.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-purple dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        My Subscriptions
    </a>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $subscription->name }}</h2>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white" style="background-color: {{ $subscription->status_color }}">{{ $subscription->status_label }}</span>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subscription->subscription_number }}</p>
        </div>
        @if (! in_array($subscription->status, ['cancelled', 'expired']))
            <button wire:click="$set('requestingCancel', true)" class="btn-secondary self-start">Request Cancellation</button>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card p-6 lg:col-span-2">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Details</h3>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Amount</dt>
                    <dd class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ money($subscription->amount) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Billing Cycle</dt>
                    <dd class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ $subscription->billing_cycle_label }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Start Date</dt>
                    <dd class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ $subscription->start_date?->format('M d, Y') }}</dd>
                </div>
                @if (in_array($subscription->status, ['active', 'trial']) && $subscription->next_billing_date)
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Next Billing</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-brand-purple">{{ $subscription->next_billing_date->format('M d, Y') }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Total Billed</dt>
                    <dd class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white">{{ money($subscription->total_billed) }}</dd>
                </div>
            </dl>
            @if ($subscription->description)
                <div class="mt-4 border-t border-gray-100 pt-4 dark:border-ink-700">
                    <p class="whitespace-pre-line text-sm text-gray-600 dark:text-gray-300">{{ $subscription->description }}</p>
                </div>
            @endif
        </div>

        <div class="card overflow-hidden lg:col-span-3">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-ink-600">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Billing History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                    <thead>
                        <tr class="table-head">
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                        @forelse ($invoices as $inv)
                            <tr wire:key="pinv-{{ $inv->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                                <td class="px-5 py-3"><a href="{{ route('portal.invoices.show', $inv) }}" class="font-medium text-gray-900 hover:text-brand-purple dark:text-white">{{ $inv->invoice_number }}</a></td>
                                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $inv->issue_date?->format('M d, Y') }}</td>
                                <td class="px-5 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ money($inv->total) }}</td>
                                <td class="px-5 py-3"><x-status-badge :color="$inv->statusColor()" :label="$inv->status" /></td>
                                <td class="px-5 py-3 text-right"><a href="{{ route('portal.invoices.show', $inv) }}" class="text-sm font-medium text-brand-purple hover:underline">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">No invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($requestingCancel)
        <x-app-modal title="Request cancellation?" close="$set('requestingCancel', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">We'll receive your request and get in touch to confirm. Your subscription stays active until then.</p>
            <div class="mt-4">
                <label class="form-label">Reason <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                <textarea wire:model="cancelReason" rows="2" class="form-input-base" placeholder="Let us know why…"></textarea>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('requestingCancel', false)" class="btn-secondary">Never mind</button>
                <button wire:click="cancelRequest" class="btn-primary">Send Request</button>
            </div>
        </x-app-modal>
    @endif
</div>
