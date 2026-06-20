<div>
    <a href="{{ route('admin.pipeline.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Pipeline
    </a>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        {{-- Left column --}}
        <div class="space-y-6 lg:col-span-3">
            <div class="card p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $lead->name }}</h2>
                        @if ($lead->company_name)
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $lead->company_name }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white" style="background-color: {{ $lead->stage->color }}">{{ $lead->stage->name }}</span>
                            <span class="text-xs text-gray-400">{{ $lead->source_label }}</span>
                            @if ($lead->is_converted)
                                <x-status-badge color="green" label="Converted" />
                            @endif
                        </div>
                    </div>
                    @permission('leads.edit')
                    <a href="{{ route('admin.leads.edit', $lead) }}" wire:navigate class="btn-secondary shrink-0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Edit
                    </a>
                    @endpermission
                </div>

                {{-- Contact --}}
                <div class="mt-5 grid grid-cols-1 gap-3 border-t border-gray-100 pt-5 dark:border-ink-700 sm:grid-cols-2">
                    @if ($lead->email)
                        <a href="mailto:{{ $lead->email }}" class="flex items-center gap-2 text-sm text-gray-700 hover:text-brand-purple dark:text-gray-300">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            {{ $lead->email }}
                        </a>
                    @endif
                    @if ($lead->phone)
                        <div class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                {{ $lead->phone }}
                            </span>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="text-xs font-medium text-green-600 hover:underline">WhatsApp</a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Activity timeline --}}
            <div class="card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Activity</h3>

                <div class="mb-5 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-ink-600 dark:bg-ink-800">
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach (['note' => 'Note', 'call' => 'Call', 'email' => 'Email', 'meeting' => 'Meeting', 'whatsapp' => 'WhatsApp'] as $type => $label)
                            <button type="button" wire:click="$set('activityType', '{{ $type }}')"
                                    class="rounded-full px-3 py-1 text-xs font-medium transition {{ $activityType === $type ? 'bg-brand-purple text-white' : 'bg-white text-gray-600 hover:bg-gray-100 dark:bg-ink-700 dark:text-gray-300' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                    <textarea wire:model="activityContent" rows="2" class="form-input-base mb-2 resize-y" placeholder="Log a {{ $activityType }}…"></textarea>
                    @error('activityContent') <p class="mb-2 text-xs text-red-500">{{ $message }}</p> @enderror
                    <div class="flex justify-end">
                        <button wire:click="addActivity" class="btn-primary">Add</button>
                    </div>
                </div>

                @if ($lead->activities->isNotEmpty())
                    <div class="space-y-4">
                        @foreach ($lead->activities as $activity)
                            <div wire:key="act-{{ $activity->id }}" class="flex gap-3">
                                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full" style="background-color: {{ $activity->icon_color }}1a;">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="{{ $activity->icon_color }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $activity->icon_path }}" /></svg>
                                </span>
                                <div class="flex-1 border-b border-gray-100 pb-3 dark:border-ink-700">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ $activity->type_label }}</span>
                                        <span class="text-[11px] text-gray-400">{{ $activity->created_by }} · {{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-1 whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-200">{{ $activity->content }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="py-6 text-center text-sm text-gray-400">No activity logged yet.</p>
                @endif
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Quick actions --}}
            <div class="card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Move Stage</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($stages as $stage)
                        <button wire:click="moveStage({{ $stage->id }})"
                                @disabled($stage->id === $lead->stage_id)
                                class="rounded-lg border px-3 py-1.5 text-xs font-medium transition {{ $stage->id === $lead->stage_id ? 'cursor-default border-transparent text-white' : 'border-gray-200 text-gray-600 hover:border-gray-300 dark:border-ink-600 dark:text-gray-300' }}"
                                @if ($stage->id === $lead->stage_id) style="background-color: {{ $stage->color }}" @endif>
                            {{ $stage->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Details --}}
            <div class="card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Value</dt><dd class="font-mono font-semibold text-gray-900 dark:text-white">{{ $lead->value ? money($lead->value) : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Probability</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $lead->probability }}%</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Weighted Value</dt><dd class="font-mono font-semibold text-brand-purple">{{ money($lead->weighted_value) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Expected Close</dt><dd class="text-gray-900 dark:text-white">{{ $lead->expected_close_date?->format('M d, Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Assigned To</dt><dd class="text-gray-900 dark:text-white">{{ $lead->assigned_to ?: 'Unassigned' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Source</dt><dd class="text-gray-900 dark:text-white">{{ $lead->source_label }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Days in Pipeline</dt><dd class="text-gray-900 dark:text-white">{{ (int) $lead->created_at->diffInDays(now()) }}d</dd></div>
                </dl>
                @if ($lead->notes)
                    <div class="mt-4 border-t border-gray-100 pt-4 dark:border-ink-700">
                        <dt class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                        <p class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-200">{{ $lead->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Convert / status --}}
            @if ($lead->is_converted)
                <div class="card border border-green-200 bg-green-50 p-6 dark:border-green-900/40 dark:bg-green-900/10">
                    <div class="flex items-center gap-2 text-green-700 dark:text-green-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-sm font-semibold">Converted to client</span>
                    </div>
                    @if ($lead->convertedClient)
                        <a href="{{ route('admin.clients.show', $lead->convertedClient) }}" wire:navigate class="mt-3 inline-block text-sm font-medium text-brand-purple hover:underline">
                            View {{ $lead->convertedClient->name }} →
                        </a>
                    @endif
                </div>
            @else
                <div class="card p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Ready to close this deal?</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Convert this lead into a client, or mark it as lost.</p>
                    <div class="mt-4 space-y-2">
                        @permission('leads.convert')
                        <button wire:click="$set('confirmingConvert', true)" class="btn-primary w-full justify-center">Convert to Client</button>
                        @endpermission
                        @permission('leads.edit')
                        <button wire:click="$set('confirmingLost', true)" class="btn-danger w-full justify-center">Mark as Lost</button>
                        @endpermission
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Convert confirmation --}}
    @if ($confirmingConvert)
        <x-app-modal title="Convert to client?" close="$set('confirmingConvert', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                This creates a client record from <strong>{{ $lead->name }}</strong> and moves the lead to the Won stage.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingConvert', false)" class="btn-secondary">Cancel</button>
                <button wire:click="convert" class="btn-primary">Convert</button>
            </div>
        </x-app-modal>
    @endif

    {{-- Mark lost --}}
    @if ($confirmingLost)
        <x-app-modal title="Mark lead as lost?" close="$set('confirmingLost', false)">
            <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">Optionally record why this lead was lost.</p>
            <textarea wire:model="lostReason" rows="3" class="form-input-base" placeholder="e.g. Went with a competitor, budget cut…"></textarea>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingLost', false)" class="btn-secondary">Cancel</button>
                <button wire:click="markLost" class="btn-danger">Mark as Lost</button>
            </div>
        </x-app-modal>
    @endif
</div>
