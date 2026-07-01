<x-layouts.portal title="My Quote Requests">
    <x-alert />

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">My Quote Requests</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Submit a new project and we'll prepare an estimate for you.</p>
        </div>
        <a href="{{ route('portal.quote-requests.create') }}" class="btn-primary shrink-0">
            <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            Request a Quote
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">Total Requests</p><p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">Awaiting Estimate</p><p class="mt-1 text-2xl font-semibold text-amber-500">{{ $stats['pending'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">Converted</p><p class="mt-1 text-2xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $stats['converted'] }}</p></div>
    </div>

    {{-- List --}}
    @forelse ($requests as $req)
        <div class="card mb-3 flex items-start gap-4 p-4">
            <span class="mt-1 h-14 w-1 shrink-0 rounded-full" style="background: {{ $req->status_color }};"></span>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $req->title }}</p>
                        <p class="mt-0.5 text-xs text-gray-400">{{ $req->request_number }} · {{ $req->service_type_label }} · {{ $req->budget_range_label }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium"
                          style="background: {{ $req->status_color }}1a; color: {{ $req->status_color }};">
                        {{ $req->status_label }}
                    </span>
                </div>
                <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                    <span>📅 {{ $req->created_at->format('M d, Y') }}</span>
                    <span>⏱ {{ $req->timeline_label }}</span>
                    @if ($req->status === 'converted' && $req->convertedEstimate)
                        <a href="{{ route('portal.estimates.show', $req->convertedEstimate) }}" class="font-medium text-brand-purple hover:underline">📄 View Estimate →</a>
                    @endif
                </div>
            </div>
            <a href="{{ route('portal.quote-requests.show', $req) }}"
               class="shrink-0 self-center rounded-lg border border-brand-purple/30 px-3 py-1.5 text-xs font-medium text-brand-purple hover:bg-brand-purple/5">
                View →
            </a>
        </div>
    @empty
        <div class="card border border-dashed p-12 text-center">
            <svg class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-ink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">No quote requests yet</p>
            <p class="mt-1 text-xs text-gray-400">Have a new project in mind? Submit a request and we'll prepare an estimate for you.</p>
            <a href="{{ route('portal.quote-requests.create') }}" class="btn-primary mt-4 inline-flex">Request a Quote</a>
        </div>
    @endforelse

    @if ($requests->hasPages())
        <div class="mt-4">{{ $requests->links() }}</div>
    @endif
</x-layouts.portal>
