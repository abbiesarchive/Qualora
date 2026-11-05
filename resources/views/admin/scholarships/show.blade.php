<x-app-layout>
    <x-slot name="title">{{ $scholarship->name }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 pb-16">
        <a href="{{ route('admin.scholarships.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-900">&larr; Back to scholarships</a>

        @if (session('status'))
            <div role="status" class="mt-5 rounded-xl bg-sage-100 px-4 py-3 text-sm font-medium text-sage-800">{{ session('status') }}</div>
        @endif

        <article class="mt-5 rounded-2xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-aura-700">{{ $scholarship->provider }}</p>
                    <h1 class="mt-1 font-display text-3xl font-semibold tracking-tight">{{ $scholarship->name }}</h1>
                </div>
                <span @class([
                    'rounded-full px-3 py-1 text-sm font-semibold',
                    'bg-sage-100 text-sage-800' => $scholarship->is_active,
                    'bg-clay-100 text-clay-800' => ! $scholarship->is_active,
                ])>{{ $scholarship->is_active ? 'Active' : 'Inactive' }}</span>
            </div>

            <dl class="mt-8 grid gap-5 sm:grid-cols-2">
                @foreach ([
                    'Year' => $scholarship->year,
                    'Type' => $scholarship->scholarship_type ? ucfirst($scholarship->scholarship_type) : null,
                    'Minimum GPA' => $scholarship->min_gpa,
                    'Open date' => $scholarship->open_date?->format('j M Y'),
                    'Deadline' => $scholarship->deadline?->format('j M Y'),
                    'Education level' => $scholarship->level,
                    'Campus' => $scholarship->campus,
                    'Minimum semester' => $scholarship->min_semester,
                    'Maximum semester' => $scholarship->max_semester,
                    'Maximum age' => $scholarship->max_age,
                    'Financial need required' => $scholarship->requires_financial_need === null ? null : ($scholarship->requires_financial_need ? 'Yes' : 'No'),
                    'Scholarship location' => $scholarship->is_abroad ? 'Abroad' : 'Domestic',
                    'English test' => $scholarship->english_test_type,
                    'Minimum English score' => $scholarship->min_english_score,
                    'Allowed majors' => $scholarship->majors ? implode(', ', $scholarship->majors) : null,
                    'Allowed nationalities' => $scholarship->allowed_nationalities ? implode(', ', $scholarship->allowed_nationalities) : null,
                    'Last verified' => $scholarship->last_verified?->format('j M Y'),
                ] as $label => $value)
                    <div>
                        <dt class="text-sm text-ink-soft">{{ $label }}</dt>
                        <dd class="mt-1 font-semibold">{{ filled($value) ? $value : '—' }}</dd>
                    </div>
                @endforeach
                <div class="sm:col-span-2">
                    <dt class="text-sm text-ink-soft">Official URL</dt>
                    <dd class="mt-1 break-all font-semibold">
                        <a href="{{ $scholarship->official_url }}" target="_blank" rel="noopener noreferrer" class="text-brand-700 hover:underline">{{ $scholarship->official_url }}</a>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm text-ink-soft">Benefits</dt>
                    <dd class="mt-1 whitespace-pre-line">{{ $scholarship->benefits ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm text-ink-soft">Other requirements</dt>
                    <dd class="mt-1 whitespace-pre-line">{{ $scholarship->other_requirements ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm text-ink-soft">Required documents</dt>
                    @if ($scholarship->required_documents)
                        <dd class="mt-1">{{ implode(', ', $scholarship->required_documents) }}</dd>
                    @else
                        <dd class="mt-1">—</dd>
                    @endif
                </div>
            </dl>

            <div class="mt-8 flex flex-wrap gap-3 border-t border-cream-dark pt-6">
                <a href="{{ route('admin.scholarships.edit', $scholarship) }}" class="inline-flex items-center justify-center rounded-full bg-aura-gradient px-5 py-2.5 text-sm font-semibold text-white hover:brightness-105">Edit scholarship</a>
                <form method="POST" action="{{ route('admin.scholarships.destroy', $scholarship) }}" onsubmit="return confirm('Delete this scholarship? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center rounded-full border border-clay-200 px-5 py-2.5 text-sm font-semibold text-clay-700 hover:bg-clay-50">Delete scholarship</button>
                </form>
            </div>
        </article>
    </div>
</x-app-layout>
