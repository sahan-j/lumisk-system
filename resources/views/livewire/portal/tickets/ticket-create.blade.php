<div>
    <a href="{{ route('portal.tickets.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-purple dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Back to tickets
    </a>

    <form wire:submit="store" class="card max-w-2xl space-y-5 p-6">
        <div>
            <label class="form-label">Subject <span class="text-red-500">*</span></label>
            <input wire:model="subject" type="text" class="form-input-base" placeholder="Brief summary of your request">
            @error('subject') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="form-label">Type <span class="text-red-500">*</span></label>
                <select wire:model="type" class="form-input-base">
                    <option value="bug_report">🐛 Bug Report</option>
                    <option value="feature_request">✨ Feature Request</option>
                    <option value="general_question">❓ General Question</option>
                    <option value="maintenance_request">🔧 Maintenance Request</option>
                </select>
                @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Priority <span class="text-red-500">*</span></label>
                <select wire:model="priority" class="form-input-base">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
                @error('priority') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        @if ($projects->count())
            <div>
                <label class="form-label">Related Project</label>
                <select wire:model="project_id" class="form-input-base">
                    <option value="">— None —</option>
                    @foreach ($projects as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                </select>
                @error('project_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        @endif

        <div>
            <label class="form-label">Message <span class="text-red-500">*</span></label>
            <textarea wire:model="message" rows="6" class="form-input-base" placeholder="Please describe your issue in detail…"></textarea>
            @error('message') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Attachments</label>
            <input wire:model="attachments" type="file" multiple
                   class="block text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-gray-200 dark:text-gray-400 dark:file:bg-ink-700 dark:file:text-gray-200">
            <p class="mt-1 text-xs text-gray-400">Max 5MB per file. Supported: JPG, PNG, GIF, PDF, DOC, DOCX, ZIP.</p>
            <div wire:loading wire:target="attachments" class="mt-1 text-xs text-gray-400">Uploading…</div>
            @if (count($attachments))
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($attachments as $file)
                        <span class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-ink-700 dark:text-gray-300">{{ $file->getClientOriginalName() }}</span>
                    @endforeach
                </div>
            @endif
            @error('attachments.*') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('portal.tickets.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <span wire:loading.remove wire:target="store">Submit Ticket</span>
                <span wire:loading wire:target="store">Submitting…</span>
            </button>
        </div>
    </form>
</div>
