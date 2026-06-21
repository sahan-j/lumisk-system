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
    <div x-data="{
        sidebarOpen: false,
        collapsed: localStorage.getItem('lumisk_sidebar') === '1',
        groups: {
            overview: localStorage.getItem('lumisk_grp_overview') !== '0',
            crm:      localStorage.getItem('lumisk_grp_crm')      !== '0',
            ops:      localStorage.getItem('lumisk_grp_ops')       !== '0',
            products: localStorage.getItem('lumisk_grp_products')  !== '0',
            finance:  localStorage.getItem('lumisk_grp_finance')   !== '0',
            admin:    localStorage.getItem('lumisk_grp_admin')     !== '0',
        },
        toggleSidebar() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('lumisk_sidebar', this.collapsed ? '1' : '0');
        },
        toggleGroup(name) {
            if (this.collapsed) return;
            this.groups[name] = !this.groups[name];
            localStorage.setItem('lumisk_grp_' + name, this.groups[name] ? '1' : '0');
        }
    }" class="min-h-full">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             x-transition.opacity class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-40 flex flex-col bg-[#0f0f0f] border-r border-[#1e1e1e] transition-all duration-200 overflow-hidden w-[220px] lg:translate-x-0"
               :class="[
                   sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                   collapsed ? 'lg:w-[52px]' : 'lg:w-[220px]'
               ]">

            {{-- Logo + collapse toggle --}}
            <div class="flex h-14 items-center border-b border-[#1e1e1e] px-3 flex-shrink-0">
                <div x-show="!collapsed" class="flex-1 min-w-0 overflow-hidden">
                    <x-brand />
                </div>
                <div x-show="collapsed" class="flex flex-1 items-center justify-center">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-brand text-white font-bold text-base">L</span>
                </div>
                <button @click="toggleSidebar()"
                        class="flex-shrink-0 text-gray-500 hover:text-white transition-colors p-1 rounded ml-1">
                    <svg :class="{ 'rotate-180': collapsed }"
                         class="h-[18px] w-[18px] transition-transform duration-200"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto overflow-x-hidden p-2">
                @php
                    $navGroups = [
                        ['overview', 'Overview', [
                            ['admin.dashboard', 'Dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'dashboard.view'],
                        ]],
                        ['crm', 'CRM & Sales', [
                            ['admin.clients.index',      'Clients',      'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z', 'clients.view'],
                            ['admin.pipeline.index',     'Pipeline',     'M9 17V9m4 8V5m4 12v-4M5 17v-2m-2 6h18a1 1 0 001-1V4a1 1 0 00-1-1H3a1 1 0 00-1 1v15a1 1 0 001 1z', 'pipeline.view'],
                            ['admin.estimates.index',    'Estimates',    'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'estimates.view'],
                            ['admin.invoices.index',     'Invoices',     'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'invoices.view'],
                            ['admin.credit-notes.index', 'Credit Notes', 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z', 'credit-notes.view'],
                        ]],
                        ['ops', 'Operations', [
                            ['admin.projects.index', 'Projects', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'projects.view'],
                            ['admin.tickets.index',  'Support',  'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z', 'tickets.view'],
                        ]],
                        ['products', 'Products & Services', [
                            ['admin.products.index',      'Products',      'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'products.view'],
                            ['admin.saved-items.index',   'Saved Items',   'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', 'invoices.view'],
                            ['admin.subscriptions.index', 'Subscriptions', 'M7 7h10v10M17 7L7 17M4 20h16', 'subscriptions.view'],
                        ]],
                        ['finance', 'Finance', [
                            ['admin.expenses.index', 'Expenses', 'M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 011-1z', 'expenses.view'],
                            ['admin.reports.index',  'Reports',  'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'reports.view'],
                        ]],
                        ['admin', 'Administration', [
                            ['admin.staff.index',    'Staff',    'M17 20h5v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z', 'staff.view'],
                            ['admin.settings.index', 'Settings', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'settings.view'],
                        ]],
                    ];
                @endphp

                @foreach ($navGroups as [$groupKey, $groupLabel, $items])
                    @php
                        $visibleItems = array_filter($items, fn($item) => auth()->user()->hasPermission($item[3]));
                    @endphp
                    @if (count($visibleItems) > 0)
                        <div class="mb-1">
                            {{-- Group header (hidden in icon-only mode) --}}
                            <div x-show="!collapsed"
                                 @click="toggleGroup('{{ $groupKey }}')"
                                 class="flex items-center cursor-pointer px-2 py-1 select-none">
                                <span style="font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #4a4a4a;">{{ $groupLabel }}</span>
                                <svg :class="groups.{{ $groupKey }} ? 'rotate-0' : '-rotate-90'"
                                     class="ml-auto h-3 w-3 flex-shrink-0 text-gray-600 transition-transform duration-200"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            {{-- Group items: always show in icon-only mode; respect group state otherwise --}}
                            <div x-show="collapsed || groups.{{ $groupKey }}"
                                 x-transition:enter="transition-[opacity,max-height] duration-200 ease-out overflow-hidden"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition-[opacity] duration-150 ease-in"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0">
                                @foreach ($visibleItems as [$route, $label, $icon, $perm])
                                    @php $active = request()->routeIs(str_replace('.index', '', $route) . '*') || request()->routeIs($route); @endphp
                                    <a href="{{ route($route) }}"
                                       x-data="{ tip: false }"
                                       @mouseenter="if(collapsed) tip = true"
                                       @mouseleave="tip = false"
                                       class="relative flex items-center gap-2.5 rounded-lg px-2 py-[7px] text-sm font-medium transition-colors mb-0.5
                                              {{ $active
                                                  ? 'bg-[#1e1e1e] text-white'
                                                  : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-gray-100' }}">
                                        <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                                        </svg>
                                        <span x-show="!collapsed" class="truncate leading-none">{{ $label }}</span>
                                        @if ($route === 'admin.tickets.index' && ($openTicketsCount ?? 0) > 0)
                                            <span x-show="!collapsed" class="ml-auto rounded-full bg-red-500 px-1.5 py-0.5 text-[9px] font-semibold text-white flex-shrink-0">{{ $openTicketsCount }}</span>
                                        @endif
                                        @if ($route === 'admin.subscriptions.index' && ($pastDueCount ?? 0) > 0)
                                            <span x-show="!collapsed" class="ml-auto rounded-full bg-red-500 px-1.5 py-0.5 text-[9px] font-semibold text-white flex-shrink-0">{{ $pastDueCount }}</span>
                                        @endif
                                        @if ($route === 'admin.products.index' && ($lowStockCount ?? 0) > 0)
                                            <span x-show="!collapsed" class="ml-auto rounded-full bg-amber-500 px-1.5 py-0.5 text-[9px] font-semibold text-white flex-shrink-0">{{ $lowStockCount }}</span>
                                        @endif
                                        {{-- Tooltip: only visible in icon-only mode on hover --}}
                                        <span x-show="tip" x-cloak
                                              class="pointer-events-none absolute left-[44px] top-1/2 -translate-y-1/2 z-50 whitespace-nowrap rounded-md bg-[#1e1e1e] border border-[#2a2a2a] px-2.5 py-1.5 text-xs font-medium text-white shadow-lg">
                                            {{ $label }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>

            {{-- Sign out --}}
            <div class="border-t border-[#1e1e1e] p-2 flex-shrink-0">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                            x-data="{ tip: false }"
                            @mouseenter="if(collapsed) tip = true"
                            @mouseleave="tip = false"
                            class="relative flex w-full items-center gap-2.5 rounded-lg px-2 py-[7px] text-sm font-medium text-gray-400 hover:bg-[#1a1a1a] hover:text-gray-100 transition-colors">
                        <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span x-show="!collapsed" class="leading-none">Sign out</span>
                        <span x-show="tip" x-cloak
                              class="pointer-events-none absolute left-[44px] top-1/2 -translate-y-1/2 z-50 whitespace-nowrap rounded-md bg-[#1e1e1e] border border-[#2a2a2a] px-2.5 py-1.5 text-xs font-medium text-white shadow-lg">
                            Sign out
                        </span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main column --}}
        <div class="transition-all duration-200"
             :class="collapsed ? 'lg:pl-[52px]' : 'lg:pl-[220px]'">
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
