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
            <input id="password" name="password" type="password" required
                   class="form-input-base" placeholder="••••••••">
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
