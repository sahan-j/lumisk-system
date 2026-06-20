@props(['doc'])

@php
    $clientNotes = $doc->clientNotes()->get();
    $attachments = $doc->attachments;
@endphp

@if ($clientNotes->isNotEmpty())
    <div class="card mt-6 p-6">
        <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Notes from {{ company_settings()->name ?: 'us' }}</h3>
        <div class="space-y-3">
            @foreach ($clientNotes as $note)
                <div class="rounded-lg border-l-[3px] border-l-cyan-400 border border-gray-200 bg-cyan-50/40 p-3 dark:border-ink-600 dark:bg-cyan-900/10">
                    <p class="mb-1 text-xs text-gray-400">{{ $note->author_name }} · {{ $note->created_at->format('M d, Y') }}</p>
                    <p class="whitespace-pre-wrap text-sm leading-relaxed text-gray-700 dark:text-gray-200">{{ $note->content }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if ($attachments->isNotEmpty())
    <div class="card mt-6 p-6">
        <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Attachments</h3>
        <div class="flex flex-wrap gap-2">
            @foreach ($attachments as $attachment)
                <a href="{{ Storage::url($attachment->path) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-brand-purple hover:bg-gray-50 dark:border-ink-600 dark:bg-ink-850 dark:hover:bg-ink-800">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="{{ $attachment->icon_color }}" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $attachment->icon_path }}" /></svg>
                    {{ $attachment->filename }}
                    <span class="text-gray-400">({{ $attachment->file_size_formatted }})</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
