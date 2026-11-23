<x-app-layout>
    <x-slot name="title">Import scholarships with AI</x-slot>

    @php
        $inputClass = 'w-full rounded-xl border-cream-dark text-sm focus:border-brand-500 focus:ring-brand-500';
        $label = 'mb-1 block text-xs font-medium text-ink-soft';
        $list = fn ($v) => is_array($v) ? implode("\n", $v) : $v;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 pb-16" x-data="{ loading: false }">
        <a href="{{ route('admin.scholarships.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-900">&larr; Back to scholarships</a>
        <h1 class="mt-4 font-display text-3xl font-semibold tracking-tight">Import scholarships with AI</h1>
        <p class="mt-2 max-w-3xl text-ink-soft">
            Paste an announcement. The AI drafts the fields, but nothing is saved until you review and confirm.
            It only copies what the text states, so check every row against the source, then add the official link and the date you verified it.
        </p>

        @if (session('ai_error'))
            <div role="alert" class="mt-5 rounded-xl bg-clay-100 px-4 py-3 text-sm text-clay-800">{{ session('ai_error') }}</div>
        @endif
        @if (session('import_error'))
            <div role="alert" class="mt-5 rounded-xl bg-clay-100 px-4 py-3 text-sm text-clay-800">{{ session('import_error') }}</div>
        @endif

        @unless ($import)
            <form method="POST" action="{{ route('admin.ai.extract-scholarships') }}" class="mt-6 rounded-2xl bg-white p-6 ring-1 ring-cream-dark" @submit="loading = true">
                @csrf
                <label for="text" class="block text-sm font-medium">Announcement text</label>
                <textarea id="text" name="text" rows="14" required minlength="30" maxlength="{{ \App\Services\ScholarshipExtractionService::SOURCE_MAX_CHARS }}" class="{{ $inputClass }} mt-2" placeholder="Paste the scholarship announcement here">{{ old('text') }}</textarea>
                @error('text')<p class="mt-1 text-sm text-clay-700">{{ $message }}</p>@enderror
                <div class="mt-4 flex items-center gap-4">
                    <button type="submit" :disabled="loading" class="inline-flex rounded-full bg-aura-gradient px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:brightness-105 disabled:opacity-60">
                        <span x-show="!loading">Extract scholarships</span>
                        <span x-show="loading" x-cloak>Reading the announcement…</span>
                    </button>
                    <a href="{{ route('admin.scholarships.create') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-900">Or add one by hand</a>
                </div>
            </form>
        @else
            @if ($import['skipped'] > 0)
                <div role="status" class="mt-5 rounded-xl bg-cream px-4 py-3 text-sm text-ink">
                    {{ $import['skipped'] }} item(s) from the AI were left out because they did not pass validation. Add them by hand if needed.
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mt-5 rounded-xl bg-clay-100 px-4 py-3 text-sm text-clay-800">
                    <p class="font-semibold">Please correct the highlighted problems:</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
                <aside class="lg:sticky lg:top-6 lg:self-start">
                    <h2 class="font-display text-lg font-semibold">Source text</h2>
                    <pre class="mt-2 max-h-[75vh] overflow-auto whitespace-pre-wrap rounded-2xl bg-white p-4 text-xs leading-relaxed text-ink ring-1 ring-cream-dark">{{ $import['text'] }}</pre>
                </aside>

                <form method="POST" action="{{ route('admin.scholarships.import.store') }}" class="space-y-5">
                    @csrf
                    <h2 class="font-display text-lg font-semibold">Review {{ count($import['items']) }} draft(s)</h2>

                    @foreach ($import['items'] as $i => $item)
                        @php
                            $v = fn ($field) => old("items.$i.$field", $item[$field] ?? null);
                            $included = session()->hasOldInput() ? old("items.$i.include") : true;
                        @endphp
                        <section class="rounded-2xl bg-white p-5 ring-1 ring-cream-dark">
                            <label class="flex items-center gap-2 text-sm font-semibold">
                                <input type="checkbox" name="items[{{ $i }}][include]" value="1" @checked($included) class="rounded border-cream-dark text-brand-600 focus:ring-brand-500">
                                Save this scholarship
                            </label>
                            <input type="hidden" name="items[{{ $i }}][is_active]" value="1">

                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <label class="sm:col-span-2"><span class="{{ $label }}">Name *</span>
                                    <input name="items[{{ $i }}][name]" value="{{ $v('name') }}" required maxlength="255" class="{{ $inputClass }}">
                                    @error("items.$i.name")<span class="text-xs text-clay-700">{{ $message }}</span>@enderror</label>
                                <label><span class="{{ $label }}">Provider *</span>
                                    <input name="items[{{ $i }}][provider]" value="{{ $v('provider') }}" required maxlength="255" class="{{ $inputClass }}">
                                    @error("items.$i.provider")<span class="text-xs text-clay-700">{{ $message }}</span>@enderror</label>
                                <label><span class="{{ $label }}">Year *</span>
                                    <input type="number" name="items[{{ $i }}][year]" value="{{ $v('year') ?? today()->year }}" required min="2000" max="65535" class="{{ $inputClass }}">
                                    @error("items.$i.year")<span class="text-xs text-clay-700">{{ $message }}</span>@enderror</label>
                                <label><span class="{{ $label }}">Campus (empty = national)</span>
                                    <input name="items[{{ $i }}][campus]" value="{{ $v('campus') }}" maxlength="255" class="{{ $inputClass }}"></label>
                                <label><span class="{{ $label }}">Level</span>
                                    <input name="items[{{ $i }}][level]" value="{{ $v('level') }}" maxlength="255" class="{{ $inputClass }}"></label>
                                <label><span class="{{ $label }}">Minimum GPA (4.0)</span>
                                    <input type="number" step="0.01" min="0" max="4" name="items[{{ $i }}][min_gpa]" value="{{ $v('min_gpa') }}" class="{{ $inputClass }}">
                                    @error("items.$i.min_gpa")<span class="text-xs text-clay-700">{{ $message }}</span>@enderror</label>
                                <label><span class="{{ $label }}">Maximum age</span>
                                    <input type="number" min="1" name="items[{{ $i }}][max_age]" value="{{ $v('max_age') }}" class="{{ $inputClass }}"></label>
                                <label><span class="{{ $label }}">Minimum semester</span>
                                    <input type="number" min="1" name="items[{{ $i }}][min_semester]" value="{{ $v('min_semester') }}" class="{{ $inputClass }}"></label>
                                <label><span class="{{ $label }}">Maximum semester</span>
                                    <input type="number" min="1" name="items[{{ $i }}][max_semester]" value="{{ $v('max_semester') }}" class="{{ $inputClass }}"></label>
                                <label><span class="{{ $label }}">Financial need required</span>
                                    @php($fn = (string) $v('requires_financial_need'))
                                    <select name="items[{{ $i }}][requires_financial_need]" class="{{ $inputClass }}">
                                        <option value="">Not stated</option>
                                        <option value="1" @selected($fn === '1')>Yes</option>
                                        <option value="0" @selected($fn === '0')>No</option>
                                    </select></label>
                                <label><span class="{{ $label }}">Type</span>
                                    @php($type = $v('scholarship_type'))
                                    <select name="items[{{ $i }}][scholarship_type]" class="{{ $inputClass }}">
                                        <option value="">Not stated</option>
                                        <option value="full" @selected($type === 'full')>Full</option>
                                        <option value="partial" @selected($type === 'partial')>Partial</option>
                                    </select></label>
                                <label><span class="{{ $label }}">English test</span>
                                    <input name="items[{{ $i }}][english_test_type]" value="{{ $v('english_test_type') }}" maxlength="255" class="{{ $inputClass }}"></label>
                                <label><span class="{{ $label }}">Minimum English score</span>
                                    <input type="number" step="0.1" min="0" name="items[{{ $i }}][min_english_score]" value="{{ $v('min_english_score') }}" class="{{ $inputClass }}"></label>
                                <label><span class="{{ $label }}">Majors (one per line, empty = any)</span>
                                    <textarea name="items[{{ $i }}][majors]" rows="4" class="{{ $inputClass }}">{{ $list($v('majors')) }}</textarea></label>
                                <label><span class="{{ $label }}">Nationalities (one per line, empty = any)</span>
                                    <textarea name="items[{{ $i }}][allowed_nationalities]" rows="2" class="{{ $inputClass }}">{{ $list($v('allowed_nationalities')) }}</textarea></label>
                                <label><span class="{{ $label }}">Location *</span>
                                    @php($abroad = (string) ($v('is_abroad') === null ? 0 : (int) $v('is_abroad')))
                                    <select name="items[{{ $i }}][is_abroad]" required class="{{ $inputClass }}">
                                        <option value="0" @selected($abroad === '0')>Domestic</option>
                                        <option value="1" @selected($abroad === '1')>Abroad</option>
                                    </select></label>
                                <span></span>
                                <label><span class="{{ $label }}">Opens</span>
                                    <input type="date" name="items[{{ $i }}][open_date]" value="{{ $v('open_date') }}" class="{{ $inputClass }}"></label>
                                <label><span class="{{ $label }}">Deadline</span>
                                    <input type="date" name="items[{{ $i }}][deadline]" value="{{ $v('deadline') }}" class="{{ $inputClass }}"></label>
                                <label class="sm:col-span-2"><span class="{{ $label }}">Other requirements</span>
                                    <textarea name="items[{{ $i }}][other_requirements]" rows="2" class="{{ $inputClass }}">{{ $v('other_requirements') }}</textarea></label>
                                <label class="sm:col-span-2"><span class="{{ $label }}">Required documents (one per line)</span>
                                    <textarea name="items[{{ $i }}][required_documents]" rows="2" class="{{ $inputClass }}">{{ $list($v('required_documents')) }}</textarea></label>
                                <label class="sm:col-span-2"><span class="{{ $label }}">Benefits</span>
                                    <textarea name="items[{{ $i }}][benefits]" rows="2" class="{{ $inputClass }}">{{ $v('benefits') }}</textarea></label>
                                <label class="sm:col-span-2"><span class="{{ $label }}">Official URL * (confirm it on the provider's site)</span>
                                    <input type="url" name="items[{{ $i }}][official_url]" value="{{ $v('official_url') }}" required maxlength="255" class="{{ $inputClass }}">
                                    @error("items.$i.official_url")<span class="text-xs text-clay-700">{{ $message }}</span>@enderror</label>
                                <label class="sm:col-span-2"><span class="{{ $label }}">Contact (one per line: email, phone, Instagram, link...)</span>
                                    <textarea name="items[{{ $i }}][contact_info]" rows="2" maxlength="1000" class="{{ $inputClass }}">{{ $v('contact_info') }}</textarea></label>
                                <label><span class="{{ $label }}">Last verified * (the date you checked the official site)</span>
                                    <input type="date" name="items[{{ $i }}][last_verified]" value="{{ old("items.$i.last_verified") }}" required class="{{ $inputClass }}">
                                    @error("items.$i.last_verified")<span class="text-xs text-clay-700">{{ $message }}</span>@enderror</label>
                            </div>
                        </section>
                    @endforeach

                    <div class="flex flex-wrap items-center gap-4">
                        <button type="submit" class="inline-flex rounded-full bg-aura-gradient px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:brightness-105">Save reviewed scholarships</button>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('discard').submit();" class="text-sm font-semibold text-clay-700 hover:text-clay-900">Discard these drafts</a>
                    </div>
                </form>
                <form id="discard" method="POST" action="{{ route('admin.scholarships.import.discard') }}" class="hidden">@csrf @method('DELETE')</form>
            </div>
        @endunless
    </div>
</x-app-layout>
