<div>
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">My Subscriptions</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Your active services and recurring plans.</p>
    </div>

    @forelse ($subscriptions as $sub)
        <a href="{{ route('portal.subscriptions.show', $sub) }}" wire:key="psub-{{ $sub->id }}"
           class="card mb-4 block p-5 transition hover:shadow-md">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $sub->name }}</span>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white" style="background-color: {{ $sub->status_color }}">{{ $sub->status_label }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $sub->subscription_number }} · {{ $sub->billing_cycle_label }}</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ money($sub->amount) }}</p>
                    @if (in_array($sub->status, ['active', 'trial']) && $sub->next_billing_date)
                        <p class="text-xs text-gray-500 dark:text-gray-400">Next: {{ $sub->next_billing_date->format('M d, Y') }}</p>
                    @endif
                </div>
            </div>
        </a>
    @empty
        <div class="card p-12 text-center text-sm text-gray-400">You have no subscriptions.</div>
    @endforelse
</div>
