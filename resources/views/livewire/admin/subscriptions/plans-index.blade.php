<div>
    <a href="{{ route('admin.subscriptions.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Subscriptions
    </a>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Subscription Plans</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Reusable templates for new subscriptions.</p>
        </div>
        <button wire:click="openCreate" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            New Plan
        </button>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3">Cycle</th>
                        <th class="px-5 py-3 text-center">Active Subs</th>
                        <th class="px-5 py-3 text-right">Monthly Value</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($plans as $plan)
                        <tr wire:key="plan-{{ $plan->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $plan->name }}</p>
                                @if ($plan->description)<p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($plan->description, 60) }}</p>@endif
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-sm font-medium text-gray-900 dark:text-white">{{ money($plan->amount) }}</td>
                            <td class="px-5 py-3"><x-status-badge color="gray" :label="$plan->billing_cycle_label" /></td>
                            <td class="px-5 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $plan->active_count }}</td>
                            <td class="px-5 py-3 text-right font-mono text-sm text-gray-700 dark:text-gray-300">{{ money($plan->monthly_value) }}</td>
                            <td class="px-5 py-3">
                                @if ($plan->is_active)
                                    <x-status-badge color="green" label="Active" />
                                @else
                                    <x-status-badge color="gray" label="Inactive" />
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openEdit({{ $plan->id }})" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $plan->id }})" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">No plans yet. Create one to speed up new subscriptions.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create / edit modal --}}
    @if ($showForm)
        <x-app-modal :title="$editingId ? 'Edit Plan' : 'New Plan'" close="$set('showForm', false)">
            <div class="space-y-4">
                <div>
                    <label class="form-label">Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="form-input-base">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea wire:model="description" rows="2" class="form-input-base"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
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
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Trial Days</label>
                        <input wire:model="trial_days" type="number" min="0" class="form-input-base">
                    </div>
                    <div class="flex items-end">
                        <label class="flex cursor-pointer items-center gap-3 pb-2">
                            <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-gold focus:ring-gold dark:border-ink-600 dark:bg-ink-800">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Active</span>
                        </label>
                    </div>
                </div>

                {{-- Features --}}
                <div>
                    <label class="form-label">Features</label>
                    <div class="flex gap-2">
                        <input wire:model="newFeature" wire:keydown.enter.prevent="addFeature" type="text" class="form-input-base" placeholder="Add a feature…">
                        <button type="button" wire:click="addFeature" class="btn-secondary shrink-0">Add</button>
                    </div>
                    @if (! empty($features))
                        <ul class="mt-2 space-y-1">
                            @foreach ($features as $i => $feature)
                                <li wire:key="feat-{{ $i }}" class="flex items-center justify-between rounded bg-gray-50 px-3 py-1.5 text-sm dark:bg-ink-800">
                                    <span class="text-gray-700 dark:text-gray-200">{{ $feature }}</span>
                                    <button type="button" wire:click="removeFeature({{ $i }})" class="text-gray-400 hover:text-red-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                <button wire:click="save" class="btn-primary">{{ $editingId ? 'Update' : 'Create' }} Plan</button>
            </div>
        </x-app-modal>
    @endif

    @if ($confirmingDelete)
        <x-app-modal title="Delete plan?" close="$set('confirmingDelete', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">Existing subscriptions keep their settings; only the template is removed.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </x-app-modal>
    @endif
</div>
