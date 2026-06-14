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
            <input id="password" name="password" type="password" required autofocus
                   class="form-input-base" placeholder="Minimum 8 characters">
        </div>
        <div>
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   class="form-input-base" placeholder="Repeat new password">
        </div>
        <button type="submit" class="btn-primary w-full">Reset Password</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('portal.login') }}" class="font-medium text-brand-purple hover:underline">&larr; Back to Login</a>
    </p>
</x-layouts.guest>
