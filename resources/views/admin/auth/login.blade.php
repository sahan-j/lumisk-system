<x-layouts.guest title="Admin Login">
    <div class="mb-8">
        <span class="text-sm font-medium uppercase tracking-widest text-brand-purple">Admin Panel</span>
        <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">Sign in to your account</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Welcome back. Please enter your details.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="form-input-base" placeholder="admin@lumisktechnology.com">
        </div>
        <div>
            <label for="password" class="form-label">Password</label>
            <div class="relative" x-data="{ show: false }">
                <input id="password" name="password" type="password" :type="show ? 'text' : 'password'" required
                       class="form-input-base pr-10" placeholder="••••••••">
                <button type="button" tabindex="-1" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-purple focus:outline-none"
                        :aria-label="show ? 'Hide password' : 'Show password'">
                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-purple focus:ring-brand-purple dark:border-ink-600 dark:bg-ink-800">
                Remember me
            </label>
        </div>
        <button type="submit" class="btn-primary w-full">Sign in</button>
    </form>

    <p class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
        Are you a client? <a href="{{ route('portal.login') }}" class="font-medium text-brand-purple hover:underline">Client portal &rarr;</a>
    </p>
</x-layouts.guest>
