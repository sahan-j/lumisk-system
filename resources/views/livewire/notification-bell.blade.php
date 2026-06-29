<div class="relative" x-data="{ open: false }" wire:poll.60s="loadNotifications">
    {{-- Bell button --}}
    <button type="button"
            @click="open = !open; if (open) $wire.loadNotifications()"
            class="relative flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-ink-700 dark:hover:text-white">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
        @if ($unreadCount > 0)
            <span class="absolute -right-1 -top-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full border-2 border-white px-1 text-[10px] font-bold text-white dark:border-ink-850" style="background: var(--brand-gradient, linear-gradient(135deg,#00d4ff,#6d5cff));">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open" x-cloak
         @click.outside="open = false"
         @keydown.escape.window="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute right-0 top-[calc(100%+8px)] z-50 w-80 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-ink-600 dark:bg-ink-850 sm:w-96">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-ink-700">
            <span class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                Notifications
                @if ($unreadCount > 0)
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-medium text-white" style="background: var(--brand-gradient, linear-gradient(135deg,#00d4ff,#6d5cff));">{{ $unreadCount }} new</span>
                @endif
            </span>
            @if ($unreadCount > 0)
                <button type="button" wire:click="markAllRead" class="text-xs font-medium text-brand-purple hover:underline">Mark all read</button>
            @endif
        </div>

        {{-- List --}}
        <div class="max-h-96 overflow-y-auto">
            @forelse ($notifications as $notif)
                <button type="button" wire:click="goToNotification('{{ $notif['id'] }}')"
                        @class([
                            'flex w-full items-start gap-3 border-b border-gray-50 px-4 py-3 text-left hover:bg-gray-50 dark:border-ink-800 dark:hover:bg-ink-800',
                        ])
                        @style(['background: var(--brand-2-soft, rgba(109,92,255,0.06))' => ! $notif['read']])>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg" style="background: {{ $notif['color'] }}1a;">
                        <svg class="h-4 w-4" style="color: {{ $notif['color'] }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $notif['icon'] }}" /></svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-start justify-between gap-2">
                            <span class="text-[13px] {{ $notif['read'] ? 'font-medium' : 'font-semibold' }} leading-tight text-gray-900 dark:text-white">{{ $notif['title'] }}</span>
                            @if (! $notif['read'])<span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full" style="background: var(--brand-2, #6d5cff);"></span>@endif
                        </span>
                        <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">{{ $notif['message'] }}</span>
                        <span class="mt-1 block text-[10px] text-gray-400">{{ $notif['time'] }}</span>
                    </span>
                </button>
            @empty
                <div class="px-5 py-10 text-center">
                    <svg class="mx-auto mb-2 h-8 w-8 text-gray-300 dark:text-ink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.73 21a2 2 0 01-3.46 0M18.63 13A17.888 17.888 0 0118 8m-6-4a6 6 0 016 6c0 .34.024.673.07 1M5.64 5.64a9 9 0 00-.64 2.36C5 14 2 16 2 16h13M9 21a3 3 0 005.66-1M3 3l18 18" /></svg>
                    <p class="text-sm text-gray-400">No notifications yet</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if (count($notifications) > 0)
            <div class="border-t border-gray-100 px-4 py-2.5 text-center dark:border-ink-700">
                <a href="{{ $guard === 'client' ? route('portal.notifications.index') : route('admin.notifications.index') }}" wire:navigate class="text-xs font-medium text-brand-purple hover:underline">View all notifications →</a>
            </div>
        @endif
    </div>
</div>
