<x-layouts.portal title="Request a Quote">
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('portal.quote-requests.index') }}"
           class="mb-5 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            Back to requests
        </a>

        <div class="card p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Request a Quote</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Fill in the details below. We'll review and send you an estimate within 1–2 business days.
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900/40 dark:bg-red-900/10">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs text-red-600 dark:text-red-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('portal.quote-requests.store') }}" enctype="multipart/form-data"
                  class="space-y-5"
                  x-data="{ chars: {{ strlen(old('description', '')) }} }">
                @csrf

                {{-- Title --}}
                <div>
                    <label for="title" class="form-label">Project Title <span class="text-red-500">*</span></label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required
                           placeholder="e.g. Restaurant Website with Online Menu" class="form-input-base">
                </div>

                {{-- Service type + budget --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="service_type" class="form-label">Service Type <span class="text-red-500">*</span></label>
                        <select id="service_type" name="service_type" required class="form-input-base">
                            <option value="">Select service…</option>
                            <option value="website" @selected(old('service_type') === 'website')>🌐 Website Development</option>
                            <option value="mobile_app" @selected(old('service_type') === 'mobile_app')>📱 Mobile App</option>
                            <option value="design" @selected(old('service_type') === 'design')>🎨 Design</option>
                            <option value="maintenance" @selected(old('service_type') === 'maintenance')>🔧 Maintenance / Support</option>
                            <option value="hosting" @selected(old('service_type') === 'hosting')>☁️ Hosting & Server</option>
                            <option value="other" @selected(old('service_type') === 'other')>📋 Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="budget_range" class="form-label">Budget Range <span class="text-red-500">*</span></label>
                        <select id="budget_range" name="budget_range" required class="form-input-base">
                            <option value="">Select range…</option>
                            <option value="under_50k" @selected(old('budget_range') === 'under_50k')>Under LKR 50,000</option>
                            <option value="50k_150k" @selected(old('budget_range') === '50k_150k')>LKR 50,000 – 150,000</option>
                            <option value="150k_500k" @selected(old('budget_range') === '150k_500k')>LKR 150,000 – 500,000</option>
                            <option value="over_500k" @selected(old('budget_range') === 'over_500k')>Over LKR 500,000</option>
                            <option value="flexible" @selected(old('budget_range') === 'flexible')>Flexible / Open to discussion</option>
                        </select>
                    </div>
                </div>

                {{-- Timeline pills --}}
                <div x-data="{ timeline: '{{ old('timeline', 'flexible') }}' }">
                    <label class="form-label">Expected Timeline <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['asap' => '🚀 ASAP', '1_month' => '1 Month', '3_months' => '3 Months', '6_months' => '6 Months', 'flexible' => '🕐 Flexible'] as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="timeline" value="{{ $value }}" x-model="timeline" class="sr-only">
                                <span class="inline-block rounded-full border px-4 py-1.5 text-sm font-medium transition"
                                      :class="timeline === '{{ $value }}'
                                          ? 'border-transparent bg-brand-purple text-white'
                                          : 'border-gray-200 bg-white text-gray-600 hover:border-brand-purple/40 dark:border-ink-600 dark:bg-ink-800 dark:text-gray-300'">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="form-label">
                        Project Description <span class="text-red-500">*</span>
                        <span class="font-normal text-gray-400">(minimum 20 characters)</span>
                    </label>
                    <textarea id="description" name="description" required minlength="20" maxlength="5000" rows="6"
                              x-on:input="chars = $event.target.value.length"
                              placeholder="Describe your project in detail:&#10;- What type of website/app do you need?&#10;- What features are required?&#10;- Do you have existing branding/design?&#10;- Reference websites you like?"
                              class="form-input-base leading-relaxed">{{ old('description') }}</textarea>
                    <p class="mt-1 text-right text-xs text-gray-400"><span x-text="chars">0</span> / 5000</p>
                </div>

                {{-- Attachments --}}
                <div x-data="{ files: [] }">
                    <label class="form-label">
                        Attachments <span class="font-normal text-gray-400">(optional — wireframes, designs, docs)</span>
                    </label>
                    <label class="flex cursor-pointer flex-col items-center rounded-lg border-2 border-dashed border-gray-200 p-5 text-center transition hover:border-brand-purple/40 dark:border-ink-600">
                        <svg class="mb-2 h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                        <span class="text-sm text-gray-600 dark:text-gray-300">Click to attach files</span>
                        <span class="mt-1 text-xs text-gray-400">JPG, PNG, PDF, DOC, ZIP — max 10MB each</span>
                        <input type="file" name="attachments[]" multiple class="sr-only"
                               x-on:change="files = Array.from($event.target.files).map(f => f.name)">
                    </label>
                    <template x-if="files.length">
                        <div class="mt-2 flex flex-wrap gap-2">
                            <template x-for="name in files" :key="name">
                                <span class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-ink-700 dark:text-gray-300">📎 <span x-text="name"></span></span>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Submit --}}
                <div class="flex items-center gap-3 pt-1">
                    <button type="submit" class="btn-primary">Submit Quote Request</button>
                    <a href="{{ route('portal.quote-requests.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.portal>
