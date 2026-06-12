<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('portal.invoices.index') }}" class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-purple dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                Invoices
            </a>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</h2>
                <x-status-badge :color="$invoice->statusColor()" :label="$invoice->status" />
            </div>
        </div>
        <a href="{{ route('portal.invoices.pdf', $invoice) }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Download PDF
        </a>
    </div>

    {{-- Payment summary --}}
    <x-payment-summary :invoice="$invoice" />

    <x-document-preview :doc="$invoice" heading="INVOICE" :number="$invoice->invoice_number"
                        recipient-label="Bill To" second-date-label="Due Date" :second-date="$invoice->due_date" />
</div>
