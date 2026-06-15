<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Projects</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track project progress, tasks and deadlines.</p>
        </div>
        @permission('projects.create')
        <a href="{{ route('admin.projects.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            New Project
        </a>
        @endpermission
    </div>

    {{-- Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search projects…" class="form-input-base pl-10">
        </div>
        <select wire:model.live="status" class="form-input-base sm:w-44">
            <option value="">All statuses</option>
            @foreach ($statuses as $s)<option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach
        </select>
        <select wire:model.live="client" class="form-input-base sm:w-48">
            <option value="">All clients</option>
            @foreach ($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
    </div>

    {{-- Grid --}}
    @if ($projects->count())
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($projects as $project)
                @php $pct = $project->completion_percentage; @endphp
                <div class="card flex flex-col p-5">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-[10px] font-semibold uppercase tracking-wider {{ match($project->statusColor()) {
                            'blue' => 'text-brand-purple',
                            'green' => 'text-green-600 dark:text-green-400',
                            'amber' => 'text-amber-600 dark:text-amber-400',
                            'red' => 'text-red-600 dark:text-red-400',
                            default => 'text-gray-500 dark:text-gray-400',
                        } }}">{{ $project->statusLabel() }}</span>
                        <x-status-badge :color="$project->priorityColor()" :label="ucfirst($project->priority)" />
                    </div>

                    <a href="{{ route('admin.projects.show', $project) }}" class="text-base font-semibold text-gray-900 hover:text-gold dark:text-white">{{ $project->name }}</a>
                    <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">{{ $project->client?->name ?? 'No client' }}</p>

                    {{-- Progress --}}
                    <div class="mb-3">
                        <div class="mb-1 flex justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Progress</span>
                            <span>{{ $pct }}%</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                            <div class="h-full rounded-full bg-gradient-brand" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    <div class="mt-auto flex items-center justify-between text-xs text-gray-400">
                        <span>{{ $project->tasks_count }} tasks ({{ $project->done_tasks_count }} done)</span>
                        @if ($project->due_date)
                            <span class="{{ $project->isOverdue() ? 'font-medium text-red-500' : '' }}">Due {{ $project->due_date->format('M d') }}</span>
                        @endif
                    </div>

                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('admin.projects.show', $project) }}" class="flex-1 rounded-lg border border-brand-purple py-1.5 text-center text-xs font-medium text-brand-purple hover:bg-brand-purple/5">View</a>
                        <a href="{{ route('admin.projects.edit', $project) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-ink-600 dark:text-gray-300 dark:hover:bg-ink-700">Edit</a>
                        <button wire:click="confirmDelete({{ $project->id }})" class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:border-ink-600 dark:hover:bg-red-900/30" title="Delete">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $projects->links() }}</div>
    @else
        <div class="card p-12 text-center">
            <p class="text-sm text-gray-400">No projects found.</p>
        </div>
    @endif

    {{-- Delete confirmation --}}
    @if ($confirmingDelete)
        <x-app-modal title="Delete project?" close="$set('confirmingDelete', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">This will soft-delete the project and its tasks. Linked invoices remain in the system.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </x-app-modal>
    @endif
</div>
