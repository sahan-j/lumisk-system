<div wire:key="notes-{{ $modelType }}-{{ $modelId }}">
    {{-- Add note --}}
    @if (! $isAdding)
        <button wire:click="$set('isAdding', true)" class="mb-4 inline-flex items-center gap-1.5 rounded-lg border border-brand-purple/30 bg-brand-purple/5 px-3 py-1.5 text-sm font-medium text-brand-purple hover:bg-brand-purple/10">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            Add Note
        </button>
    @else
        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-ink-600 dark:bg-ink-800">
            <textarea wire:model="newNote" rows="3" placeholder="Write a note…" class="form-input-base mb-3 resize-y"></textarea>
            @error('newNote') <p class="mb-2 text-xs text-red-500">{{ $message }}</p> @enderror
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label class="flex cursor-pointer items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="isInternal" class="rounded border-gray-300 text-gold focus:ring-gold dark:border-ink-600 dark:bg-ink-800">
                    Internal note (admin only)
                    @if ($isInternal)
                        <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">🔒 Hidden from client</span>
                    @else
                        <span class="rounded bg-cyan-100 px-1.5 py-0.5 text-[10px] font-medium text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300">👤 Visible to client</span>
                    @endif
                </label>
                <div class="flex gap-2">
                    <button wire:click="$set('isAdding', false)" type="button" class="btn-secondary">Cancel</button>
                    <button wire:click="addNote" class="btn-primary">Save Note</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Notes list --}}
    @if ($notes->isNotEmpty())
        <div class="space-y-3">
            @foreach ($notes as $note)
                <div wire:key="note-{{ $note->id }}"
                     class="rounded-lg border-l-[3px] p-3 {{ $note->is_internal
                        ? 'border-l-amber-400 border border-amber-200 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-900/10'
                        : 'border-l-cyan-400 border border-gray-200 bg-white dark:border-ink-600 dark:bg-ink-850' }}">
                    <div class="mb-2 flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-brand text-[11px] font-semibold text-white">{{ strtoupper(substr($note->author_name, 0, 1)) }}</span>
                            <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $note->author_name }}</span>
                            <span class="text-[11px] text-gray-400">{{ $note->created_at->diffForHumans() }}</span>
                            @if ($note->is_internal)
                                <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">🔒 Internal</span>
                            @else
                                <span class="rounded bg-cyan-100 px-1.5 py-0.5 text-[10px] font-medium text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300">👤 Client visible</span>
                            @endif
                        </div>
                        <button wire:click="deleteNote({{ $note->id }})" wire:confirm="Delete this note?" class="text-gray-400 hover:text-red-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <p class="whitespace-pre-wrap text-sm leading-relaxed text-gray-700 dark:text-gray-200">{{ $note->content }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="py-6 text-center text-sm text-gray-400">No notes yet.</p>
    @endif
</div>
