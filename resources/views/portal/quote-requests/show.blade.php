<x-layouts.portal :title="$quoteRequest->request_number">
    <x-alert />

    <div class="mx-auto max-w-3xl">
        <a href="{{ route('portal.quote-requests.index') }}"
           class="mb-5 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            Back to requests
        </a>

        <div class="mb-6">
            <p class="font-mono text-xs text-gray-400">{{ $quoteRequest->request_number }}</p>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $quoteRequest->title }}</h1>
        </div>

        {{-- Status tracker --}}
        @unless ($quoteRequest->status === 'declined')
            @php
                $steps = [['pending', 'Submitted'], ['reviewing', 'Under Review'], ['converted', 'Estimate Ready']];
                $order = ['pending', 'reviewing', 'converted'];
                $currentIdx = array_search($quoteRequest->status, $order);
                $currentIdx = $currentIdx === false ? 0 : $currentIdx;
            @endphp
            <div class="card mb-6 flex items-center p-5">
                @foreach ($steps as $i => [$stepStatus, $stepLabel])
                    @php $done = $i <= $currentIdx; @endphp
                    <div class="flex-1 text-center">
                        <div class="mx-auto mb-1.5 flex h-9 w-9 items-center justify-center rounded-full {{ $i === $currentIdx ? 'ring-2 ring-brand-purple ring-offset-2 dark:ring-offset-ink-850' : '' }}"
                             style="{{ $done ? 'background: var(--brand-gradient, linear-gradient(135deg,#00d4ff,#6d5cff));' : '' }}"
                             @class(['bg-gray-200 dark:bg-ink-600' => ! $done])>
                            <svg class="h-4 w-4 {{ $done ? 'text-white' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                @if ($stepStatus === 'pending')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                @elseif ($stepStatus === 'reviewing')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                @endif
                            </svg>
                        </div>
                        <p class="text-xs font-medium {{ $done ? 'text-brand-purple' : 'text-gray-400' }}">{{ $stepLabel }}</p>
                    </div>
                    @if ($i < 2)
                        <div class="mb-5 h-0.5 flex-1 {{ $i < $currentIdx ? 'bg-brand-purple' : 'bg-gray-200 dark:bg-ink-600' }}"></div>
                    @endif
                @endforeach
            </div>
        @endunless

        {{-- Status message --}}
        @if ($quoteRequest->status === 'pending')
            <div class="card mb-6 border-amber-200 bg-amber-50 p-4 dark:border-amber-900/40 dark:bg-amber-900/10">
                <p class="text-sm font-medium text-amber-700 dark:text-amber-400">⏳ Your request is pending review</p>
                <p class="mt-1 text-xs text-amber-600 dark:text-amber-500">We'll review your request and get back to you within 1–2 business days.</p>
            </div>
        @elseif ($quoteRequest->status === 'reviewing')
            <div class="card mb-6 border-brand-purple/25 bg-brand-purple/5 p-4">
                <p class="text-sm font-medium text-brand-purple">👀 Your request is being reviewed</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Our team is preparing your estimate. You'll be notified when it's ready.</p>
            </div>
        @elseif ($quoteRequest->status === 'converted')
            <div class="card mb-6 flex items-center justify-between gap-4 border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/40 dark:bg-emerald-900/10">
                <div>
                    <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">✅ Your estimate is ready!</p>
                    <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-500">We've prepared an estimate based on your request. Review and accept it to get started.</p>
                </div>
                @if ($quoteRequest->convertedEstimate)
                    <a href="{{ route('portal.estimates.show', $quoteRequest->convertedEstimate) }}" class="btn-primary shrink-0">View Estimate →</a>
                @endif
            </div>
        @elseif ($quoteRequest->status === 'declined')
            <div class="card mb-6 border-red-200 bg-red-50 p-4 dark:border-red-900/40 dark:bg-red-900/10">
                <p class="text-sm font-medium text-red-600 dark:text-red-400">❌ Request could not be fulfilled</p>
                @if ($quoteRequest->declined_reason)
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-500">{{ $quoteRequest->declined_reason }}</p>
                @endif
                @if (Route::has('portal.tickets.create'))
                    <a href="{{ route('portal.tickets.create') }}" class="mt-2.5 inline-block text-xs font-medium text-red-600 hover:underline dark:text-red-400">Contact support for more information →</a>
                @endif
            </div>
        @endif

        {{-- Details --}}
        <div class="card p-6">
            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Request Details</h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div><dt class="text-xs text-gray-400">Service Type</dt><dd class="mt-0.5 text-sm text-gray-800 dark:text-gray-200">{{ $quoteRequest->service_type_label }}</dd></div>
                <div><dt class="text-xs text-gray-400">Budget Range</dt><dd class="mt-0.5 text-sm text-gray-800 dark:text-gray-200">{{ $quoteRequest->budget_range_label }}</dd></div>
                <div><dt class="text-xs text-gray-400">Timeline</dt><dd class="mt-0.5 text-sm text-gray-800 dark:text-gray-200">{{ $quoteRequest->timeline_label }}</dd></div>
                <div><dt class="text-xs text-gray-400">Submitted</dt><dd class="mt-0.5 text-sm text-gray-800 dark:text-gray-200">{{ $quoteRequest->created_at->format('M d, Y') }}</dd></div>
            </dl>

            <div class="mt-5 border-t border-gray-100 pt-5 dark:border-ink-700">
                <dt class="mb-1.5 text-xs text-gray-400">Description</dt>
                <dd class="whitespace-pre-line text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $quoteRequest->description }}</dd>
            </div>

            @if ($quoteRequest->attachments)
                <div class="mt-5 border-t border-gray-100 pt-5 dark:border-ink-700">
                    <dt class="mb-2 text-xs text-gray-400">Attachments</dt>
                    <div class="space-y-2">
                        @foreach ($quoteRequest->attachments as $i => $attachment)
                            <a href="{{ route('portal.quote-requests.attachment.download', ['quoteRequest' => $quoteRequest, 'index' => $i]) }}"
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
</x-layouts.portal>
