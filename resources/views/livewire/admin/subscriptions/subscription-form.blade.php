<div>
    <a href="{{ route('admin.subscriptions.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Subscriptions
    </a>

    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">{{ $subscription ? 'Edit Subscription' : 'New Subscription' }}</h2>

    <form wire:submit="save" class="space-y-6">
        {{-- Plan selection --}}
        @if ($plans->isNotEmpty() && ! $subscription)
            <div class="card p-6">
                <h3 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Choose a Plan</h3>
                <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">Selecting a plan auto-fills the fields below. You can still adjust them.</p>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($plans as $plan)
                        <button type="button" wire:click="selectPlan({{ $plan->id }})"
                                class="rounded-lg border-2 p-4 text-left transition {{ $plan_id === $plan->id ? 'border-brand-purple bg-brand-purple/5' : 'border-gray-200 hover:border-gray-300 dark:border-ink-600 dark:hover:border-ink-500' }}">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $plan->name }}</div>
                            <div class="my-1 text-lg font-bold text-brand-purple">{{ money($plan->amount) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">per {{ $plan->billing_cycle_label }}@if ($plan->trial_days > 0) · {{ $plan->trial_days }}d trial @endif</div>
                        </button>
                    @endforeach
                    <button type="button" wire:click="clearPlan"
                            class="rounded-lg border-2 border-dashed p-4 text-left transition {{ ! $plan_id ? 'border-brand-purple bg-brand-purple/5' : 'border-gray-200 hover:border-gray-300 dark:border-ink-600' }}">
                        <div class="font-medium text-gray-700 dark:text-gray-200">Custom / No plan</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter details manually</div>
                    </button>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Left: details --}}
            <div class="space-y-6 lg:col-span-2">
                <div class="card p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="form-label">Client <span class="text-red-500">*</span></label>
                            <select wire:model="client_id" class="form-input-base">
                                <option value="">Select a client…</option>
                                @foreach ($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}@if ($c->company_name) — {{ $c->company_name }}@endif</option>@endforeach
                            </select>
                            @error('client_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Subscription Name <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" class="form-input-base" placeholder="e.g. Website Maintenance Retainer">
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Description</label>
                            <textarea wire:model="description" rows="2" class="form-input-base"></textarea>
                        </div>
                        <div>
                            <label class="form-label">Amount <span class="text-red-500">*</span></label>
                            <input wire:model="amount" type="number" step="0.01" min="0" class="form-input-base">
                            @error('amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Billing Cycle <span class="text-red-500">*</span></label>
                            <select wire:model="billing_cycle" class="form-input-base">
                                @foreach ($cycles as $c)<option value="{{ $c }}">{{ ucwords(str_replace('_', ' ', $c)) }}</option>@endforeach
                            </select>
                            @error('billing_cycle') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Start Date <span class="text-red-500">*</span></label>
                            <input wire:model="start_date" type="date" class="form-input-base">
                            @error('start_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">End Date <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                            <input wire:model="end_date" type="date" class="form-input-base">
                            @error('end_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        @if ($subscription)
                            <div>
                                <label class="form-label">Status</label>
                                <select wire:model="status" class="form-input-base">
                                    @foreach ($statuses as $s)<option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Next Billing Date</label>
                                <input wire:model="next_billing_date" type="date" class="form-input-base">
                            </div>
                        @else
                            <div>
                                <label class="form-label">Trial Days <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                                <input wire:model="trial_days" type="number" min="0" class="form-input-base" placeholder="0">
                                @error('trial_days') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: options --}}
            <div class="space-y-6">
                <div class="card p-6">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Automation</h3>
                    <label class="flex cursor-pointer items-start gap-3 py-2">
                        <input wire:model="auto_invoice" type="checkbox" class="mt-0.5 rounded border-gray-300 text-gold focus:ring-gold dark:border-ink-600 dark:bg-ink-800">
                        <span>
                            <span class="block text-sm font-medium text-gray-700 dark:text-gray-200">Auto-generate invoice</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Create an invoice automatically on each billing date.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 py-2">
                        <input wire:model="auto_send_invoice" type="checkbox" class="mt-0.5 rounded border-gray-300 text-gold focus:ring-gold dark:border-ink-600 dark:bg-ink-800">
                        <span>
                            <span class="block text-sm font-medium text-gray-700 dark:text-gray-200">Auto-send invoice</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Email the invoice to the client when generated.</span>
                        </span>
                    </label>
                </div>
                <div class="card p-6">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" rows="4" class="form-input-base" placeholder="Internal notes…"></textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.subscriptions.index') }}" wire:navigate class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <span wire:loading.remove wire:target="save">{{ $subscription ? 'Update' : 'Create' }} Subscription</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>
