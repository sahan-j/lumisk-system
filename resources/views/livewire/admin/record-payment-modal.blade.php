<div>
    @if ($show && $invoice)
        <x-app-modal title="Record Payment" close="$set('show', false)" max-width="sm:max-w-lg">
            {{-- Invoice summary --}}
            <div class="mb-5 rounded-lg bg-gray-50 p-4 dark:bg-ink-800">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoice</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
                    <span class="font-mono text-sm text-gray-900 dark:text-white">{{ money($invoice->total) }}</span>
                </div>
                <div class="mt-1 flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Paid</span>
                    <span class="font-mono text-sm text-green-600 dark:text-green-400">{{ money($invoice->total_paid) }}</span>
                </div>
                <div class="mt-1 flex items-center justify-between border-t border-gray-200 pt-2 dark:border-ink-600">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Outstanding</span>
                    @if ($invoice->outstanding_balance > 0)
                        <span class="font-mono text-sm font-bold text-brand-purple">{{ money($invoice->outstanding_balance) }}</span>
                    @else
                        <span class="text-sm font-bold text-green-600 dark:text-green-400">Fully Paid</span>
                    @endif
                </div>
            </div>

            {{-- Form --}}
            @if ($invoice->outstanding_balance > 0)
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="form-label">Amount ({{ company_settings()->currency ?: 'LKR' }}) <span class="text-red-500">*</span></label>
                        <input wire:model="amount" type="number" step="0.01" min="0.01" class="form-input-base" placeholder="0.00">
                        @error('amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">Payment Method <span class="text-red-500">*</span></label>
                            <select wire:model="paymentMethod" class="form-input-base">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="card">Card</option>
                                <option value="other">Other</option>
                            </select>
                            @error('paymentMethod') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Payment Date <span class="text-red-500">*</span></label>
                            <input wire:model="paymentDate" type="date" class="form-input-base">
                            @error('paymentDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Reference Number <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                        <input wire:model="referenceNumber" type="text" class="form-input-base" placeholder="Transaction ID, Cheque No. etc.">
                        @error('referenceNumber') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Note <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                        <textarea wire:model="note" rows="2" class="form-input-base text-sm"></textarea>
                        @error('note') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-ink-600">
                        <button type="button" wire:click="$set('show', false)" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">Record Payment</span>
                            <span wire:loading wire:target="save">Saving…</span>
                        </button>
                    </div>
                </form>
            @else
                <div class="flex justify-end border-t border-gray-200 pt-4 dark:border-ink-600">
                    <button type="button" wire:click="$set('show', false)" class="btn-secondary">Close</button>
                </div>
            @endif

            {{-- Payment history --}}
            @if ($invoice->payments->isNotEmpty())
                <div class="mt-6 border-t border-gray-200 pt-4 dark:border-ink-600">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Payment History</p>
                    <div class="space-y-2">
                        @foreach ($invoice->payments as $payment)
                            <div wire:key="payment-{{ $payment->id }}" class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 dark:border-ink-600">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $payment->payment_method_label }}@if ($payment->reference_number)<span class="font-normal text-gray-400"> · {{ $payment->reference_number }}</span>@endif
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->payment_date->format('M d, Y') }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm font-medium text-green-600 dark:text-green-400">{{ money($payment->amount) }}</span>
                                    <button type="button"
                                            wire:click="deletePayment({{ $payment->id }})"
                                            wire:confirm="Are you sure you want to remove this payment?"
                                            class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30"
                                            title="Remove payment">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-app-modal>
    @endif
</div>
