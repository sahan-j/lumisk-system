<x-layouts.portal title="My Profile">
    <x-alert />

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Profile Details --}}
        <div class="card p-6">
            <h2 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">Profile Details</h2>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Update your contact information.</p>

            <form method="POST" action="{{ route('portal.profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="form-label">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $client->name) }}" required class="form-input-base">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <p class="rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:bg-ink-800 dark:text-gray-300">{{ $client->email }}</p>
                    <p class="mt-1.5 text-xs text-gray-400">Contact admin to change email.</p>
                </div>
                <div>
                    <label for="phone" class="form-label">Phone <span class="text-gray-400">(optional)</span></label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $client->phone) }}" class="form-input-base">
                </div>
                <div>
                    <label for="address" class="form-label">Address <span class="text-gray-400">(optional)</span></label>
                    <textarea id="address" name="address" rows="3" class="form-input-base">{{ old('address', $client->address) }}</textarea>
                </div>

                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="card p-6">
            <h2 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">Change Password</h2>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Use a strong password you don't reuse elsewhere.</p>

            <form method="POST" action="{{ route('portal.profile.password') }}" class="space-y-5">
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
</x-layouts.portal>
