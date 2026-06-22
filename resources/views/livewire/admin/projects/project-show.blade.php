<div>
    <a href="{{ route('admin.projects.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Back to projects
    </a>

    @php $pct = $project->completion_percentage; @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Header --}}
            <div class="card p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="mb-2 flex items-center gap-2">
                            <x-status-badge :color="$project->statusColor()" :label="$project->statusLabel()" />
                            <x-status-badge :color="$project->priorityColor()" :label="ucfirst($project->priority) . ' priority'" />
                            @if ($project->isOverdue())
                                <x-status-badge color="red" label="Overdue" />
                            @endif
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $project->name }}</h2>
                        @if ($project->client)
                            <a href="{{ route('admin.clients.show', $project->client) }}" class="text-sm text-gray-500 hover:text-gold dark:text-gray-400">{{ $project->client->name }}</a>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.projects.edit', $project) }}" class="btn-secondary">Edit</a>
                    </div>
                </div>

                @if ($project->description)
                    <p class="mt-4 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300">{{ $project->description }}</p>
                @endif

                {{-- Progress --}}
                <div class="mt-5">
                    <div class="mb-1.5 flex justify-between text-sm">
                        <span class="font-medium text-gray-700 dark:text-gray-200">Progress</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $pct }}%</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                        <div class="h-full rounded-full bg-gradient-brand transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Tasks --}}
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-ink-600">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        Tasks
                        <span class="ml-1 text-sm font-normal text-gray-400">
                            ({{ $project->tasks->where('status', 'done')->count() }} of {{ $project->tasks->count() }})
                        </span>
                    </h3>
                    <button wire:click="$toggle('showTaskForm')" class="text-sm font-medium text-gold hover:underline">
                        {{ $showTaskForm ? 'Cancel' : '+ Add Task' }}
                    </button>
                </div>

                {{-- Add task form --}}
                @if ($showTaskForm)
                    <form wire:submit="addTask" class="border-b border-gray-200 bg-gray-50 p-4 dark:border-ink-600 dark:bg-ink-800/50">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <div class="flex-1">
                                <input wire:model="taskTitle" type="text" placeholder="Task title…" class="form-input-base" autofocus>
                                @error('taskTitle') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <select wire:model="taskPriority" class="form-input-base sm:w-32">
                                @foreach (\App\Models\Task::PRIORITIES as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
                            </select>
                            <input wire:model="taskDueDate" type="date" class="form-input-base sm:w-40">
                            <button type="submit" class="btn-primary">Add</button>
                        </div>
                    </form>
                @endif

                {{-- Task list --}}
                <div class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($project->tasks as $task)
                        @php $done = $task->status === 'done'; @endphp
                        <div class="flex items-center gap-3 px-5 py-3" wire:key="task-{{ $task->id }}">
                            <button wire:click="toggleTask({{ $task->id }})"
                                    class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border-2 transition
                                           {{ $done ? 'border-green-500 bg-green-500' : 'border-gray-300 hover:border-green-400 dark:border-ink-600' }}"
                                    title="{{ $done ? 'Mark as to-do' : 'Mark as done' }}">
                                @if ($done)
                                    <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                @endif
                            </button>

                            <div class="flex-1">
                                <p class="text-sm font-medium {{ $done ? 'text-gray-400 line-through' : 'text-gray-900 dark:text-white' }}">{{ $task->title }}</p>
                                @if ($task->due_date)
                                    <p class="text-xs {{ $task->due_date->isPast() && ! $done ? 'text-red-500' : 'text-gray-400' }}">Due {{ $task->due_date->format('M d, Y') }}</p>
                                @endif
                            </div>

                            <x-status-badge :color="$task->priorityColor()" :label="ucfirst($task->priority)" />

                            <button wire:click="deleteTask({{ $task->id }})" wire:confirm="Delete this task?" class="rounded p-1 text-gray-400 hover:text-red-500" title="Delete">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-gray-400">No tasks yet. Add the first one above.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-6">
            {{-- Details --}}
            <div class="card p-5">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Project Details</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Status</span>
                        <select wire:change="updateStatus($event.target.value)" class="form-input-base">
                            @foreach (\App\Models\Project::STATUSES as $s)
                                <option value="{{ $s }}" @selected($project->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-3 dark:border-ink-700">
                        <span class="text-gray-500 dark:text-gray-400">Priority</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ ucfirst($project->priority) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Start Date</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $project->start_date?->format('M d, Y') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Due Date</span>
                        <span class="font-medium {{ $project->isOverdue() ? 'text-red-500' : 'text-gray-900 dark:text-white' }}">{{ $project->due_date?->format('M d, Y') ?? '—' }}</span>
                    </div>
                    @if ($project->budget !== null)
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Budget</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ money($project->budget) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Time tracked --}}
            @php
                $projectTime = $project->timeEntries()->whereNotNull('duration_minutes')->get();
                $projectMins = (int) $projectTime->sum('duration_minutes');
                $projectBillable = (float) $projectTime->where('is_billable', true)->sum('billable_amount');
            @endphp
            @if ($projectMins > 0)
                <div class="card p-5">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Time Tracked</h3>
                    <div class="flex gap-6">
                        <div>
                            <p class="font-mono text-2xl font-bold text-gray-900 dark:text-white">{{ sprintf('%dh %02dm', intdiv($projectMins, 60), $projectMins % 60) }}</p>
                            <p class="text-xs text-gray-400">total hours</p>
                        </div>
                        @if ($projectBillable > 0)
                            <div>
                                <p class="text-2xl font-bold text-brand-purple">{{ money($projectBillable) }}</p>
                                <p class="text-xs text-gray-400">billable value</p>
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('admin.time.report', ['projectId' => $project->id]) }}" wire:navigate class="mt-3 inline-block text-xs font-medium text-brand-purple hover:underline">View time report →</a>
                </div>
            @endif

            {{-- Linked invoices --}}
            <div class="card p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Linked Invoices</h3>
                    <button wire:click="$toggle('showLinkInvoice')" class="text-xs font-medium text-gold hover:underline">+ Link</button>
                </div>

                <div class="space-y-2">
                    @forelse ($project->invoices as $inv)
                        <div class="flex items-center justify-between rounded-lg border border-gray-100 px-3 py-2 dark:border-ink-700" wire:key="inv-{{ $inv->id }}">
                            <a href="{{ route('admin.invoices.show', $inv) }}" class="text-sm font-medium text-gray-900 hover:text-gold dark:text-white">{{ $inv->invoice_number }}</a>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ money($inv->total) }}</span>
                                <x-status-badge :color="$inv->statusColor()" :label="$inv->status" />
                                <button wire:click="unlinkInvoice({{ $inv->id }})" class="text-gray-300 hover:text-red-500" title="Unlink">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No invoices linked.</p>
                    @endforelse
                </div>

                @if ($project->invoices->count())
                    <div class="mt-3 flex justify-between border-t border-gray-100 pt-3 text-sm dark:border-ink-700">
                        <span class="text-gray-500 dark:text-gray-400">Total invoiced</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ money($invoicedTotal) }}</span>
                    </div>
                @endif
            </div>

            {{-- Client card --}}
            @if ($project->client)
                <div class="card p-5">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Client</h3>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $project->client->name }}</p>
                    @if ($project->client->email)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $project->client->email }}</p>@endif
                    @if ($project->client->phone)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $project->client->phone }}</p>@endif
                    <a href="{{ route('admin.clients.show', $project->client) }}" class="mt-2 inline-block text-xs font-medium text-gold hover:underline">View client →</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Link invoice modal --}}
    @if ($showLinkInvoice)
        <x-app-modal title="Link an Invoice" close="$set('showLinkInvoice', false)">
            <form wire:submit="linkInvoice" class="space-y-4">
                <div>
                    <label class="form-label">Select Invoice</label>
                    <select wire:model="linkInvoiceId" class="form-input-base">
                        <option value="">— Choose —</option>
                        @foreach ($availableInvoices as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->invoice_number }} — {{ $inv->client?->name ?? '—' }} ({{ money($inv->total) }})</option>
                        @endforeach
                    </select>
                    @error('linkInvoiceId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    @if ($availableInvoices->isEmpty())
                        <p class="mt-1 text-xs text-gray-400">No invoices available to link.</p>
                    @endif
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="$set('showLinkInvoice', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Link Invoice</button>
                </div>
            </form>
        </x-app-modal>
    @endif

    <x-notes-attachments :record="$project" />
</div>
