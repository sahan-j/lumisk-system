@props(['size' => 'md', 'mono' => false])

@php
    $textSize = match ($size) {
        'sm' => 'text-lg',
        'lg' => 'text-3xl',
        'xl' => 'text-4xl',
        default => 'text-2xl',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-2 font-semibold $textSize"]) }}>
    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-brand text-white font-bold">L</span>
    <span class="{{ $mono ? 'text-white' : 'text-navy dark:text-white' }}">Lumisk<span class="text-brand-purple">.</span></span>
</span>
