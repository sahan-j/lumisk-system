<div wire:key="attachments-{{ $modelType }}-{{ $modelId }}">
    {{-- Upload area --}}
    <div x-data="{ dragging: false }"
         x-on:dragover.prevent="dragging = true"
         x-on:dragleave.prevent="dragging = false"
         x-on:drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
         @click="$refs.fileInput.click()"
         :class="dragging ? 'border-brand-purple bg-brand-purple/5' : 'border-gray-300 dark:border-ink-600'"
         class="mb-4 cursor-pointer rounded-xl border-2 border-dashed p-6 text-center transition hover:border-gray-400 dark:hover:border-ink-500">
        <input x-ref="fileInput" type="file" multiple class="hidden" wire:model="uploadedFiles">
        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-300">Drag &amp; drop files here, or click to browse</p>
        <p class="mt-1 text-xs text-gray-400">JPG, PNG, PDF, DOC, XLS, ZIP — max 10MB each</p>
    </div>

    <div wire:loading wire:target="uploadedFiles,uploadFiles" class="mb-4 text-center text-sm text-brand-purple">
        Uploading…
    </div>

    @error('uploadedFiles.*') <p class="mb-3 text-sm text-red-500">{{ $message }}</p> @enderror

    {{-- Attachments grid --}}
    @if ($attachments->isNotEmpty())
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($attachments as $attachment)
                <div wire:key="att-{{ $attachment->id }}" class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-ink-600 dark:bg-ink-800">
                    <div class="mb-2 flex h-20 items-center justify-center overflow-hidden rounded">
                        @if ($attachment->is_image)
                            <img src="{{ Storage::url($attachment->path) }}" alt="{{ $attachment->filename }}" class="h-20 w-full rounded object-cover">
                        @else
                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="{{ $attachment->icon_color }}" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $attachment->icon_path }}" /></svg>
                        @endif
                    </div>
                    <p class="truncate text-xs font-medium text-gray-900 dark:text-white" title="{{ $attachment->filename }}">{{ $attachment->filename }}</p>
                    <p class="mb-2 text-[10px] text-gray-400">{{ $attachment->file_size_formatted }} · {{ $attachment->created_at->format('M d') }}</p>
                    <div class="flex items-center gap-1">
                        <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="flex flex-1 items-center justify-center gap-1 rounded border border-gray-200 bg-white py-1 text-[11px] text-brand-purple hover:bg-gray-50 dark:border-ink-600 dark:bg-ink-700 dark:hover:bg-ink-600">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            View
                        </a>
                        <a href="{{ Storage::url($attachment->path) }}" download="{{ $attachment->filename }}" class="rounded border border-gray-200 bg-white p-1.5 text-gray-500 hover:bg-gray-50 dark:border-ink-600 dark:bg-ink-700 dark:hover:bg-ink-600" title="Download">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        </a>
                        <button wire:click="deleteAttachment({{ $attachment->id }})" wire:confirm="Delete {{ $attachment->filename }}?" class="rounded border border-red-200 bg-white p-1.5 text-red-500 hover:bg-red-50 dark:border-red-900/40 dark:bg-ink-700" title="Delete">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="py-6 text-center text-sm text-gray-400">No attachments yet.</p>
    @endif
</div>
