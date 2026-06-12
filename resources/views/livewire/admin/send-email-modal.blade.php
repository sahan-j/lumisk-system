<div>
    @if ($show)
        <x-app-modal
            :title="$type === 'invoice' ? 'Send Invoice' : 'Send Estimate'"
            close="$set('show', false)"
            max-width="sm:max-w-xl"
        >
            <form wire:submit="send" class="space-y-4">
                {{-- Send error --}}
                @if ($errors->has('send_error'))
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-400">
                        {{ $errors->first('send_error') }}
                    </div>
                @endif

                {{-- To --}}
                <div>
                    <label class="form-label">To <span class="text-red-500">*</span></label>
                    <input wire:model="toEmail" type="email" class="form-input-base" placeholder="client@example.com">
                    @error('toEmail') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- CC --}}
                <div>
                    <label class="form-label">CC <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                    <input wire:model="ccEmail" type="email" class="form-input-base" placeholder="cc@example.com">
                    @error('ccEmail') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Subject --}}
                <div>
                    <label class="form-label">Subject <span class="text-red-500">*</span></label>
                    <input wire:model="subject" type="text" class="form-input-base">
                    @error('subject') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Message --}}
                <div>
                    <label class="form-label">Message <span class="text-red-500">*</span></label>
                    <textarea wire:model="message" rows="6" class="form-input-base text-sm"></textarea>
                    @error('message') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Available placeholders:
                        @if($type === 'invoice')
                            {invoice_number}, {client_name}, {total}, {due_date}, {company_name}
                        @else
                            {estimate_number}, {client_name}, {total}, {expiry_date}, {company_name}
                        @endif
                    </p>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-4 dark:border-ink-600">
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        PDF will be attached automatically.
                    </p>
                    <div class="flex gap-3">
                        <button type="button" wire:click="$set('show', false)" class="btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="send">
                            <span wire:loading.remove wire:target="send">
                                <svg class="mr-1.5 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Send Email
                            </span>
                            <span wire:loading wire:target="send">Sending…</span>
                        </button>
                    </div>
                </div>
            </form>
        </x-app-modal>
    @endif
</div>
