<form
    method="POST"
    action="{{ $isEdit ? route('admin.scholarships.update', $scholarship) : route('admin.scholarships.store') }}"
    class="mt-6 space-y-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div role="alert" class="rounded-xl bg-clay-100 px-4 py-3 text-sm text-clay-800">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-2xl bg-white p-6 ring-1 ring-cream-dark">
        <h2 class="font-display text-xl font-semibold">Basic information</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <label class="sm:col-span-2">
                <span class="mb-1 block text-sm font-medium">Scholarship name *</span>
                <input name="name" required maxlength="255" value="{{ old('name', $scholarship->name) }}" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Provider *</span>
                <input name="provider" required maxlength="255" value="{{ old('provider', $scholarship->provider) }}" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Education level</span>
                <input name="level" maxlength="255" value="{{ old('level', $scholarship->level) }}" placeholder="e.g. S1" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Campus</span>
                <input name="campus" maxlength="255" value="{{ old('campus', $scholarship->campus) }}" placeholder="Leave empty for national" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Scholarship type</span>
                @php($type = old('scholarship_type', $scholarship->scholarship_type))
                <select name="scholarship_type" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Not specified</option>
                    <option value="full" @selected($type === 'full')>Full</option>
                    <option value="partial" @selected($type === 'partial')>Partial</option>
                </select>
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Year *</span>
                <input type="number" name="year" required min="2000" max="65535" value="{{ old('year', $scholarship->year ?? today()->year) }}" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Status *</span>
                @php($active = (string) old('is_active', $scholarship->exists ? (int) $scholarship->is_active : 1))
                <select name="is_active" required class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
                    <option value="1" @selected($active === '1')>Active</option>
                    <option value="0" @selected($active === '0')>Inactive</option>
                </select>
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Scholarship location *</span>
                @php($abroad = (string) old('is_abroad', $scholarship->exists ? (int) $scholarship->is_abroad : 0))
                <select name="is_abroad" required class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
                    <option value="0" @selected($abroad === '0')>Domestic</option>
                    <option value="1" @selected($abroad === '1')>Abroad</option>
                </select>
            </label>
        </div>
    </section>

    <section class="rounded-2xl bg-white p-6 ring-1 ring-cream-dark">
        <h2 class="font-display text-xl font-semibold">Eligibility</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <label>
                <span class="mb-1 block text-sm font-medium">Minimum GPA</span>
                <input type="number" name="min_gpa" min="0" max="9.99" step="0.01" value="{{ old('min_gpa', $scholarship->min_gpa) }}" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Financial need</span>
                @php($need = (string) old('requires_financial_need', $scholarship->requires_financial_need === null ? '' : (int) $scholarship->requires_financial_need))
                <select name="requires_financial_need" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
                    <option value="" @selected($need === '')>Not specified</option>
                    <option value="1" @selected($need === '1')>Required</option>
                    <option value="0" @selected($need === '0')>Not required</option>
                </select>
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Minimum semester</span>
                <input type="number" name="min_semester" min="1" max="255" value="{{ old('min_semester', $scholarship->min_semester) }}" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Maximum semester</span>
                <input type="number" name="max_semester" min="1" max="255" value="{{ old('max_semester', $scholarship->max_semester) }}" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Maximum age</span>
                <input type="number" name="max_age" min="1" max="255" value="{{ old('max_age', $scholarship->max_age) }}" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Major options</span>
                <textarea name="majors" rows="2" placeholder="Separate items with commas or new lines" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">{{ old('majors', implode(', ', $scholarship->majors ?? [])) }}</textarea>
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Allowed nationalities</span>
                <textarea name="allowed_nationalities" rows="2" placeholder="Separate items with commas or new lines" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">{{ old('allowed_nationalities', implode(', ', $scholarship->allowed_nationalities ?? [])) }}</textarea>
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">English test type</span>
                <input name="english_test_type" maxlength="255" value="{{ old('english_test_type', $scholarship->english_test_type) }}" placeholder="e.g. IELTS or TOEFL" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Minimum English test score</span>
                <input type="number" name="min_english_score" min="0" max="9999.9" step="0.1" value="{{ old('min_english_score', $scholarship->min_english_score) }}" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
        </div>
    </section>

    <section class="rounded-2xl bg-white p-6 ring-1 ring-cream-dark">
        <h2 class="font-display text-xl font-semibold">Dates and details</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <label>
                <span class="mb-1 block text-sm font-medium">Open date</span>
                <input type="date" name="open_date" value="{{ old('open_date', $scholarship->open_date?->format('Y-m-d')) }}" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Deadline</span>
                <input type="date" name="deadline" value="{{ old('deadline', $scholarship->deadline?->format('Y-m-d')) }}" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Last verified *</span>
                <input type="date" name="last_verified" required value="{{ old('last_verified', $scholarship->last_verified?->format('Y-m-d') ?? today()->format('Y-m-d')) }}" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Official URL *</span>
                <input type="url" name="official_url" required maxlength="255" value="{{ old('official_url', $scholarship->official_url) }}" placeholder="https://" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">
            </label>
            <label class="sm:col-span-2">
                <span class="mb-1 block text-sm font-medium">Benefits</span>
                <textarea name="benefits" rows="3" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">{{ old('benefits', $scholarship->benefits) }}</textarea>
            </label>
            <label class="sm:col-span-2">
                <span class="mb-1 block text-sm font-medium">Other requirements</span>
                <textarea name="other_requirements" rows="3" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">{{ old('other_requirements', $scholarship->other_requirements) }}</textarea>
            </label>
            <label class="sm:col-span-2">
                <span class="mb-1 block text-sm font-medium">Required documents</span>
                <textarea name="required_documents" rows="3" placeholder="Separate items with commas or new lines" class="w-full rounded-xl border-cream-dark focus:border-brand-500 focus:ring-brand-500">{{ old('required_documents', implode(', ', $scholarship->required_documents ?? [])) }}</textarea>
            </label>
        </div>
    </section>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-aura-gradient px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
            {{ $isEdit ? 'Save changes' : 'Add scholarship' }}
        </button>
        <a href="{{ route('admin.scholarships.index') }}" class="inline-flex items-center justify-center rounded-full border border-cream-dark px-6 py-2.5 text-sm font-semibold text-ink hover:bg-white">Cancel</a>
    </div>
</form>
