<div
    x-data="globalSearch()"
    x-on:keydown.window.cmd.k.prevent="openSearch()"
    x-on:keydown.window.ctrl.k.prevent="openSearch()"
    x-on:keydown.escape="closeSearch()"
    class="relative w-full max-w-md">

    {{-- Search input --}}
    <div class="relative">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
            x-ref="searchInput"
            type="text"
            wire:model.live.debounce.300ms="query"
            x-on:focus="open = true"
            x-on:input="activeIndex = -1"
            x-on:keydown.down.prevent="moveDown()"
            x-on:keydown.up.prevent="moveUp()"
            x-on:keydown.enter.prevent="selectActive()"
            placeholder="Search clients, invoices, estimates…"
            class="w-full rounded-lg border border-gray-300 bg-gray-50 py-2 pl-10 pr-12 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-purple focus:ring-brand-purple dark:border-ink-600 dark:bg-ink-800 dark:text-gray-100">
        <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 rounded border border-gray-200 px-1.5 py-0.5 text-[10px] font-medium text-gray-400 dark:border-ink-600">⌘K</span>
    </div>

    {{-- Results dropdown --}}
    <div
        x-show="open && $wire.showResults"
        x-on:click.outside="open = false"
        x-transition
        x-cloak
        class="absolute left-0 right-0 top-[calc(100%+6px)] z-50 max-h-[420px] overflow-y-auto rounded-xl border border-gray-200 bg-white p-2 shadow-2xl dark:border-ink-600 dark:bg-ink-850">

        {{-- Clients --}}
        @if (count($results['clients']) > 0)
            <div class="px-2.5 pb-1 pt-2 text-[10px] font-semibold uppercase tracking-wider text-brand-purple">Clients ({{ count($results['clients']) }})</div>
            @foreach ($results['clients'] as $c)
                <a href="{{ route('admin.clients.show', $c['id']) }}" class="search-item block rounded-md px-2.5 py-2">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $c['name'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $c['email'] }}</div>
                </a>
            @endforeach
        @endif

        {{-- Invoices --}}
        @if (count($results['invoices']) > 0)
            <div class="px-2.5 pb-1 pt-2 text-[10px] font-semibold uppercase tracking-wider text-brand-purple">Invoices ({{ count($results['invoices']) }})</div>
            @foreach ($results['invoices'] as $inv)
                <a href="{{ route('admin.invoices.show', $inv['id']) }}" class="search-item flex items-center justify-between rounded-md px-2.5 py-2">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $inv['invoice_number'] }} · {{ $inv['client_name'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ money($inv['total']) }}</div>
                    </div>
                    <span class="rounded-full bg-brand-purple/10 px-2 py-0.5 text-[10px] font-medium text-brand-purple">{{ ucfirst($inv['status']) }}</span>
                </a>
            @endforeach
        @endif

        {{-- Estimates --}}
        @if (count($results['estimates']) > 0)
            <div class="px-2.5 pb-1 pt-2 text-[10px] font-semibold uppercase tracking-wider text-brand-purple">Estimates ({{ count($results['estimates']) }})</div>
            @foreach ($results['estimates'] as $est)
                <a href="{{ route('admin.estimates.show', $est['id']) }}" class="search-item flex items-center justify-between rounded-md px-2.5 py-2">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $est['estimate_number'] }} · {{ $est['client_name'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ money($est['total']) }}</div>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-medium" style="background: rgba(0,212,255,0.12); color: #00a8cc;">{{ ucfirst($est['status']) }}</span>
                </a>
            @endforeach
        @endif

        {{-- Empty state --}}
        @if (count($results['clients']) === 0 && count($results['invoices']) === 0 && count($results['estimates']) === 0)
            <div class="px-6 py-6 text-center text-sm text-gray-400">No results found for "{{ $query }}"</div>
        @endif
    </div>
</div>
