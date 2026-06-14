<div>
    <a href="{{ route('admin.tickets.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Back to tickets
    </a>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left: conversation --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="card p-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-sm font-semibold text-brand-purple">{{ $ticket->ticket_number }}</span>
                    <x-status-badge :color="$ticket->statusColor()" :label="$ticket->statusLabel()" />
                    <x-status-badge :color="$ticket->priorityColor()" :label="ucfirst($ticket->priority)" />
                    @if ($ticket->isSlaOverdue())<x-status-badge color="red" label="SLA Overdue" />@endif
                </div>
                <h2 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ $ticket->subject }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $ticket->typeLabel() }} · Opened {{ $ticket->created_at->format('M d, Y') }}
                    @if ($ticket->client) · <a href="{{ route('admin.clients.show', $ticket->client) }}" class="text-gold hover:underline">{{ $ticket->client->name }}</a>@endif
                </p>
            </div>

            {{-- Conversation thread --}}
            <div class="card p-6">
                <div class="space-y-5">
                    @foreach ($ticket->messages as $msg)
                        @php $isAdmin = $msg->sender_type === 'admin'; @endphp
                        <div class="flex items-start gap-3 {{ $isAdmin ? 'flex-row-reverse' : '' }}">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-semibold
                                        {{ $isAdmin ? 'bg-gradient-brand text-white' : 'bg-gray-100 text-gray-500 dark:bg-ink-700 dark:text-gray-300' }}">
                                {{ strtoupper(substr($msg->sender_name, 0, 1)) }}
                            </div>
                            <div class="max-w-[80%]">
                                <div class="mb-1 text-[11px] text-gray-400 {{ $isAdmin ? 'text-right' : '' }}">
                                    {{ $msg->sender_name }} · {{ $msg->created_at->diffForHumans() }}
                                    @if ($msg->is_internal_note)<span class="font-semibold text-amber-500"> · Internal Note</span>@endif
                                </div>
                                <div class="rounded-xl border px-4 py-3 text-sm leading-relaxed
                                    @if ($msg->is_internal_note) border-amber-200 bg-amber-50 text-gray-800 dark:border-amber-800/50 dark:bg-amber-900/20 dark:text-amber-100
                                    @elseif ($isAdmin) border-brand-purple/20 bg-brand-purple/5 text-gray-800 dark:border-brand-purple/30 dark:bg-brand-purple/10 dark:text-gray-100
                                    @else border-gray-200 bg-gray-50 text-gray-800 dark:border-ink-600 dark:bg-ink-800 dark:text-gray-100 @endif">
                                    {!! nl2br(e($msg->message)) !!}
                                </div>
                                @if ($msg->attachments->count())
                                    <div class="mt-2 flex flex-wrap gap-2 {{ $isAdmin ? 'justify-end' : '' }}">
                                        @foreach ($msg->attachments as $att)
                                            <a href="{{ route('admin.tickets.attachment.download', [$ticket, $att]) }}"
                                               class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs text-brand-purple hover:bg-gray-50 dark:border-ink-600 dark:bg-ink-850 dark:hover:bg-ink-700">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                                {{ $att->filename }} <span class="text-gray-400">({{ $att->humanSize() }})</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Reply form --}}
                <form wire:submit="reply" class="mt-6 border-t border-gray-200 pt-5 dark:border-ink-600">
                    <textarea wire:model="replyMessage" rows="4" class="form-input-base" placeholder="Type your reply…"></textarea>
                    @error('replyMessage') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input wire:model="isInternalNote" type="checkbox" class="rounded border-gray-300 text-amber-500 focus:ring-amber-500 dark:border-ink-600 dark:bg-ink-800">
                                Internal note
                            </label>
                            <label class="cursor-pointer text-sm text-gray-500 hover:text-brand-purple dark:text-gray-400">
                                <input wire:model="attachments" type="file" multiple class="hidden">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                    Attach files
                                </span>
                            </label>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" wire:click="updateStatus('resolved')" class="btn-secondary">Mark Resolved</button>
                            <button type="submit" class="btn-primary">
                                <span wire:loading.remove wire:target="reply">Send Reply</span>
                                <span wire:loading wire:target="reply">Sending…</span>
                            </button>
                        </div>
                    </div>

                    {{-- Selected files / upload state --}}
                    <div wire:loading wire:target="attachments" class="mt-2 text-xs text-gray-400">Uploading…</div>
                    @if (count($attachments))
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($attachments as $file)
                                <span class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-ink-700 dark:text-gray-300">{{ $file->getClientOriginalName() }}</span>
                            @endforeach
                        </div>
                    @endif
                    @error('attachments.*') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </form>
            </div>
        </div>

        {{-- Right: sidebar --}}
        <div class="space-y-6">
            {{-- Details --}}
            <div class="card p-5">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Ticket Details</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Status</span>
                        <select wire:change="updateStatus($event.target.value)" class="form-input-base">
                            @foreach (\App\Models\Ticket::STATUSES as $s)
                                <option value="{{ $s }}" @selected($ticket->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <span class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Priority</span>
                        <select wire:change="updatePriority($event.target.value)" class="form-input-base">
                            @foreach (\App\Models\Ticket::PRIORITIES as $p)
                                <option value="{{ $p }}" @selected($ticket->priority === $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-3 dark:border-ink-700">
                        <span class="text-gray-500 dark:text-gray-400">Type</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->typeLabel() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Created</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">First Response</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->first_response_at?->format('M d, H:i') ?? 'Not yet' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">SLA Due</span>
                        @if (in_array($ticket->status, ['resolved', 'closed']))
                            <span class="font-medium text-gray-400">—</span>
                        @elseif ($ticket->sla_due_at)
                            <span class="font-medium {{ $ticket->isSlaOverdue() ? 'text-red-500' : 'text-gray-900 dark:text-white' }}">{{ $ticket->sla_due_at->format('M d, H:i') }}</span>
                        @else
                            <span class="font-medium text-gray-400">—</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Client --}}
            @if ($ticket->client)
                <div class="card p-5">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Client</h3>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->client->name }}</p>
                    @if ($ticket->client->email)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->client->email }}</p>@endif
                    @if ($ticket->client->phone)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->client->phone }}</p>@endif
                    <a href="{{ route('admin.clients.show', $ticket->client) }}" class="mt-2 inline-block text-xs font-medium text-gold hover:underline">View client →</a>
                </div>
            @endif

            {{-- Project --}}
            @if ($ticket->project)
                @php $pct = $ticket->project->completion_percentage; @endphp
                <div class="card p-5">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Linked Project</h3>
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->project->name }}</p>
                        <x-status-badge :color="$ticket->project->statusColor()" :label="$ticket->project->statusLabel()" />
                    </div>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                        <div class="h-full rounded-full bg-gradient-brand" style="width: {{ $pct }}%"></div>
                    </div>
                    <a href="{{ route('admin.projects.show', $ticket->project) }}" class="mt-2 inline-block text-xs font-medium text-gold hover:underline">View project →</a>
                </div>
            @endif

            {{-- Actions --}}
            <div class="card p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Actions</h3>
                <div class="space-y-2">
                    <button wire:click="updateStatus('resolved')" class="w-full rounded-lg bg-green-600 py-2 text-sm font-medium text-white hover:bg-green-700">Mark as Resolved</button>
                    <button wire:click="updateStatus('in_progress')" class="w-full rounded-lg bg-brand-purple py-2 text-sm font-medium text-white hover:opacity-90">Mark In Progress</button>
                    <button wire:click="updateStatus('closed')" class="w-full rounded-lg bg-gray-500 py-2 text-sm font-medium text-white hover:bg-gray-600">Close Ticket</button>
                    @if (in_array($ticket->status, ['resolved', 'closed']))
                        <button wire:click="updateStatus('open')" class="w-full rounded-lg border border-gray-300 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-ink-600 dark:text-gray-200 dark:hover:bg-ink-700">Reopen</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
