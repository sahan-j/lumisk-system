@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' — ' : '' }}{{ config('app.name') }} Portal</title>
    <script>
        if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>{!! \App\Helpers\ThemeHelper::getCss() !!}</style>
</head>
<body class="h-full antialiased">
    <div class="min-h-full" x-data="{ mobileNav: false }">
        <header class="border-b border-gray-200 bg-white dark:border-ink-600 dark:bg-ink-850">
            <div class="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4 sm:px-6">
                <a href="{{ route('portal.dashboard') }}"><x-brand /></a>

                @php
                    $links = [
                        'portal.dashboard' => 'Dashboard',
                        'portal.invoices.index' => 'Invoices',
                        'portal.estimates.index' => 'Estimates',
                    ];
                    // Show Credit Notes only when the client actually has some.
                    if (\App\Models\CreditNote::where('client_id', auth('client')->id())->where('status', '!=', 'draft')->exists()) {
                        $links['portal.credit-notes.index'] = 'Credit Notes';
                    }
                    $links += [
                        'portal.subscriptions.index' => 'Subscriptions',
                        'portal.projects.index' => 'Projects',
                        'portal.documents.index' => 'Documents',
                        'portal.tickets.index' => 'Support',
                        'portal.kb.index' => 'Help',
                    ];
                    // Drop any link whose route isn't registered (stale cache / not-yet-deployed) so the page never 500s.
                    $links = array_filter($links, fn ($r) => \Illuminate\Support\Facades\Route::has($r), ARRAY_FILTER_USE_KEY);
                @endphp
                <nav class="hidden items-center gap-1 md:flex">
                    @foreach ($links as $route => $label)
                        <a href="{{ route($route) }}"
                           class="rounded-lg px-3 py-2 text-sm font-medium transition
                                  {{ request()->routeIs(str_replace('.index','',$route).'*')
                                      ? 'bg-gray-100 text-navy dark:bg-ink-700 dark:text-brand-purple'
                                      : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white' }}">
                            {{ $label }}@if ($route === 'portal.documents.index' && ($newFromLumiskCount ?? 0) > 0)<span class="ml-1 rounded-full bg-brand-purple px-1.5 py-0.5 text-[10px] font-semibold text-white">{{ $newFromLumiskCount }}</span>@endif
                        </a>
                    @endforeach
                </nav>

                <div class="ml-auto flex items-center gap-3">
                    <livewire:notification-bell guard="client" />
                    <x-theme-toggle />
                    <div class="relative hidden sm:block" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-brand text-sm font-semibold text-white">
                                {{ strtoupper(substr(auth('client')->user()->name ?? 'C', 0, 1)) }}
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ auth('client')->user()->name }}</span>
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" x-transition
                             class="absolute right-0 z-30 mt-2 w-44 rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-ink-600 dark:bg-ink-850">
                            <a href="{{ route('portal.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-ink-700">My Profile</a>
                            <form method="POST" action="{{ route('portal.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-ink-700">Sign out</button>
                            </form>
                        </div>
                    </div>
                    <button @click="mobileNav = !mobileNav" class="text-gray-500 md:hidden">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
            {{-- Mobile nav --}}
            <nav x-show="mobileNav" x-cloak class="border-t border-gray-200 px-4 py-2 dark:border-ink-600 md:hidden">
                @foreach ($links as $route => $label)
                    <a href="{{ route($route) }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-ink-700">{{ $label }}</a>
                @endforeach
                <a href="{{ route('portal.profile') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-ink-700">My Profile</a>
                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-ink-700">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            @if ($title)
                <h1 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
            @endif
            {{ $slot }}
        </main>
    </div>

    <x-toasts />
    @livewireScripts
</body>
</html>
