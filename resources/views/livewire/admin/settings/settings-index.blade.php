<div x-data="{ tab: 'company' }">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Settings</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage company details and document defaults.</p>
    </div>

    {{-- Tabs --}}
    <div class="mb-6 border-b border-gray-200 dark:border-ink-600">
        <nav class="flex gap-1">
            @foreach (['company' => 'Company Info', 'invoice' => 'Invoice Defaults', 'estimate' => 'Estimate Defaults', 'email' => 'Email Templates', 'whatsapp' => 'WhatsApp', 'tickets' => 'Support Tickets', 'expenses' => 'Expenses'] as $key => $label)
                <button type="button" @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-gold text-gold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                        class="border-b-2 px-4 py-2.5 text-sm font-medium transition">{{ $label }}</button>
            @endforeach
        </nav>
    </div>

    <form wire:submit="save">
        {{-- Tab 1: Company Info --}}
        <div x-show="tab === 'company'" class="card max-w-3xl p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="form-label">Company Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="form-input-base">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input wire:model="email" type="email" class="form-input-base">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input wire:model="phone" type="text" class="form-input-base">
                </div>
                <div>
                    <label class="form-label">Website</label>
                    <input wire:model="website" type="text" class="form-input-base">
                </div>
                <div>
                    <label class="form-label">Currency <span class="text-red-500">*</span></label>
                    <input wire:model="currency" type="text" class="form-input-base">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea wire:model="address" rows="3" class="form-input-base"></textarea>
                </div>

                {{-- Logo --}}
                <div class="sm:col-span-2">
                    <label class="form-label">Logo</label>
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-ink-600 dark:bg-ink-800">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="h-full w-full object-contain" alt="preview">
                            @elseif ($company->logo)
                                <img src="{{ $company->logoUrl() }}" class="h-full w-full object-contain" alt="logo">
                            @else
                                <span class="text-2xl font-bold text-gold">L</span>
                            @endif
                        </div>
                        <div>
                            <input wire:model="logo" type="file" accept="image/*"
                                   class="block text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-gray-200 dark:text-gray-400 dark:file:bg-ink-700 dark:file:text-gray-200">
                            <div wire:loading wire:target="logo" class="mt-1 text-xs text-gray-400">Uploading…</div>
                            @error('logo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            @if ($company->logo)
                                <button type="button" wire:click="removeLogo" class="mt-1 text-xs text-red-500 hover:underline">Remove logo</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bank Details --}}
            <div class="mt-6 border-t border-gray-200 pt-6 dark:border-ink-600">
                <h3 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Bank Details</h3>
                <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">Shown in the payment details section of invoice and estimate PDFs.</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Bank Name</label>
                        <input wire:model="bank_name" type="text" class="form-input-base">
                        @error('bank_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Account Name</label>
                        <input wire:model="bank_account_name" type="text" class="form-input-base">
                        @error('bank_account_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Account Number</label>
                        <input wire:model="bank_account_number" type="text" class="form-input-base">
                        @error('bank_account_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Branch</label>
                        <input wire:model="bank_branch" type="text" class="form-input-base">
                        @error('bank_branch') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Document Appearance --}}
            <div class="mt-6 border-t border-gray-200 pt-6 dark:border-ink-600">
                <h3 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Document Appearance</h3>
                <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">Controls the text size used across invoice and estimate PDFs.</p>
                <div class="sm:w-64">
                    <label class="form-label">PDF Font Size</label>
                    <select wire:model="pdf_font_size" class="form-input-base">
                        <option value="8">Extra Small</option>
                        <option value="9">Small</option>
                        <option value="10">Normal (Recommended)</option>
                        <option value="11">Large</option>
                        <option value="12">Extra Large</option>
                        <option value="14">Huge</option>
                    </select>
                    @error('pdf_font_size') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Tab 2: Invoice Defaults --}}
        <div x-show="tab === 'invoice'" x-cloak class="card max-w-3xl p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">Invoice Prefix <span class="text-red-500">*</span></label>
                    <input wire:model="invoice_prefix" type="text" class="form-input-base">
                    @error('invoice_prefix') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Next Number <span class="text-red-500">*</span></label>
                    <input wire:model="invoice_next_number" type="number" min="1" class="form-input-base">
                    @error('invoice_next_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Default Tax Rate (%)</label>
                    <input wire:model="default_tax_rate" type="number" step="0.01" min="0" max="100" class="form-input-base">
                    @error('default_tax_rate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Default Notes</label>
                    <textarea wire:model="default_notes" rows="3" class="form-input-base"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Default Terms</label>
                    <textarea wire:model="default_terms" rows="3" class="form-input-base"></textarea>
                </div>
            </div>

            <div class="mt-6 border-t border-gray-200 pt-6 dark:border-ink-600">
                <h3 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Automation</h3>
                <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                    Requires the Laravel scheduler to be running (cPanel cron job: <code class="rounded bg-gray-100 px-1 py-0.5 dark:bg-ink-700">php artisan schedule:run</code>).
                </p>
                <label class="flex cursor-pointer items-center gap-3">
                    <div class="relative flex-shrink-0" x-data>
                        <input wire:model="overdue_reminders_enabled" type="checkbox" id="overdue_reminders_enabled" class="sr-only peer">
                        <div class="h-5 w-9 rounded-full bg-gray-200 peer-checked:bg-brand-purple dark:bg-ink-600 peer-checked:dark:bg-brand-purple transition-colors"></div>
                        <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Send automatic overdue reminder emails to clients</span>
                </label>
                @error('overdue_reminders_enabled') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Tab 3: Estimate Defaults --}}
        <div x-show="tab === 'estimate'" x-cloak class="card max-w-3xl p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">Estimate Prefix <span class="text-red-500">*</span></label>
                    <input wire:model="estimate_prefix" type="text" class="form-input-base">
                    @error('estimate_prefix') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Next Number <span class="text-red-500">*</span></label>
                    <input wire:model="estimate_next_number" type="number" min="1" class="form-input-base">
                    @error('estimate_next_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Default Expiry (days) <span class="text-red-500">*</span></label>
                    <input wire:model="estimate_expiry_days" type="number" min="1" class="form-input-base">
                    @error('estimate_expiry_days') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Tab 4: Email Templates --}}
        <div x-show="tab === 'email'" x-cloak class="card max-w-3xl p-6">
            <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">
                Default subject and body pre-filled when sending invoices or estimates via email.
                Use <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-ink-700">{placeholder}</code> variables — they are replaced with real values when the modal opens.
            </p>
            <div class="space-y-5">
                <div>
                    <label class="form-label">Reply-to Email</label>
                    <input wire:model="reply_to_email" type="email" class="form-input-base" placeholder="replies@yourdomain.com">
                    @error('reply_to_email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <hr class="border-gray-200 dark:border-ink-600">

                <div>
                    <label class="form-label">Invoice Email Subject <span class="text-red-500">*</span></label>
                    <input wire:model="invoice_email_subject" type="text" class="form-input-base">
                    <p class="mt-1 text-xs text-gray-400">{invoice_number}, {client_name}, {total}, {due_date}, {company_name}</p>
                    @error('invoice_email_subject') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Invoice Email Body <span class="text-red-500">*</span></label>
                    <textarea wire:model="invoice_email_body" rows="5" class="form-input-base text-sm"></textarea>
                    @error('invoice_email_body') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <hr class="border-gray-200 dark:border-ink-600">

                <div>
                    <label class="form-label">Estimate Email Subject <span class="text-red-500">*</span></label>
                    <input wire:model="estimate_email_subject" type="text" class="form-input-base">
                    <p class="mt-1 text-xs text-gray-400">{estimate_number}, {client_name}, {total}, {expiry_date}, {company_name}</p>
                    @error('estimate_email_subject') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Estimate Email Body <span class="text-red-500">*</span></label>
                    <textarea wire:model="estimate_email_body" rows="5" class="form-input-base text-sm"></textarea>
                    @error('estimate_email_body') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Tab 5: WhatsApp Templates --}}
        <div x-show="tab === 'whatsapp'" x-cloak class="card max-w-3xl p-6">
            <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">
                Default message pre-filled when sending invoices or estimates via WhatsApp.
                Use <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-ink-700">{placeholder}</code> variables — replaced with real values when the modal opens.
                <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-ink-700">{link}</code> inserts the secure public view link.
            </p>
            <div class="space-y-5">
                <div>
                    <label class="form-label">Invoice Message Template <span class="text-red-500">*</span></label>
                    <textarea wire:model="whatsapp_message_invoice" rows="8" class="form-input-base font-mono text-xs"></textarea>
                    <p class="mt-1 text-xs text-gray-400">{client_name}, {invoice_number}, {total}, {due_date}, {link}, {company_name}, {company_phone}</p>
                    @error('whatsapp_message_invoice') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <hr class="border-gray-200 dark:border-ink-600">

                <div>
                    <label class="form-label">Estimate Message Template <span class="text-red-500">*</span></label>
                    <textarea wire:model="whatsapp_message_estimate" rows="8" class="form-input-base font-mono text-xs"></textarea>
                    <p class="mt-1 text-xs text-gray-400">{client_name}, {estimate_number}, {total}, {expiry_date}, {link}, {company_name}, {company_phone}</p>
                    @error('whatsapp_message_estimate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Tab 6: Support Tickets --}}
        <div x-show="tab === 'tickets'" x-cloak class="card max-w-3xl p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">Ticket Prefix <span class="text-red-500">*</span></label>
                    <input wire:model="ticket_prefix" type="text" class="form-input-base">
                    <p class="mt-1 text-xs text-gray-400">Used for ticket numbers, e.g. {{ $ticket_prefix ?: 'TKT' }}-001</p>
                    @error('ticket_prefix') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 border-t border-gray-200 pt-6 dark:border-ink-600">
                <h3 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">SLA Response Targets (hours)</h3>
                <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">Hours to first response by priority. The SLA-overdue checker runs every 15 minutes.</p>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <label class="form-label">Low</label>
                        <input wire:model="sla_low_hours" type="number" min="1" class="form-input-base">
                        @error('sla_low_hours') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Medium</label>
                        <input wire:model="sla_medium_hours" type="number" min="1" class="form-input-base">
                        @error('sla_medium_hours') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">High</label>
                        <input wire:model="sla_high_hours" type="number" min="1" class="form-input-base">
                        @error('sla_high_hours') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Critical</label>
                        <input wire:model="sla_critical_hours" type="number" min="1" class="form-input-base">
                        @error('sla_critical_hours') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 border-t border-gray-200 pt-6 dark:border-ink-600">
                <label class="flex cursor-pointer items-center gap-3">
                    <div class="relative flex-shrink-0" x-data>
                        <input wire:model="ticket_notifications_enabled" type="checkbox" class="sr-only peer">
                        <div class="h-5 w-9 rounded-full bg-gray-200 peer-checked:bg-brand-purple dark:bg-ink-600 peer-checked:dark:bg-brand-purple transition-colors"></div>
                        <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Send ticket email notifications (new tickets, replies, status changes)</span>
                </label>
                @error('ticket_notifications_enabled') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Tab 7: Expenses (category management) --}}
        <div x-show="tab === 'expenses'" x-cloak class="card max-w-3xl p-6">
            <h3 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Expense Categories</h3>
            <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">Organise expenses into categories. Categories with expenses can't be deleted.</p>
            <livewire:admin.settings.expense-categories-manager />
        </div>

        <div x-show="tab !== 'expenses'" class="mt-6 flex max-w-3xl justify-end">
            <button type="submit" class="btn-primary">
                <span wire:loading.remove wire:target="save">Save Settings</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>
