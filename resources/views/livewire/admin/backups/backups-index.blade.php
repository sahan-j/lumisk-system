<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Database Backups</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Download or restore point-in-time MySQL backups.</p>
        </div>
        @permission('settings.edit')
            <button wire:click="createBackup" wire:loading.attr="disabled" wire:target="createBackup" class="btn-primary">
                <svg wire:loading.remove wire:target="createBackup" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                <span wire:loading.remove wire:target="createBackup">Create Backup Now</span>
                <span wire:loading wire:target="createBackup">Creating…</span>
            </button>
        @endpermission
    </div>

    {{-- Auto-backup info --}}
    <div class="mb-5 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-900/40 dark:bg-green-900/10">
        <svg class="h-5 w-5 shrink-0 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">Automatic backups enabled</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Daily at 2:00 AM · Keeping the last 30 backups</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Filename</th>
                        <th class="px-5 py-3">Size</th>
                        <th class="px-5 py-3">Created</th>
                        <th class="px-5 py-3">Age</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($backups as $backup)
                        <tr wire:key="backup-{{ $backup['filename'] }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs text-gray-900 dark:text-white">{{ $backup['filename'] }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $backup['size_formatted'] }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $backup['created_at']->format('M d, Y H:i') }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $backup['created_at']->diffForHumans() }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.backups.download', $backup['filename']) }}" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Download">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    </a>
                                    @permission('settings.edit')
                                        <button wire:click="deleteBackup('{{ $backup['filename'] }}')" wire:confirm="Delete this backup permanently?" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">No backups yet. Click “Create Backup Now” to start.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
