<div>
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Client Documents</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Files uploaded by clients across all accounts.</p>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card p-5"><p class="text-sm text-gray-500 dark:text-gray-400">Unread Uploads</p><p class="mt-1 text-2xl font-semibold text-brand-purple">{{ $stats['unread'] }}</p></div>
        <div class="card p-5"><p class="text-sm text-gray-500 dark:text-gray-400">Uploaded Today</p><p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_today'] }}</p></div>
        <div class="card p-5"><p class="text-sm text-gray-500 dark:text-gray-400">Clients with Uploads</p><p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['clients_with_uploads'] }}</p></div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <select wire:model.live="filter" class="form-input-base">
            <option value="unread">Unread only</option>
            <option value="all">All client uploads</option>
        </select>
        <select wire:model.live="category" class="form-input-base">
            <option value="">All categories</option>
            @foreach ($categories as $cat)<option value="{{ $cat }}">{{ ucwords(str_replace('_', ' ', $cat)) }}</option>@endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">File</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($documents as $doc)
                        <tr wire:key="adoc-{{ $doc->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg" style="background: {{ $doc->icon_color }}1a;">
                                        <svg class="h-4 w-4" style="color: {{ $doc->icon_color }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $doc->icon }}" /></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $doc->title }}</p>
                                        <p class="text-xs text-gray-400">{{ $doc->file_size_formatted }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-sm">
                                <a href="{{ route('admin.clients.documents', $doc->client_id) }}" wire:navigate class="font-medium text-gray-900 hover:text-brand-purple dark:text-white">{{ $doc->client?->name ?? '—' }}</a>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $doc->category_label }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $doc->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-xs">
                                @if ($doc->viewed_by_admin)<span class="text-green-600 dark:text-green-400">✓ Viewed</span>@else<span class="rounded-full bg-brand-purple/10 px-2 py-0.5 font-medium text-brand-purple">New</span>@endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.documents.download', $doc) }}" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Download">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    </a>
                                    <a href="{{ route('admin.clients.documents', $doc->client_id) }}" wire:navigate class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Open client documents">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">No documents found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $documents->links() }}</div>
</div>
