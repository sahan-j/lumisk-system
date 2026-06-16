<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Staff Members</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage team accounts and role-based access.</p>
        </div>
        @permission('staff.create')
            <a href="{{ route('admin.staff.create') }}" wire:navigate class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Add Staff
            </a>
        @endpermission
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Staff</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalStaff }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Active</span><p class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">{{ $activeCount }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Admins</span><p class="mt-2 text-2xl font-semibold text-brand-purple">{{ $adminCount }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Staff Role</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $staffCount }}</p></div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Last Login</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    {{-- Current logged-in user (always shown at top) --}}
                    <tr class="bg-brand-purple/5 dark:bg-brand-purple/10">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if ($currentUser->avatar)
                                    <img src="{{ asset('storage/' . $currentUser->avatar) }}" alt="Avatar" class="h-9 w-9 rounded-full object-cover">
                                @else
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold text-white" style="background-color: {{ $currentUser->role_color }}">
                                        {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                                    </span>
                                @endif
                                <div>
                                    <p class="flex items-center gap-2 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $currentUser->name }}
                                        <span class="rounded-full bg-brand-purple/10 px-2 py-0.5 text-[10px] font-semibold text-brand-purple">You</span>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $currentUser->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white" style="background-color: {{ $currentUser->role_color }}">{{ $currentUser->role_label }}</span>
                        </td>
                        <td class="px-5 py-3"><x-status-badge color="green" label="Active" /></td>
                        <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $currentUser->last_login_at?->diffForHumans() ?? 'Now' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.profile') }}" wire:navigate class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Edit Profile">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>

                    {{-- Section label when other members exist --}}
                    @if ($staff->isNotEmpty())
                        <tr class="bg-gray-50 dark:bg-ink-800/50">
                            <td colspan="5" class="px-5 py-1.5 text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Other Team Members</td>
                        </tr>
                    @endif

                    @forelse ($staff as $member)
                        <tr wire:key="staff-{{ $member->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($member->avatar)
                                        <img src="{{ asset('storage/' . $member->avatar) }}" alt="Avatar" class="h-9 w-9 rounded-full object-cover">
                                    @else
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold text-white" style="background-color: {{ $member->role_color }}">
                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                        </span>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $member->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white" style="background-color: {{ $member->role_color }}">{{ $member->role_label }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @if ($member->is_active)
                                    <x-status-badge color="green" label="Active" />
                                @else
                                    <x-status-badge color="red" label="Inactive" />
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $member->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @permission('staff.edit')
                                        <a href="{{ route('admin.staff.edit', $member) }}" wire:navigate class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                        @unless ($member->isSuperAdmin())
                                            <button wire:click="toggleActive({{ $member->id }})" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="{{ $member->is_active ? 'Deactivate' : 'Activate' }}">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" /></svg>
                                            </button>
                                        @endunless
                                    @endpermission
                                    @permission('staff.delete')
                                        @unless ($member->isSuperAdmin())
                                            <button wire:click="confirmDelete({{ $member->id }})" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        @endunless
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">
                            No other staff members yet.
                            @permission('staff.create')
                                <a href="{{ route('admin.staff.create') }}" wire:navigate class="ml-1 text-brand-purple hover:underline">Add your first team member →</a>
                            @endpermission
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($confirmingDelete)
        <x-app-modal title="Remove staff member?" close="$set('confirmingDelete', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">This permanently removes the account and its custom permissions. This cannot be undone.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Remove</button>
            </div>
        </x-app-modal>
    @endif
</div>
