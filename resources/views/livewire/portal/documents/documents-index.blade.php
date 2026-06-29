<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">My Documents</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Share files with Lumisk Technology and access documents we've shared with you.</p>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">Total Documents</p><p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">My Uploads</p><p class="mt-1 text-2xl font-semibold text-brand-purple">{{ $stats['my_uploads'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">From Lumisk</p><p class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">{{ $stats['from_lumisk'] }}</p></div>
        @if ($stats['new_from_lumisk'] > 0)
            <div class="card border-brand-purple/30 bg-brand-purple/5 p-4"><p class="text-xs text-brand-purple">New for You</p><p class="mt-1 text-2xl font-semibold text-brand-purple">{{ $stats['new_from_lumisk'] }} new</p></div>
        @else
            <div class="card p-4"><p class="text-xs text-green-600 dark:text-green-400">All Caught Up</p><p class="mt-1 text-sm font-medium text-green-600 dark:text-green-400">✓ No new files</p></div>
        @endif
    </div>

    {{-- Upload --}}
    <form wire:submit="upload" class="card mb-6 p-5">
        <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
            <svg class="h-4 w-4 text-brand-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
            Upload Documents
        </h3>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900/40 dark:bg-red-900/10">
                @foreach ($errors->all() as $error)
                    <p class="text-xs text-red-600 dark:text-red-400">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="form-label">Files <span class="text-red-500">*</span></label>
                <input wire:model="files" type="file" multiple class="form-input-base text-sm">
                <p class="mt-1 text-xs text-gray-400">PDF, images, Word, Excel, ZIP — max 20MB each.</p>
                <div wire:loading wire:target="files" class="mt-1 text-xs text-brand-purple">Uploading…</div>
            </div>
            <div>
                <label class="form-label">Category <span class="text-red-500">*</span></label>
                <select wire:model="category" class="form-input-base">
                    <option value="">Select category…</option>
                    <option value="payment_proof">💳 Payment Proof</option>
                    <option value="requirements">📋 Requirements / Brief</option>
                    <option value="content">✏️ Content</option>
                    <option value="contract">📄 Signed Contract</option>
                    <option value="design_feedback">🎨 Design Feedback</option>
                    <option value="other">📎 Other</option>
                </select>
                @error('category') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Related Project</label>
                <select wire:model="projectId" class="form-input-base">
                    <option value="">No specific project</option>
                    @foreach ($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="form-label">Note for Lumisk Team</label>
                <textarea wire:model="clientNote" rows="2" placeholder="Any notes about these files…" class="form-input-base text-sm"></textarea>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="upload,files">
                <span wire:loading.remove wire:target="upload">Upload Files</span>
                <span wire:loading wire:target="upload">Uploading…</span>
            </button>
        </div>
    </form>

    {{-- From Lumisk --}}
    @php $adminDocs = $documents->where('uploaded_by', 'admin')->where('is_visible_to_client', true); @endphp
    @if ($adminDocs->count() > 0)
        <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
            <svg class="h-4 w-4 text-brand-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            Documents from Lumisk Technology
        </h3>
        <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($adminDocs as $doc)
                @php $isNew = in_array($doc->id, $newDocIds); @endphp
                <div wire:key="adoc-{{ $doc->id }}" class="card p-4 {{ $isNew ? 'border-l-4 border-l-brand-purple' : '' }}">
                    <div class="mb-3 flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" style="background: {{ $doc->icon_color }}1a;">
                            <svg class="h-5 w-5" style="color: {{ $doc->icon_color }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $doc->icon }}" /></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $doc->title }}</p>
                            <p class="text-xs text-gray-400">{{ $doc->file_size_formatted }} · {{ $doc->created_at->format('M d, Y') }}</p>
                        </div>
                        @if ($isNew)<span class="shrink-0 rounded-full bg-brand-purple px-2 py-0.5 text-[10px] font-medium text-white">New</span>@endif
                    </div>
                    <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">{{ $doc->category_label }}@if ($doc->description) · {{ Str::limit($doc->description, 60) }}@endif</p>
                    <div class="flex gap-2">
                        @if ($doc->is_image || $doc->is_pdf)
                            <a href="{{ route('portal.documents.preview', $doc) }}" target="_blank" class="btn-secondary flex-1 justify-center !py-1.5 text-xs">Preview</a>
                        @endif
                        <a href="{{ route('portal.documents.download', $doc) }}" class="btn-primary flex-1 justify-center !py-1.5 text-xs">Download</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- My uploads --}}
    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
        <svg class="h-4 w-4 text-brand-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
        My Uploads
    </h3>
    <div class="space-y-2">
        @forelse ($documents->where('uploaded_by', 'client') as $doc)
            <div wire:key="cdoc-{{ $doc->id }}" class="card flex items-center gap-3 p-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" style="background: {{ $doc->icon_color }}1a;">
                    <svg class="h-5 w-5" style="color: {{ $doc->icon_color }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $doc->icon }}" /></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $doc->title }}</p>
                    <p class="mt-0.5 flex flex-wrap gap-x-3 text-xs text-gray-400">
                        <span>{{ $doc->category_label }}</span><span>{{ $doc->file_size_formatted }}</span><span>{{ $doc->created_at->format('M d, Y') }}</span>
                        @if ($doc->project)<span>📁 {{ $doc->project->name }}</span>@endif
                    </p>
                    @if ($doc->client_note)<p class="mt-1 text-xs italic text-gray-500 dark:text-gray-400">"{{ $doc->client_note }}"</p>@endif
                </div>
                <div class="shrink-0 text-right text-[10px]">
                    @if ($doc->viewed_by_admin)<span class="text-green-600 dark:text-green-400">✓ Seen by team</span>@else<span class="text-gray-400">Pending review</span>@endif
                </div>
                <button wire:click="deleteDocument({{ $doc->id }})" wire:confirm="Delete this document?" class="shrink-0 rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        @empty
            <div class="card border border-dashed p-10 text-center text-sm text-gray-400">No documents uploaded yet. Use the upload area above to share files with us.</div>
        @endforelse
    </div>
</div>
