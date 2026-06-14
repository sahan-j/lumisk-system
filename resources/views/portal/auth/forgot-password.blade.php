<x-layouts.guest title="Forgot Password">
    <div class="mb-8">
        <span class="text-sm font-medium uppercase tracking-widest text-brand-purple">Client Portal</span>
        <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">Forgot Password</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter your email and we'll send you a reset link.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-950/40 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    @error('email')
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
            {{ $message }}
        </div>
    @enderror

    <form method="POST" action="{{ route('portal.password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="form-input-base" placeholder="you@company.com">
        </div>
        <button type="submit" class="btn-primary w-full">Send Reset Link</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('portal.login') }}" class="font-medium text-brand-purple hover:underline">&larr; Back to Login</a>
    </p>
</x-layouts.guest>
