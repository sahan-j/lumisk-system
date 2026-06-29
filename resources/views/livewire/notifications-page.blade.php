<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Notifications</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $unreadCount }} unread</p>
        </div>
        <div class="flex gap-2">
            @if ($unreadCount > 0)
                <button wire:click="markAllRead" class="btn-secondary !py-1.5 text-sm">Mark all read</button>
            @endif
            <button wire:click="clearRead" wire:confirm="Delete all read notifications?" class="btn-secondary !py-1.5 text-sm">Clear read</button>
        </div>
    </div>

    {{-- Filter tabs --}}
    <div class="mb-4 flex gap-1 border-b border-gray-200 dark:border-ink-600">
        @foreach (['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $key => $label)
            <button wire:click="setFilter('{{ $key }}')"
                    @class([
                        'border-b-2 px-4 py-2 text-sm font-medium transition',
                        'border-brand-purple text-brand-purple' => $filter === $key,
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' => $filter !== $key,
                    ])>{{ $label }}</button>
        @endforeach
    </div>

    {{-- List --}}
    <div class="card divide-y divide-gray-100 overflow-hidden dark:divide-ink-700">
        @forelse ($notifications as $n)
            @php $d = $n->data; $read = ! is_null($n->read_at); @endphp
            <div wire:key="n-{{ $n->id }}" @class(['flex items-start gap-3 px-5 py-4', 'bg-brand-purple/[0.04] dark:bg-brand-purple/[0.08]' => ! $read])>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" style="background: {{ ($d['color'] ?? '#6d5cff') }}1a;">
                    <svg class="h-5 w-5" style="color: {{ $d['color'] ?? '#6d5cff' }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $d['icon'] ?? 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5' }}" /></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <p class="text-sm {{ $read ? 'font-medium' : 'font-semibold' }} text-gray-900 dark:text-white">{{ $d['title'] ?? 'Notification' }}</p>
                        @unless ($read)<span class="h-1.5 w-1.5 rounded-full bg-brand-purple"></span>@endunless
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $d['message'] ?? '' }}</p>
                    <div class="mt-1.5 flex items-center gap-3 text-xs">
                        <span class="text-gray-400">{{ $n->created_at->format('M d, Y · H:i') }}</span>
                        @if (! empty($d['url']))
                            <a href="{{ $d['url'] }}" class="font-medium text-brand-purple hover:underline">View →</a>
                        @endif
                        @unless ($read)
                            <button wire:click="markRead('{{ $n->id }}')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">Mark read</button>
                        @endunless
                    </div>
                </div>
            </div>
        @empty
            <div class="px-5 py-16 text-center">
                <svg class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-ink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                <p class="text-sm text-gray-400">No {{ $filter !== 'all' ? $filter : '' }} notifications</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
