<x-layouts.admin :title="'Quote Request ' . $quoteRequest->request_number">
    <x-alert />

    <a href="{{ route('admin.quote-requests.index') }}"
       class="mb-5 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Back to quote requests
    </a>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left --}}
        <div class="lg:col-span-2">
            <div class="card p-6">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="font-mono text-xs text-gray-400">{{ $quoteRequest->request_number }}</p>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $quoteRequest->title }}</h1>
                        <a href="{{ route('admin.clients.show', $quoteRequest->client) }}" class="mt-1 inline-block text-sm text-brand-purple hover:underline">{{ $quoteRequest->client?->name }}</a>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium" style="background: {{ $quoteRequest->status_color }}1a; color: {{ $quoteRequest->status_color }};">{{ $quoteRequest->status_label }}</span>
                </div>

                <div class="border-t border-gray-100 pt-4 dark:border-ink-700">
                    <p class="mb-1.5 text-xs text-gray-400">Client Requirements</p>
                    <p class="whitespace-pre-line text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $quoteRequest->description }}</p>
                </div>

                @if ($quoteRequest->attachments)
                    <div class="mt-5 border-t border-gray-100 pt-4 dark:border-ink-700">
                        <p class="mb-2 text-xs text-gray-400">Attachments</p>
                        <div class="space-y-2">
                            @foreach ($quoteRequest->attachments as $i => $attachment)
                                <a href="{{ route('admin.quote-requests.attachment.download', ['quoteRequest' => $quoteRequest, 'index' => $i]) }}"
                                   class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-ink-600 dark:text-gray-300 dark:hover:bg-ink-800">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                    <span class="flex-1 truncate">{{ $attachment['name'] }}</span>
                                    <span class="text-xs text-gray-400">{{ number_format($attachment['size'] / 1024, 0) }} KB</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right --}}
        <div class="space-y-6">
            {{-- Details --}}
            <div class="card p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-gray-400">Service</dt><dd class="text-right text-gray-800 dark:text-gray-200">{{ $quoteRequest->service_type_label }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-400">Budget</dt><dd class="text-right text-gray-800 dark:text-gray-200">{{ $quoteRequest->budget_range_label }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-400">Timeline</dt><dd class="text-right text-gray-800 dark:text-gray-200">{{ $quoteRequest->timeline_label }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-400">Submitted</dt><dd class="text-right text-gray-800 dark:text-gray-200">{{ $quoteRequest->created_at->format('M d, Y') }}</dd></div>
                </dl>
            </div>

            {{-- Actions --}}
            @if (in_array($quoteRequest->status, ['pending', 'reviewing'], true))
                <div class="card p-5">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Actions</h3>

                    {{-- Convert --}}
                    <form method="POST" action="{{ route('admin.quote-requests.convert', $quoteRequest) }}">
                        @csrf
                        <label class="form-label text-xs">Internal note (optional)</label>
                        <textarea name="note" rows="2" placeholder="Any notes about this conversion…" class="form-input-base mb-2 text-sm"></textarea>
                        <button type="submit" class="btn-primary w-full justify-center">
                            <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            Convert to Estimate
                        </button>
                    </form>

                    {{-- Decline --}}
                    <div x-data="{ show: false }" class="mt-3 border-t border-gray-100 pt-3 dark:border-ink-700">
                        <button @click="show = !show" type="button" class="w-full rounded-lg border border-red-200 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:hover:bg-red-900/10">
                            Decline Request
                        </button>
                        <form x-show="show" x-cloak x-transition method="POST" action="{{ route('admin.quote-requests.decline', $quoteRequest) }}" class="mt-2">
                            @csrf
                            <textarea name="declined_reason" required rows="3" minlength="10" placeholder="Reason for declining (client will see this)…" class="form-input-base mb-2 text-sm"></textarea>
                            <button type="submit" class="w-full rounded-lg bg-red-500 py-2 text-sm font-medium text-white hover:bg-red-600">Confirm Decline</button>
                        </form>
                    </div>
                </div>
            @elseif ($quoteRequest->status === 'converted')
                <div class="card border-emerald-200 bg-emerald-50 p-5 text-center dark:border-emerald-900/40 dark:bg-emerald-900/10">
                    <svg class="mx-auto mb-2 h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Converted to Estimate</p>
                    @if ($quoteRequest->convertedEstimate)
                        <a href="{{ route('admin.estimates.show', $quoteRequest->convertedEstimate) }}" class="mt-2 inline-block text-xs font-medium text-emerald-600 hover:underline dark:text-emerald-400">View Estimate ({{ $quoteRequest->convertedEstimate->estimate_number }}) →</a>
                    @endif
                    @if ($quoteRequest->admin_note)
                        <p class="mt-3 border-t border-emerald-200 pt-3 text-left text-xs text-gray-500 dark:border-emerald-900/40 dark:text-gray-400">{{ $quoteRequest->admin_note }}</p>
                    @endif
                </div>
            @elseif ($quoteRequest->status === 'declined')
                <div class="card border-red-200 bg-red-50 p-5 dark:border-red-900/40 dark:bg-red-900/10">
                    <p class="text-sm font-medium text-red-600 dark:text-red-400">Request Declined</p>
                    @if ($quoteRequest->declined_reason)
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-500">{{ $quoteRequest->declined_reason }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
