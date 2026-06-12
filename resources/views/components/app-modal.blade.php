@props(['title' => null, 'maxWidth' => 'sm:max-w-lg', 'close' => null])

{{-- Presentational modal. Render conditionally from the parent (@if). The
     `close` prop is a Livewire action string used by the backdrop and X button. --}}
<div class="fixed inset-0 z-50 overflow-y-auto" @if($close) wire:keydown.escape="{{ $close }}" @endif>
    <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
        <div class="fixed inset-0 bg-black/50 transition-opacity"
             @if($close) wire:click="{{ $close }}" @endif></div>

        <div {{ $attributes->merge(['class' => "relative w-full $maxWidth card p-6 shadow-xl"]) }}>
            @if ($title || $close)
                <div class="mb-5 flex items-start justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                    @if ($close)
                        <button type="button" wire:click="{{ $close }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
