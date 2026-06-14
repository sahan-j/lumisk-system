<div>
    <a href="{{ route('portal.projects.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-purple dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Back to projects
    </a>

    @php $pct = $project->completion_percentage; @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="card p-6">
                <div class="mb-2 flex items-center gap-2">
                    <x-status-badge :color="$project->statusColor()" :label="$project->statusLabel()" />
                    @if ($project->isOverdue())
                        <x-status-badge color="red" label="Overdue" />
                    @endif
                </div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $project->name }}</h2>

                @if ($project->description)
                    <p class="mt-3 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300">{{ $project->description }}</p>
                @endif

                <div class="mt-5">
                    <div class="mb-1.5 flex justify-between text-sm">
                        <span class="font-medium text-gray-700 dark:text-gray-200">Progress</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $pct }}%</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                        <div class="h-full rounded-full bg-gradient-brand" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Tasks (read-only) --}}
            <div class="card overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-ink-600">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        Tasks
                        <span class="ml-1 text-sm font-normal text-gray-400">({{ $project->tasks->where('status', 'done')->count() }} of {{ $project->tasks->count() }})</span>
                    </h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($project->tasks as $task)
                        @php $done = $task->status === 'done'; @endphp
                        <div class="flex items-center gap-3 px-5 py-3">
                            <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border-2 {{ $done ? 'border-green-500 bg-green-500' : 'border-gray-300 dark:border-ink-600' }}">
                                @if ($done)
                                    <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                @endif
                            </span>
                            <div class="flex-1">
                                <p class="text-sm {{ $done ? 'text-gray-400 line-through' : 'text-gray-900 dark:text-white' }}">{{ $task->title }}</p>
                                @if ($task->due_date)
                                    <p class="text-xs text-gray-400">Due {{ $task->due_date->format('M d, Y') }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-gray-400">No tasks yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right --}}
        <div class="space-y-6">
            <div class="card p-5">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Details</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Start Date</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $project->start_date?->format('M d, Y') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Due Date</span>
                        <span class="font-medium {{ $project->isOverdue() ? 'text-red-500' : 'text-gray-900 dark:text-white' }}">{{ $project->due_date?->format('M d, Y') ?? '—' }}</span>
                    </div>
                </div>
            </div>

            @if ($project->invoices->count())
                <div class="card p-5">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Invoices</h3>
                    <div class="space-y-2">
                        @foreach ($project->invoices as $inv)
                            <a href="{{ route('portal.invoices.show', $inv) }}" class="flex items-center justify-between rounded-lg border border-gray-100 px-3 py-2 hover:bg-gray-50 dark:border-ink-700 dark:hover:bg-ink-800">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $inv->invoice_number }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ money($inv->total) }}</span>
                                    <x-status-badge :color="$inv->statusColor()" :label="$inv->status" />
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
