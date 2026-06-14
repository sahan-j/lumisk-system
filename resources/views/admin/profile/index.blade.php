<x-layouts.admin title="My Profile">
    <x-alert />

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Profile Details --}}
        <div class="card p-6">
            <h2 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">Profile Details</h2>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Update your personal information.</p>

            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-5"
                  x-data="{ preview: null }">
                @csrf
                @method('PUT')

                {{-- Avatar --}}
                <div class="flex items-center gap-4">
                    <template x-if="preview">
                        <img :src="preview" alt="Avatar preview" class="h-20 w-20 rounded-full object-cover ring-2 ring-brand-purple/30">
                    </template>
                    <template x-if="!preview">
                        <div>
                            @if ($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="h-20 w-20 rounded-full object-cover ring-2 ring-brand-purple/30">
                            @else
                                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-brand text-2xl font-semibold text-white">
                                    {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </template>
                    <div>
                        <label class="btn-secondary cursor-pointer text-xs">
                            Change Photo
                            <input type="file" name="avatar" accept="image/jpeg,image/png" class="hidden"
                                   @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null">
                        </label>
                        <p class="mt-1.5 text-xs text-gray-400">JPG or PNG, max 2 MB.</p>
                    </div>
                </div>

                <div>
                    <label for="name" class="form-label">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="form-input-base">
                </div>
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="form-input-base">
                </div>
                <div>
                    <label for="phone" class="form-label">Phone <span class="text-gray-400">(optional)</span></label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="form-input-base">
                </div>
                <div>
                    <label for="job_title" class="form-label">Job Title <span class="text-gray-400">(optional)</span></label>
                    <input id="job_title" name="job_title" type="text" value="{{ old('job_title', $user->job_title) }}" class="form-input-base">
                </div>

                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="card p-6">
            <h2 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">Change Password</h2>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Use a strong password you don't reuse elsewhere.</p>

            <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="form-label">Current Password</label>
                    <input id="current_password" name="current_password" type="password" required class="form-input-base">
                </div>
                <div>
                    <label for="password" class="form-label">New Password</label>
                    <input id="password" name="password" type="password" required class="form-input-base" placeholder="Minimum 8 characters">
                </div>
                <div>
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="form-input-base">
                </div>

                <button type="submit" class="btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</x-layouts.admin>
