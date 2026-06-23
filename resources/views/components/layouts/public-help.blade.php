@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' — ' : '' }}Help Center — {{ config('app.name') }}</title>
    <script>
        if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>{!! \App\Helpers\ThemeHelper::getCss() !!}</style>
</head>
<body class="h-full antialiased">
    <div class="min-h-full">
        <header class="border-b border-gray-200 bg-white dark:border-ink-600 dark:bg-ink-850">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between gap-6 px-4 sm:px-6">
                <a href="{{ route('public.kb.index') }}"><x-brand /></a>
                <div class="flex items-center gap-3">
                    <x-theme-toggle />
                    <a href="{{ route('portal.login') }}" class="rounded-lg bg-gradient-brand px-4 py-2 text-sm font-medium text-white">Client Login</a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
            {{ $slot }}
        </main>

        <footer class="border-t border-gray-200 py-6 text-center text-xs text-gray-400 dark:border-ink-600">
            © {{ date('Y') }} {{ config('app.name') }} · <a href="{{ route('portal.login') }}" class="hover:text-brand-purple">Client Portal</a>
        </footer>
    </div>

    <x-toasts />
    @livewireScripts
</body>
</html>
