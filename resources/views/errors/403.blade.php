<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Denied — {{ config('app.name') }}</title>
    <script>if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased">
    <div class="flex min-h-full items-center justify-center bg-gray-50 px-6 dark:bg-ink-900">
        <div class="w-full max-w-md text-center">
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl bg-red-100 dark:bg-red-900/30">
                <svg class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Access Denied</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ $exception?->getMessage() ?: "You don't have permission to access this page." }}
            </p>
            <p class="mt-1 text-xs text-gray-400">If you need access, please contact your super admin.</p>
            <div class="mt-6 flex justify-center gap-3">
                <a href="javascript:history.back()" class="btn-secondary">Go Back</a>
                <a href="{{ url('/admin/dashboard') }}" class="btn-primary">Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
