<div>
    @php $fmt = fn ($m) => sprintf('%dh %02dm', intdiv((int) $m, 60), (int) $m % 60); @endphp

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Time Tracking</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track time against projects and clients.</p>
        </div>
        <a href="{{ route('admin.time.report') }}" wire:navigate class="btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0V9m0 8H7m8 0h2M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            View Report
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Today</span><p class="mt-2 font-mono text-2xl font-semibold text-gray-900 dark:text-white">{{ $fmt($todayMinutes) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Billable today</span><p class="mt-2 font-mono text-2xl font-semibold text-brand-purple">{{ $fmt($todayBillable) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">This week</span><p class="mt-2 font-mono text-2xl font-semibold text-gray-900 dark:text-white">{{ $fmt($weekMinutes) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Timer</span><p class="mt-2 text-2xl font-semibold {{ $runningEntry ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">{{ $runningEntry ? 'Active' : 'None' }}</p></div>
    </div>

    {{-- Running timer / start form --}}
    @if ($runningEntry)
        <div class="card mb-6 border-l-4 border-green-500 p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-green-500"></span>
                        </span>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">Timer Running</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-200">
                        {{ $runningEntry->description ?? 'No description' }}
                        @if ($runningEntry->project) — {{ $runningEntry->project->name }} @endif
                    </p>
                    <p class="text-xs text-gray-400">Started {{ $runningEntry->started_at->format('H:i') }} ({{ $runningEntry->started_at->diffForHumans() }})</p>
                </div>
                <div class="text-center"
                     x-data="{
                        started: new Date('{{ $runningEntry->started_at->toIso8601String() }}').getTime(),
                        elapsed: '00:00:00',
                        tick() {
                            let d = Math.max(0, Math.floor((Date.now() - this.started) / 1000));
                            let h = String(Math.floor(d / 3600)).padStart(2, '0');
                            let m = String(Math.floor((d % 3600) / 60)).padStart(2, '0');
                            let s = String(d % 60).padStart(2, '0');
                            this.elapsed = h + ':' + m + ':' + s;
                        }
                     }"
                     x-init="tick(); setInterval(() => tick(), 1000)">
                    <div class="font-mono text-3xl font-bold text-gray-900 dark:text-white" x-text="elapsed">00:00:00</div>
                    <button wire:click="stopTimer({{ $runningEntry->id }})" class="btn-danger mt-2 !py-1.5 text-sm">
                        <svg class="mr-1 inline h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2" /></svg>
                        Stop Timer
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="card mb-6 p-5">
            <h3 class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-gray-900 dark:text-white">
                <svg class="h-4 w-4 text-brand-purple" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                Start Timer
            </h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-1">
                    <label class="form-label">Description</label>
                    <input wire:model="description" type="text" placeholder="What are you working on?" class="form-input-base">
                </div>
                <div>
                    <label class="form-label">Project</label>
                    <select wire:model="projectId" class="form-input-base">
                        <option value="">No project</option>
                        @foreach ($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Client</label>
                    <select wire:model="clientId" class="form-input-base">
                        <option value="">No client</option>
                        @foreach ($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button wire:click="startTimer" class="btn-primary w-full justify-center">
                        <svg class="mr-1 inline h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                        Start
                    </button>
                </div>
            </div>
            <label class="mt-3 inline-flex cursor-pointer items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <input wire:model="isBillable" type="checkbox" class="rounded border-gray-300 text-brand-purple focus:ring-brand-purple dark:border-ink-600 dark:bg-ink-800">
                Billable time
            </label>
        </div>
    @endif

    {{-- Manual entry --}}
    <div class="mb-6">
        <button wire:click="$toggle('showManual')" class="inline-flex items-center gap-1 rounded-md border border-brand-purple/30 px-3 py-1.5 text-xs font-medium text-brand-purple hover:bg-brand-purple/5">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            Add Manual Entry
        </button>

        @if ($showManual)
            <div class="card mt-3 p-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <label class="form-label">Description</label>
                        <input wire:model="mDescription" type="text" placeholder="Task description" class="form-input-base">
                        @error('mDescription') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Date</label>
                        <input wire:model="mDate" type="date" class="form-input-base">
                        @error('mDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Start</label>
                        <input wire:model="mStart" type="time" class="form-input-base">
                        @error('mStart') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">End</label>
                        <input wire:model="mEnd" type="time" class="form-input-base">
                        @error('mEnd') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Rate (/hr)</label>
                        <input wire:model="mRate" type="number" step="0.01" min="0" placeholder="auto" class="form-input-base">
                    </div>
                    <div>
                        <label class="form-label">Project</label>
                        <select wire:model="mProjectId" class="form-input-base">
                            <option value="">No project</option>
                            @foreach ($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Client</label>
                        <select wire:model="mClientId" class="form-input-base">
                            <option value="">No client</option>
                            @foreach ($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <input wire:model="mBillable" type="checkbox" class="rounded border-gray-300 text-brand-purple focus:ring-brand-purple dark:border-ink-600 dark:bg-ink-800">
                        Billable
                    </label>
                    <button wire:click="addManual" class="btn-primary !py-1.5 text-sm">Add Entry</button>
                </div>
            </div>
        @endif
    </div>

    {{-- Entries for the day --}}
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Time Entries</h3>
        <input wire:model.live="filterDate" type="date" class="form-input-base !w-auto !py-1 text-sm">
    </div>

    <div class="space-y-2">
        @forelse ($entries as $entry)
            <div wire:key="te-{{ $entry->id }}" class="card flex items-center gap-3 p-3">
                <div class="h-10 w-1 shrink-0 rounded {{ $entry->is_billable ? 'bg-brand-purple' : 'bg-gray-200 dark:bg-ink-600' }}"></div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $entry->description ?? 'No description' }}</p>
                    <p class="text-xs text-gray-400">
                        @if ($entry->project){{ $entry->project->name }}@endif
                        @if ($entry->client) · {{ $entry->client->name }}@endif
                        @if (! $entry->project && ! $entry->client)—@endif
                    </p>
                </div>
                <div class="shrink-0 text-center">
                    <p class="text-xs text-gray-400">{{ $entry->started_at->format('H:i') }} — {{ $entry->ended_at?->format('H:i') ?? '…' }}</p>
                    <p class="font-mono text-sm font-bold text-gray-900 dark:text-white">{{ $entry->is_running ? 'running' : $entry->duration_formatted }}</p>
                </div>
                @if ($entry->is_billable && $entry->hourly_rate)
                    <div class="shrink-0 text-right">
                        <p class="text-[10px] uppercase text-gray-400">billable</p>
                        <p class="font-mono text-sm font-semibold text-brand-purple">{{ money($entry->billable_amount) }}</p>
                        @if ($entry->is_billed)<p class="text-[10px] text-green-600 dark:text-green-400">✓ billed</p>@endif
                    </div>
                @endif
                <button wire:click="deleteEntry({{ $entry->id }})" wire:confirm="Delete this entry?" class="shrink-0 rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        @empty
            <div class="card p-10 text-center text-sm text-gray-400">No time entries for this day. Start a timer or add one manually.</div>
        @endforelse
    </div>
</div>
