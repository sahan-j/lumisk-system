<div>
    @if ($projects->count())
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($projects as $project)
                @php $pct = $project->completion_percentage; @endphp
                <a href="{{ route('portal.projects.show', $project) }}" class="card flex flex-col p-5 transition hover:shadow-md">
                    <div class="mb-2 flex items-center justify-between">
                        <x-status-badge :color="$project->statusColor()" :label="$project->statusLabel()" />
                        @if ($project->isOverdue())
                            <x-status-badge color="red" label="Overdue" />
                        @endif
                    </div>

                    <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $project->name }}</p>
                    <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                        @if ($project->due_date) Due {{ $project->due_date->format('M d, Y') }} @else No due date @endif
                    </p>

                    <div class="mt-auto">
                        <div class="mb-1 flex justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $project->done_tasks_count }} / {{ $project->tasks_count }} tasks</span>
                            <span>{{ $pct }}%</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                            <div class="h-full rounded-full bg-gradient-brand" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="card p-12 text-center">
            <p class="text-sm text-gray-400">No projects yet.</p>
        </div>
    @endif
</div>
