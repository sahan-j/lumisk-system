@if (session('success'))
    <div class="mb-4 rounded-lg border px-4 py-3 text-sm" style="background: rgba(0,212,255,0.1); border-color: #00d4ff; color: #0f172a;">
        <span class="dark:!text-cyan-200">&#10003; {{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg border px-4 py-3 text-sm" style="background: rgba(239,68,68,0.1); border-color: #ef4444; color: #0f172a;">
        <span class="dark:!text-red-200">&#10007; {{ session('error') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
        <ul class="list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
