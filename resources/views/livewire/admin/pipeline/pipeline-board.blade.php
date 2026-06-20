<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Sales Pipeline</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track leads from first contact to closed deal.</p>
        </div>
        <div class="flex items-center gap-2">
            @permission('pipeline.manage_stages')
            <button wire:click="openStages" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" /></svg>
                Manage Stages
            </button>
            @endpermission
            @permission('leads.create')
            <a href="{{ route('admin.leads.create') }}" wire:navigate class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Add Lead
            </a>
            @endpermission
        </div>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card border-t-[3px] border-t-brand-purple p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Active Leads</span>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_leads'] }}</p>
            <span class="text-xs text-gray-400">in pipeline</span>
        </div>
        <div class="card border-t-[3px] border-t-cyan-400 p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Pipeline Value</span>
            <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ money($stats['total_value']) }}</p>
            <span class="text-xs text-gray-400">total potential</span>
        </div>
        <div class="card border-t-[3px] border-t-gold p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Weighted Value</span>
            <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ money($stats['weighted_value']) }}</p>
            <span class="text-xs text-gray-400">probability-adjusted</span>
        </div>
        <div class="card border-t-[3px] border-t-green-500 p-5">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Won This Month</span>
            <p class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">{{ $stats['won_this_month'] }}</p>
            <span class="text-xs text-gray-400">{{ money($stats['won_value_this_month']) }}</span>
        </div>
    </div>

    {{-- Kanban board --}}
    <div class="flex gap-3 overflow-x-auto pb-4" style="min-height:520px;">
        @foreach ($stages as $stage)
            <div wire:key="stage-col-{{ $stage->id }}"
                 class="flex w-72 flex-shrink-0 flex-col rounded-xl bg-gray-50 dark:bg-ink-800"
                 style="border-top:3px solid {{ $stage->color }};">

                {{-- Column header (reactive: counts/values refresh after a move) --}}
                <div class="border-b border-gray-200 px-4 py-3 dark:border-ink-600">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $stage->name }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                              style="background-color: {{ $stage->color }}1a; color: {{ $stage->color }};">
                            {{ $stage->leads->count() }}
                        </span>
                    </div>
                    <div class="mt-0.5 text-[11px] text-gray-400">{{ money($stage->leads->sum('value')) }}</div>
                </div>

                {{-- Cards: SortableJS-managed, so kept out of Livewire's DOM morphing --}}
                <div wire:ignore
                     data-kanban-cards
                     data-stage-id="{{ $stage->id }}"
                     class="flex min-h-[400px] flex-1 flex-col gap-2 p-2.5">
                    @foreach ($stage->leads as $lead)
                        <div data-lead-id="{{ $lead->id }}"
                             class="kanban-card group cursor-grab rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition hover:shadow-md dark:border-ink-600 dark:bg-ink-850"
                             onclick="if(!window._kanbanDragged){window.location='{{ route('admin.leads.show', $lead) }}'}">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $lead->name }}</div>
                            @if ($lead->company_name)
                                <div class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">{{ $lead->company_name }}</div>
                            @endif
                            @if ($lead->value)
                                <div class="mt-2 font-mono text-sm font-bold text-brand-purple">{{ money($lead->value) }}</div>
                            @endif
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-[10px] text-gray-400">{{ $lead->source_label }}</span>
                                @if ($lead->probability)
                                    <span class="rounded-full bg-brand-purple/10 px-1.5 py-0.5 text-[10px] font-medium text-brand-purple">{{ $lead->probability }}%</span>
                                @endif
                            </div>
                            @if ($lead->expected_close_date)
                                <div class="mt-1.5 flex items-center gap-1 text-[10px] text-gray-400">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ $lead->expected_close_date->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Convert-to-client prompt (lead dropped on the Won stage) --}}
    @if ($confirmConvertLeadId)
        <x-app-modal title="Convert to client?" close="dismissConvert">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                <strong>{{ $confirmConvertName }}</strong> reached the Won stage. Convert this lead into a client now?
                A client record will be created and the lead marked as converted.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="dismissConvert" class="btn-secondary">Just move to Won</button>
                @permission('leads.convert')
                <button wire:click="convertConfirmed" class="btn-primary">Convert to Client</button>
                @endpermission
            </div>
        </x-app-modal>
    @endif

    {{-- Stage management --}}
    @if ($managingStages)
        <x-app-modal title="Manage Pipeline Stages" close="$set('managingStages', false)" max-width="sm:max-w-2xl">
            <div class="space-y-4">
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-ink-600">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-ink-600">
                        <thead class="bg-gray-50 dark:bg-ink-800">
                            <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                <th class="px-3 py-2">Order</th>
                                <th class="px-3 py-2">Stage</th>
                                <th class="px-3 py-2">Type</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                            @foreach ($stages as $stage)
                                <tr wire:key="stage-row-{{ $stage->id }}">
                                    <td class="px-3 py-2 text-gray-500">{{ $stage->sort_order }}</td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-2 font-medium text-gray-900 dark:text-white">
                                            <span class="h-3 w-3 rounded-full" style="background-color: {{ $stage->color }}"></span>
                                            {{ $stage->name }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($stage->is_won)<x-status-badge color="green" label="Won" />
                                        @elseif ($stage->is_lost)<x-status-badge color="red" label="Lost" />
                                        @else<span class="text-xs text-gray-400">Open</span>@endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="editStage({{ $stage->id }})" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Edit">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <button wire:click="deleteStage({{ $stage->id }})" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Add / edit stage form --}}
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-ink-600 dark:bg-ink-800">
                    <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $stageId ? 'Edit Stage' : 'Add Stage' }}</h4>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="form-label">Name <span class="text-red-500">*</span></label>
                            <input wire:model="stageName" type="text" class="form-input-base" placeholder="e.g. Demo Scheduled">
                            @error('stageName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Color</label>
                            <input wire:model="stageColor" type="color" class="h-10 w-full rounded-lg border border-gray-300 dark:border-ink-600">
                        </div>
                        <div>
                            <label class="form-label">Sort Order</label>
                            <input wire:model="stageSortOrder" type="number" min="0" class="form-input-base">
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                            <input wire:model="stageIsWon" type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500 dark:border-ink-600 dark:bg-ink-800">
                            Final "Won" stage
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                            <input wire:model="stageIsLost" type="checkbox" class="rounded border-gray-300 text-red-600 focus:ring-red-500 dark:border-ink-600 dark:bg-ink-800">
                            Final "Lost" stage
                        </label>
                    </div>
                    <div class="mt-3 flex justify-end gap-2">
                        @if ($stageId)
                            <button wire:click="resetStageForm" class="btn-secondary">Cancel Edit</button>
                        @endif
                        <button wire:click="saveStage" class="btn-primary">{{ $stageId ? 'Update Stage' : 'Add Stage' }}</button>
                    </div>
                </div>
            </div>
        </x-app-modal>
    @endif

    @assets
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    @endassets

    @script
    <script>
        const initKanban = () => {
            document.querySelectorAll('[data-kanban-cards]').forEach((el) => {
                if (el._sortable) return;
                el._sortable = Sortable.create(el, {
                    group: 'pipeline',
                    animation: 150,
                    ghostClass: 'kanban-ghost',
                    draggable: '[data-lead-id]',
                    onStart: () => { window._kanbanDragged = true; },
                    onEnd: (evt) => {
                        // Brief flag so the card's onclick doesn't fire right after a drag.
                        setTimeout(() => { window._kanbanDragged = false; }, 50);

                        const leadId = parseInt(evt.item.dataset.leadId, 10);
                        const stageId = parseInt(evt.to.dataset.stageId, 10);
                        const orderedIds = Array.from(evt.to.querySelectorAll('[data-lead-id]'))
                            .map((c) => parseInt(c.dataset.leadId, 10));

                        $wire.moveLead(leadId, stageId, orderedIds);
                    },
                });
            });
        };

        initKanban();
    </script>
    @endscript
</div>
