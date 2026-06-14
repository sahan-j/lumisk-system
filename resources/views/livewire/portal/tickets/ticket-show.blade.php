<div>
    <a href="{{ route('portal.tickets.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-purple dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Back to tickets
    </a>

    @php $isClosed = in_array($ticket->status, ['closed', 'resolved']); @endphp

    {{-- Status bar --}}
    <div class="card mb-6 flex flex-wrap items-center justify-between gap-3 p-5">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono text-sm font-semibold text-brand-purple">{{ $ticket->ticket_number }}</span>
                <x-status-badge :color="$ticket->statusColor()" :label="$ticket->statusLabel()" />
                <x-status-badge :color="$ticket->priorityColor()" :label="ucfirst($ticket->priority)" />
            </div>
            <h2 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $ticket->subject }}</h2>
            <p class="text-xs text-gray-400">{{ $ticket->typeLabel() }} · Opened {{ $ticket->created_at->format('M d, Y') }}</p>
        </div>
        <div>
            @if ($isClosed)
                <button wire:click="reopen" class="btn-secondary">Reopen Ticket</button>
            @else
                <button wire:click="close" wire:confirm="Close this ticket?" class="btn-secondary">Close Ticket</button>
            @endif
        </div>
    </div>

    {{-- Conversation --}}
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
                        </div>
                        <div class="rounded-xl border px-4 py-3 text-sm leading-relaxed
                            {{ $isAdmin
                                ? 'border-brand-purple/20 bg-brand-purple/5 text-gray-800 dark:border-brand-purple/30 dark:bg-brand-purple/10 dark:text-gray-100'
                                : 'border-gray-200 bg-gray-50 text-gray-800 dark:border-ink-600 dark:bg-ink-800 dark:text-gray-100' }}">
                            {!! nl2br(e($msg->message)) !!}
                        </div>
                        @if ($msg->attachments->count())
                            <div class="mt-2 flex flex-wrap gap-2 {{ $isAdmin ? 'justify-end' : '' }}">
                                @foreach ($msg->attachments as $att)
                                    <a href="{{ route('portal.tickets.attachment.download', [$ticket, $att]) }}"
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
        @if (! $isClosed)
            <form wire:submit="reply" class="mt-6 border-t border-gray-200 pt-5 dark:border-ink-600">
                <textarea wire:model="replyMessage" rows="4" class="form-input-base" placeholder="Type your reply…"></textarea>
                @error('replyMessage') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <label class="cursor-pointer text-sm text-gray-500 hover:text-brand-purple dark:text-gray-400">
                        <input wire:model="attachments" type="file" multiple class="hidden">
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                            Attach files
                        </span>
                    </label>
                    <button type="submit" class="btn-primary">
                        <span wire:loading.remove wire:target="reply">Send Reply</span>
                        <span wire:loading wire:target="reply">Sending…</span>
                    </button>
                </div>
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
        @else
            <p class="mt-6 border-t border-gray-200 pt-5 text-center text-sm text-gray-400 dark:border-ink-600">
                This ticket is {{ $ticket->statusLabel() }}. Reopen it to continue the conversation.
            </p>
        @endif
    </div>
</div>
