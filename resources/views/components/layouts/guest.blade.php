@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' — ' : '' }}{{ config('app.name') }}</title>
    <script>
        if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased">
    <div class="flex min-h-full">
        {{-- Brand panel --}}
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-navy p-12 lg:flex">
            <div class="absolute inset-0 opacity-20"
                 style="background-image: radial-gradient(circle at 20% 20%, #00d4ff 0, transparent 40%), radial-gradient(circle at 80% 70%, #6d5cff 0, transparent 35%);"></div>
            <x-brand size="lg" mono class="relative" />
            <div class="relative">
                <h1 class="text-4xl font-semibold leading-tight text-white">
                    Business management,<br><span class="text-gradient-brand">refined.</span>
                </h1>
                <p class="mt-4 max-w-md text-gray-300">
                    Invoices, estimates and clients — all in one premium workspace built for Lumisk Technology.
                </p>
            </div>
            <p class="relative text-sm text-gray-400">&copy; {{ date('Y') }} Lumisk Technology</p>
        </div>

        {{-- Form panel --}}
        <div class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-20">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <x-brand size="lg" />
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>
