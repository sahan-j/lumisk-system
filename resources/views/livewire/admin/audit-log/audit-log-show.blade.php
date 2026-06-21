<div>
    @php
        $fmt = function ($value) {
            if (is_null($value)) return 'null';
            if (is_bool($value)) return $value ? 'true' : 'false';
            if (is_array($value)) return json_encode($value);
            return (string) $value;
        };
    @endphp

    {{-- Back link --}}
    <a href="{{ route('admin.audit-log.index') }}" wire:navigate class="mb-3 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-purple dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Audit Log
    </a>

    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">Audit Log Detail</h2>

    {{-- Record info --}}
    <div class="card mb-6 p-5">
        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium"
                  style="background-color: {{ $auditLog->event_color }}1a; color: {{ $auditLog->event_color }};">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $auditLog->event_icon }}" /></svg>
                {{ ucfirst($auditLog->event) }}
            </span>
            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ class_basename($auditLog->auditable_type) }}</span>
            @if ($auditLog->auditable_label)
                <span class="font-mono text-sm text-brand-purple">{{ $auditLog->auditable_label }}</span>
            @endif
        </div>

        <dl class="mt-5 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-400">User</dt>
                <dd class="mt-1 text-gray-900 dark:text-white">{{ $auditLog->user_name ?? 'System' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-400">When</dt>
                <dd class="mt-1 text-gray-900 dark:text-white">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-400">IP Address</dt>
                <dd class="mt-1 text-gray-900 dark:text-white">{{ $auditLog->ip_address ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-400">Source</dt>
                <dd class="mt-1 capitalize text-gray-900 dark:text-white">{{ $auditLog->user_type }}</dd>
            </div>
        </dl>

        @if ($auditLog->url)
            <div class="mt-4 text-sm">
                <dt class="text-xs uppercase tracking-wider text-gray-400">URL</dt>
                <dd class="mt-1 break-all font-mono text-xs text-gray-500 dark:text-gray-400">{{ $auditLog->url }}</dd>
            </div>
        @endif
    </div>

    {{-- Changes --}}
    @if ($auditLog->event === 'updated')
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- Before --}}
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900/40 dark:bg-red-900/10">
                <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-red-500">Before</p>
                @foreach ($auditLog->old_values ?? [] as $field => $value)
                    <div class="flex justify-between gap-3 border-b border-red-100 py-1.5 text-xs last:border-0 dark:border-red-900/30">
                        <span class="capitalize text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', $field) }}</span>
                        <span class="max-w-[60%] break-all text-right font-mono text-gray-900 dark:text-gray-100">{{ $fmt($value) }}</span>
                    </div>
                @endforeach
            </div>
            {{-- After --}}
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900/40 dark:bg-green-900/10">
                <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-green-600">After</p>
                @foreach ($auditLog->new_values ?? [] as $field => $value)
                    <div class="flex justify-between gap-3 border-b border-green-100 py-1.5 text-xs last:border-0 dark:border-green-900/30">
                        <span class="capitalize text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', $field) }}</span>
                        <span class="max-w-[60%] break-all text-right font-mono text-gray-900 dark:text-gray-100">{{ $fmt($value) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif (in_array($auditLog->event, ['created', 'restored']))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900/40 dark:bg-green-900/10">
            <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-green-600">{{ $auditLog->event === 'created' ? 'New record' : 'Restored values' }}</p>
            @foreach ($auditLog->new_values ?? [] as $field => $value)
                <div class="flex justify-between gap-3 border-b border-green-100 py-1.5 text-xs last:border-0 dark:border-green-900/30">
                    <span class="capitalize text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', $field) }}</span>
                    <span class="max-w-[60%] break-all text-right font-mono text-gray-900 dark:text-gray-100">{{ $fmt($value) }}</span>
                </div>
            @endforeach
        </div>
    @elseif ($auditLog->event === 'deleted')
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900/40 dark:bg-red-900/10">
            <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-red-500">Deleted record</p>
            @foreach ($auditLog->old_values ?? [] as $field => $value)
                <div class="flex justify-between gap-3 border-b border-red-100 py-1.5 text-xs last:border-0 dark:border-red-900/30">
                    <span class="capitalize text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', $field) }}</span>
                    <span class="max-w-[60%] break-all text-right font-mono text-gray-900 dark:text-gray-100">{{ $fmt($value) }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
