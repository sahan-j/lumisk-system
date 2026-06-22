<div>
    <a href="{{ route('admin.staff.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Staff
    </a>

    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">{{ $user ? 'Edit Staff Member' : 'New Staff Member' }}</h2>

    <form wire:submit="save" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left: details --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="card p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" class="form-input-base">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input wire:model="email" type="email" class="form-input-base">
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Password {{ $user ? '(leave blank to keep)' : '' }} @if(! $user)<span class="text-red-500">*</span>@endif</label>
                        <input wire:model="password" type="password" class="form-input-base" placeholder="Minimum 8 characters">
                        @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Confirm Password</label>
                        <input wire:model="password_confirmation" type="password" class="form-input-base">
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input wire:model="phone" type="text" class="form-input-base">
                    </div>
                    <div>
                        <label class="form-label">Job Title</label>
                        <input wire:model="job_title" type="text" class="form-input-base">
                    </div>
                    <div>
                        <label class="form-label">Hourly Rate</label>
                        <input wire:model="hourly_rate" type="number" step="0.01" min="0" placeholder="e.g. 3500" class="form-input-base">
                        <p class="mt-1 text-xs text-gray-400">Default billing rate for this staff member's time.</p>
                        @error('hourly_rate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Role <span class="text-red-500">*</span></label>
                        <select wire:model.live="role" class="form-input-base" @if($user && $user->isSuperAdmin()) disabled @endif>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                        </select>
                        @error('role') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <label class="flex cursor-pointer items-center gap-3">
                            <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-gold focus:ring-gold dark:border-ink-600 dark:bg-ink-800">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Account active</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: custom permissions --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Permissions</h3>
            <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">Defaults follow the selected role. Tick to override per-user.</p>
            <div class="max-h-[520px] space-y-5 overflow-y-auto pr-1">
                @forelse ($permissionGroups as $group => $permissions)
                    <div>
                        <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-brand-purple">{{ $group }}</p>
                        @foreach ($permissions as $permission)
                            @php $key = str_replace('.', '_', $permission->name); $isDefault = in_array($permission->name, $roleDefaults, true); @endphp
                            <label class="flex cursor-pointer items-center justify-between border-b border-gray-100 py-1.5 dark:border-ink-700">
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ $permission->label }}</span>
                                <span class="flex items-center gap-2">
                                    <span class="text-[10px] {{ $isDefault ? 'text-green-500' : 'text-gray-400' }}">{{ $isDefault ? 'default ✓' : 'default ✗' }}</span>
                                    <input type="checkbox" wire:model="permState.{{ $key }}" class="rounded border-gray-300 text-gold focus:ring-gold dark:border-ink-600 dark:bg-ink-800">
                                </span>
                            </label>
                        @endforeach
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                        <p>No permissions found.</p>
                        <p class="mt-1 text-xs">Run: <code class="rounded bg-gray-100 px-1 dark:bg-ink-700">php artisan db:seed --class=PermissionsSeeder</code></p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="flex justify-end gap-3 lg:col-span-3">
            <a href="{{ route('admin.staff.index') }}" wire:navigate class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <span wire:loading.remove wire:target="save">{{ $user ? 'Update' : 'Create' }} Staff</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>
