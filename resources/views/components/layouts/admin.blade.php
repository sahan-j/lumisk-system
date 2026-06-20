@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' — ' : '' }}{{ config('app.name') }}</title>
    <script>
        (function () {
            function applyTheme() {
                if (localStorage.theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
            applyTheme();
            document.addEventListener('livewire:navigated', applyTheme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full antialiased">
    <div x-data="{ sidebarOpen: false }" class="min-h-full">
        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             x-transition.opacity class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-200 dark:border-ink-600 dark:bg-ink-850 lg:translate-x-0">
            <div class="flex h-16 items-center gap-2 border-b border-gray-200 px-6 dark:border-ink-600">
                <x-brand />
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto p-4">
                @php
                    $nav = [
                        ['admin.dashboard', 'Dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'dashboard.view'],
                        ['admin.clients.index', 'Clients', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z', 'clients.view'],
                        ['admin.pipeline.index', 'Pipeline', 'M9 17V9m4 8V5m4 12v-4M5 17v-2m-2 6h18a1 1 0 001-1V4a1 1 0 00-1-1H3a1 1 0 00-1 1v15a1 1 0 001 1z', 'pipeline.view'],
                        ['admin.invoices.index', 'Invoices', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'invoices.view'],
                        ['admin.estimates.index', 'Estimates', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'estimates.view'],
                        ['admin.projects.index', 'Projects', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'projects.view'],
                        ['admin.tickets.index', 'Support', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z', 'tickets.view'],
                        ['admin.expenses.index', 'Expenses', 'M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 011-1z', 'expenses.view'],
                        ['admin.subscriptions.index', 'Subscriptions', 'M7 7h10v10M17 7L7 17M4 20h16', 'subscriptions.view'],
                        ['admin.reports.index', 'Reports', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'reports.view'],
                        ['admin.saved-items.index', 'Saved Items', 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', 'invoices.view'],
                        ['admin.staff.index', 'Staff', 'M17 20h5v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z', 'staff.view'],
                        ['admin.settings.index', 'Settings', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'settings.view'],
                    ];
                @endphp
                @foreach ($nav as [$route, $label, $icon, $perm])
                    @continue (! auth()->user()->hasPermission($perm))
                    @php $active = request()->routeIs(str_replace('.index', '', $route) . '*') || request()->routeIs($route); @endphp
                    <a href="{{ route($route) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                              {{ $active
                                  ? 'nav-active'
                                  : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-ink-700' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                        </svg>
                        {{ $label }}
                        @if ($route === 'admin.tickets.index' && ($openTicketsCount ?? 0) > 0)
                            <span class="ml-auto rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-semibold text-white">{{ $openTicketsCount }}</span>
                        @endif
                        @if ($route === 'admin.subscriptions.index' && ($pastDueCount ?? 0) > 0)
                            <span class="ml-auto rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-semibold text-white">{{ $pastDueCount }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-gray-200 p-4 dark:border-ink-600">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-ink-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main column --}}
        <div class="lg:pl-64">
            <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-gray-200 bg-white/80 px-4 backdrop-blur dark:border-ink-600 dark:bg-ink-850/80 sm:px-6">
                <button @click="sidebarOpen = true" class="text-gray-500 lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="shrink-0 text-lg font-semibold text-gray-900 dark:text-white">{{ $title ?? '' }}</h1>

                <div class="ml-auto hidden flex-1 justify-center px-4 md:flex">
                    <livewire:admin.global-search />
                </div>

                <div class="ml-auto flex items-center gap-3 md:ml-0">
                    <x-theme-toggle />
                    <div class="relative border-l border-gray-200 pl-3 dark:border-ink-600" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="flex items-center gap-2">
                            @if (auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="h-8 w-8 rounded-full object-cover">
                            @else
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-brand text-sm font-semibold text-white">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                            <span class="hidden text-sm font-medium text-gray-700 dark:text-gray-200 sm:block">{{ auth()->user()->name }}</span>
                            <svg class="hidden h-4 w-4 text-gray-400 sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" x-transition
                             class="absolute right-4 mt-2 w-44 rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-ink-600 dark:bg-ink-850">
                            <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-ink-700">My Profile</a>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-ink-700">Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-toasts />
    <livewire:admin.send-email-modal />
    <livewire:admin.record-payment-modal />
    <livewire:admin.duplicate-modal />
    <livewire:admin.convert-modal />
    <livewire:admin.whatsapp-modal />
    @livewireScripts
</body>
</html>
