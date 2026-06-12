@props(['color' => 'gray', 'label' => ''])

@php
    // Full class strings so Tailwind's JIT can detect them.
    $classes = match ($color) {
        'green' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'blue'  => 'bg-brand-purple/10 text-brand-purple dark:bg-brand-purple/20 dark:text-brand-purple',
        'red'   => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        default => 'bg-gray-100 text-gray-600 dark:bg-ink-700 dark:text-gray-300',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize $classes"]) }}>
    {{ $label }}
</span>
