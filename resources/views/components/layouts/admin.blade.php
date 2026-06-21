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

    {{-- Sidebar styles — plain CSS so they're always applied, no Tailwind JIT dependency --}}
    <style>
        /* Sidebar shell */
        .ls-aside { background:#111118; border-right:1px solid #1f1f2e; display:flex; flex-direction:column; height:100%; overflow:hidden; flex-shrink:0; transition:width 0.22s cubic-bezier(.4,0,.2,1); }

        /* Logo bar */
        .ls-logo-bar { display:flex; align-items:center; height:58px; border-bottom:1px solid #1f1f2e; flex-shrink:0; overflow:hidden; padding:0 12px; gap:10px; }
        .ls-logo-bar-collapsed { display:flex; align-items:center; justify-content:center; height:58px; border-bottom:1px solid #1f1f2e; flex-shrink:0; width:52px; }
        .ls-logo-icon { width:30px; height:30px; border-radius:8px; background:linear-gradient(135deg,#6d5cff,#00d4ff); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:14px; color:#fff; flex-shrink:0; }
        .ls-logo-text { font-size:16px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; }
        .ls-logo-text span { color:#6d5cff; }
        .ls-toggle-btn { margin-left:auto; flex-shrink:0; background:none; border:none; cursor:pointer; color:#4a4a6a; padding:5px; border-radius:6px; display:flex; align-items:center; justify-content:center; transition:color 0.15s, background 0.15s; }
        .ls-toggle-btn:hover { color:#fff; background:rgba(255,255,255,0.07); }
        .ls-toggle-icon { width:16px; height:16px; transition:transform 0.22s cubic-bezier(.4,0,.2,1); }

        /* Nav scroll area */
        .ls-nav { flex:1; overflow-y:auto; overflow-x:hidden; padding:10px 8px; scrollbar-width:thin; scrollbar-color:#2a2a3a transparent; }
        .ls-nav::-webkit-scrollbar { width:3px; }
        .ls-nav::-webkit-scrollbar-thumb { background:#2a2a3a; border-radius:2px; }

        /* Group */
        .ls-group { margin-bottom:4px; }
        .ls-group-header { display:flex; align-items:center; padding:9px 8px 4px; cursor:pointer; user-select:none; gap:6px; }
        .ls-group-label { font-size:9px; text-transform:uppercase; letter-spacing:1.6px; font-weight:700; color:#7868e8; white-space:nowrap; }
        .ls-group-arrow { margin-left:auto; font-size:9px; color:#7868e8; transition:transform 0.2s ease; flex-shrink:0; line-height:1; opacity:0.7; }
        .ls-divider { border:none; border-top:1px solid #1f1f2e; margin:5px 0; }

        /* Nav item */
        .ls-item { display:flex; align-items:center; gap:10px; padding:8px 9px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; color:#7c7c9a; position:relative; transition:background 0.15s, color 0.15s; margin-bottom:1px; border-left:2px solid transparent; }
        .ls-item:hover { background:rgba(255,255,255,0.05); color:#c8c8e0; }
        .ls-item.ls-active { background:linear-gradient(135deg,rgba(109,92,255,0.18),rgba(0,212,255,0.08)); color:#fff; border-left-color:#6d5cff; }
        .ls-item-icon { width:18px; height:18px; flex-shrink:0; color:#5a5a7a; transition:color 0.15s; }
        .ls-item:hover .ls-item-icon { color:#a5b4fc; }
        .ls-active .ls-item-icon { color:#a5b4fc; }
        .ls-item-label { white-space:nowrap; overflow:hidden; line-height:1.2; }
        .ls-badge { margin-left:auto; flex-shrink:0; display:inline-flex; align-items:center; justify-content:center; min-width:18px; height:17px; padding:0 5px; border-radius:10px; font-size:9px; font-weight:700; color:#fff; }

        /* Tooltip (icon-only mode) */
        .ls-tooltip { position:absolute; left:46px; top:50%; transform:translateY(-50%); z-index:9999; background:#1a1a2e; border:1px solid #2a2a4a; color:#e2e2ff; font-size:12px; font-weight:500; padding:6px 12px; border-radius:7px; white-space:nowrap; pointer-events:none; box-shadow:0 8px 24px rgba(0,0,0,0.5); }

        /* Sign-out row */
        .ls-signout { border-top:1px solid #1f1f2e; padding:8px; flex-shrink:0; }
        .ls-signout-btn { display:flex; width:100%; align-items:center; gap:10px; padding:8px 9px; border-radius:8px; background:none; border:none; cursor:pointer; font-size:13px; font-weight:500; color:#7c7c9a; transition:background 0.15s, color 0.15s; text-align:left; }
        .ls-signout-btn:hover { background:rgba(255,255,255,0.05); color:#c8c8e0; }
        .ls-signout-btn:hover .ls-item-icon { color:#a5b4fc; }
    </style>
</head>
<body class="h-full overflow-hidden antialiased">

    <div x-data="{
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
         }"
         class="flex h-full">

        {{-- ══════════════════════════════════════
             SIDEBAR
        ══════════════════════════════════════ --}}
        <aside class="ls-aside"
               :style="'width:' + (collapsed ? '52px' : '220px')">

            {{-- Logo bar — EXPANDED --}}
            <div class="ls-logo-bar" x-show="!collapsed">
                <div class="ls-logo-icon">L</div>
                <div class="ls-logo-text">Lumisk<span>.</span></div>
                <button class="ls-toggle-btn" @click="toggleSidebar()" style="margin-left:auto;">
                    <svg class="ls-toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            {{-- Logo bar — COLLAPSED: just a centred expand button, fits in 52px --}}
            <div class="ls-logo-bar-collapsed" x-show="collapsed">
                <button class="ls-toggle-btn" @click="toggleSidebar()" title="Expand sidebar">
                    <svg class="ls-toggle-icon" style="transform:rotate(180deg);"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            {{-- Nav --}}
            <nav class="ls-nav">
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
                    $isFirst = true;
                @endphp

                @foreach ($navGroups as [$groupKey, $groupLabel, $items])
                    @php
                        $visibleItems = array_filter($items, fn($item) => auth()->user()->hasPermission($item[3]));
                    @endphp
                    @if (count($visibleItems) > 0)

                        @if (! $isFirst)
                            <hr class="ls-divider">
                        @endif

                        <div class="ls-group">
                            {{-- Group label — hidden in icon-only mode --}}
                            <div class="ls-group-header" x-show="!collapsed" @click="toggleGroup('{{ $groupKey }}')">
                                <span class="ls-group-label">{{ $groupLabel }}</span>
                                <span class="ls-group-arrow"
                                      :style="groups.{{ $groupKey }} ? '' : 'transform:rotate(-90deg)'">&#9662;</span>
                            </div>

                            {{-- Items --}}
                            <div x-show="collapsed || groups.{{ $groupKey }}"
                                 x-transition:enter="transition-opacity duration-150"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition-opacity duration-100"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0">
                                @foreach ($visibleItems as [$route, $label, $icon, $perm])
                                    @php $active = request()->routeIs(str_replace('.index', '', $route) . '*') || request()->routeIs($route); @endphp
                                    <a href="{{ route($route) }}"
                                       class="ls-item {{ $active ? 'ls-active' : '' }}"
                                       x-data="{ tip: false }"
                                       @mouseenter="if(collapsed) tip = true"
                                       @mouseleave="tip = false">
                                        <svg class="ls-item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                                        </svg>
                                        <span class="ls-item-label" x-show="!collapsed">{{ $label }}</span>
                                        @if ($route === 'admin.tickets.index' && ($openTicketsCount ?? 0) > 0)
                                            <span class="ls-badge" style="background:#ef4444;" x-show="!collapsed">{{ $openTicketsCount }}</span>
                                        @endif
                                        @if ($route === 'admin.subscriptions.index' && ($pastDueCount ?? 0) > 0)
                                            <span class="ls-badge" style="background:#ef4444;" x-show="!collapsed">{{ $pastDueCount }}</span>
                                        @endif
                                        @if ($route === 'admin.products.index' && ($lowStockCount ?? 0) > 0)
                                            <span class="ls-badge" style="background:#f59e0b;" x-show="!collapsed">{{ $lowStockCount }}</span>
                                        @endif
                                        {{-- Collapsed tooltip --}}
                                        <span class="ls-tooltip" x-show="tip" x-cloak>{{ $label }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        @php $isFirst = false; @endphp
                    @endif
                @endforeach
            </nav>

            {{-- Sign out --}}
            <div class="ls-signout">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="ls-signout-btn"
                            x-data="{ tip: false }"
                            @mouseenter="if(collapsed) tip = true"
                            @mouseleave="tip = false"
                            style="position:relative;">
                        <svg class="ls-item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span x-show="!collapsed">Sign out</span>
                        <span class="ls-tooltip" x-show="tip" x-cloak>Sign out</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- ══════════════════════════════════════
             MAIN COLUMN — flex-1 fills remaining width automatically
        ══════════════════════════════════════ --}}
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">

            <header class="flex-shrink-0 sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-gray-200 bg-white/90 px-4 backdrop-blur dark:border-ink-600 dark:bg-ink-850/90 sm:px-6">
                <button @click="toggleSidebar()" class="text-gray-500 hover:text-gray-900 dark:hover:text-white lg:hidden">
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
                            <svg class="hidden h-4 w-4 text-gray-400 sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
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

            <main class="p-4 sm:p-6 lg:p-8" style="flex:1; min-height:0; overflow-y:auto;">
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
