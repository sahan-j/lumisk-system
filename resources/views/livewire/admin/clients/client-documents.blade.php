<div>
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('admin.clients.show', $client) }}" wire:navigate class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            {{ $client->name }}
        </a>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Documents — {{ $client->name }}</h2>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">Total</p><p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">From Client</p><p class="mt-1 text-2xl font-semibold text-brand-purple">{{ $stats['from_client'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">From Us</p><p class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">{{ $stats['from_admin'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">Unseen by Client</p><p class="mt-1 text-2xl font-semibold text-amber-500">{{ $stats['unviewed_by_client'] }}</p></div>
    </div>

    {{-- Upload --}}
    @permission('clients.edit')
    <form wire:submit="upload" class="card mb-6 p-5">
        <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Share a Document with {{ $client->name }}</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="form-label">Files <span class="text-red-500">*</span></label>
                <input wire:model="files" type="file" multiple class="form-input-base text-sm">
                @error('files.*') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                @error('files') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Category</label>
                <select wire:model="category" class="form-input-base">
                    <option value="contract">📄 Contract</option>
                    <option value="requirements">📋 Requirements</option>
                    <option value="content">✏️ Content</option>
                    <option value="design_feedback">🎨 Design Feedback</option>
                    <option value="other">📎 Other</option>
                </select>
            </div>
            <div>
                <label class="form-label">Related Project</label>
                <select wire:model="projectId" class="form-input-base">
                    <option value="">None</option>
                    @foreach ($projects as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label class="form-label">Description (shown to client)</label>
                <textarea wire:model="description" rows="2" class="form-input-base text-sm"></textarea>
            </div>
            <label class="inline-flex cursor-pointer items-center gap-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                <input wire:model="isVisibleToClient" type="checkbox" class="rounded border-gray-300 text-brand-purple focus:ring-brand-purple dark:border-ink-600 dark:bg-ink-800">
                Visible to client
            </label>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="upload,files">
                <span wire:loading.remove wire:target="upload">Upload &amp; Share</span>
                <span wire:loading wire:target="upload">Uploading…</span>
            </button>
        </div>
    </form>
    @endpermission

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">File</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">By</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($documents as $doc)
                        <tr wire:key="doc-{{ $doc->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg" style="background: {{ $doc->icon_color }}1a;">
                                        <svg class="h-4 w-4" style="color: {{ $doc->icon_color }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $doc->icon }}" /></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $doc->title }}</p>
                                        <p class="text-xs text-gray-400">{{ $doc->file_size_formatted }}@if ($doc->client_note) · "{{ Str::limit($doc->client_note, 40) }}"@endif</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $doc->category_label }}</td>
                            <td class="px-5 py-3">
                                @if ($doc->uploaded_by === 'client')
                                    <span class="rounded-full bg-brand-purple/10 px-2 py-0.5 text-[10px] font-medium text-brand-purple">Client</span>
                                @else
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">Us</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $doc->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-xs">
                                @if ($doc->uploaded_by === 'admin')
                                    @if (! $doc->is_visible_to_client)<span class="text-gray-400">Hidden</span>
                                    @elseif ($doc->viewed_by_client)<span class="text-green-600 dark:text-green-400">✓ Seen</span>
                                    @else<span class="text-amber-500">Sent</span>@endif
                                @else
                                    <span class="text-gray-400">Received</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($doc->uploaded_by === 'admin')
                                        <button wire:click="toggleVisibility({{ $doc->id }})" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="{{ $doc->is_visible_to_client ? 'Hide from client' : 'Show to client' }}">
                                            @if ($doc->is_visible_to_client)
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                                            @endif
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.documents.download', $doc) }}" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Download">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    </a>
                                    @permission('clients.edit')
                                    <button wire:click="deleteDocument({{ $doc->id }})" wire:confirm="Delete this document?" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">No documents yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
