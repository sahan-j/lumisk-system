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
                    <div class="relative" x-data="{ show: false }">
                        <input id="current_password" name="current_password" :type="show ? 'text' : 'password'" required class="form-input-base pr-10">
                        <button type="button" tabindex="-1" @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-purple focus:outline-none"
                                :aria-label="show ? 'Hide password' : 'Show password'">
                            <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="password" class="form-label">New Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <input id="password" name="password" :type="show ? 'text' : 'password'" required class="form-input-base pr-10" placeholder="Minimum 8 characters">
                        <button type="button" tabindex="-1" @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-purple focus:outline-none"
                                :aria-label="show ? 'Hide password' : 'Show password'">
                            <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" required class="form-input-base pr-10">
                        <button type="button" tabindex="-1" @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-purple focus:outline-none"
                                :aria-label="show ? 'Hide password' : 'Show password'">
                            <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</x-layouts.portal>
