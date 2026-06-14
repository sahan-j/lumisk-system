<x-layouts.guest title="Reset Password">
    <div class="mb-8">
        <span class="text-sm font-medium uppercase tracking-widest text-brand-purple">Client Portal</span>
        <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">Reset Password</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter your new password below.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('portal.password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div>
            <label for="password" class="form-label">New Password</label>
            <div class="relative" x-data="{ show: false }">
                <input x-ref="input" id="password" name="password" type="password" required autofocus
                       class="form-input-base pr-10" placeholder="Minimum 8 characters">
                <button type="button" tabindex="-1"
                        @click="show = !show; $refs.input.type = show ? 'text' : 'password'"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-purple focus:outline-none"
                        aria-label="Toggle password visibility">
                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
        </div>
        <div>
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="relative" x-data="{ show: false }">
                <input x-ref="input" id="password_confirmation" name="password_confirmation" type="password" required
                       class="form-input-base pr-10" placeholder="Repeat new password">
                <button type="button" tabindex="-1"
                        @click="show = !show; $refs.input.type = show ? 'text' : 'password'"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-purple focus:outline-none"
                        aria-label="Toggle password visibility">
                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
        </div>
        <button type="submit" class="btn-primary w-full">Reset Password</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('portal.login') }}" class="font-medium text-brand-purple hover:underline">&larr; Back to Login</a>
    </p>
</x-layouts.guest>
