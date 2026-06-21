@props(['invoice'])

{{-- Read-only payment summary shared by the admin and client-portal invoice pages.
     Expects $invoice to have its `payments` relation loaded. --}}
@if ($invoice->payments->isNotEmpty() || $invoice->status !== 'draft')
    @php $pct = $invoice->total > 0 ? min(100, max(0, round(($invoice->total_paid / $invoice->total) * 100))) : 0; @endphp
    <div class="card mb-6 p-5">
        <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Payment Summary</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Invoice Total</p>
                <p class="mt-1 font-mono text-lg font-semibold text-gray-900 dark:text-white">{{ currency_amount($invoice, $invoice->total) }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Total Paid</p>
                <p class="mt-1 font-mono text-lg font-semibold {{ $invoice->total_paid > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">{{ currency_amount($invoice, $invoice->total_paid) }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Outstanding</p>
                @if ($invoice->outstanding_balance > 0)
                    <p class="mt-1 font-mono text-lg font-semibold text-brand-purple">{{ currency_amount($invoice, $invoice->outstanding_balance) }}</p>
                @else
                    <p class="mt-1 text-lg font-semibold text-green-600 dark:text-green-400">Fully Paid</p>
                @endif
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="mt-4 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
            <div class="h-full rounded-full bg-gradient-brand" style="width: {{ $pct }}%"></div>
        </div>

        {{-- Payment history --}}
        @if ($invoice->payments->isNotEmpty())
            <div class="mt-5 border-t border-gray-200 pt-4 dark:border-ink-600">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Payment History</p>
                <div class="space-y-2">
                    @foreach ($invoice->payments as $payment)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-brand-soft text-brand-purple">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $payment->payment_method_label }}@if ($payment->reference_number)<span class="font-normal text-gray-400"> · {{ $payment->reference_number }}</span>@endif
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->payment_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <span class="font-mono text-sm font-medium text-green-600 dark:text-green-400">{{ currency_amount($invoice, $payment->amount) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
