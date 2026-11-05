<x-app-layout>
    <x-slot name="title">{{ $scholarship->name }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 pb-16">
        <a href="{{ route('scholarships.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-900">&larr; Back to scholarships</a>

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
                <div>
                    <dt class="text-sm text-ink-soft">Minimum GPA</dt>
                    <dd class="mt-1 font-semibold">{{ $scholarship->min_gpa ?? 'Not specified' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-ink-soft">Scholarship type</dt>
                    <dd class="mt-1 font-semibold">{{ $scholarship->scholarship_type ? ucfirst($scholarship->scholarship_type) : 'Not specified' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-ink-soft">Open date</dt>
                    <dd class="mt-1 font-semibold">{{ $scholarship->open_date?->format('j M Y') ?? 'Not specified' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-ink-soft">Deadline</dt>
                    <dd class="mt-1 font-semibold">{{ $scholarship->deadline?->format('j M Y') ?? 'Not specified' }}</dd>
                </div>
                @if ($scholarship->level)
                    <div>
                        <dt class="text-sm text-ink-soft">Education level</dt>
                        <dd class="mt-1 font-semibold">{{ $scholarship->level }}</dd>
                    </div>
                @endif
                @if ($scholarship->campus)
                    <div>
                        <dt class="text-sm text-ink-soft">Campus</dt>
                        <dd class="mt-1 font-semibold">{{ $scholarship->campus }}</dd>
                    </div>
                @endif
                @if ($scholarship->min_semester || $scholarship->max_semester)
                    <div>
                        <dt class="text-sm text-ink-soft">Eligible semester</dt>
                        <dd class="mt-1 font-semibold">
                            {{ $scholarship->min_semester ?? 'Any' }}–{{ $scholarship->max_semester ?? 'Any' }}
                        </dd>
                    </div>
                @endif
                @if ($scholarship->max_age)
                    <div>
                        <dt class="text-sm text-ink-soft">Maximum age</dt>
                        <dd class="mt-1 font-semibold">{{ $scholarship->max_age }}</dd>
                    </div>
                @endif
            </dl>

            @if ($scholarship->benefits)
                <section class="mt-8 border-t border-cream-dark pt-6">
                    <h2 class="font-display text-xl font-semibold">Benefits</h2>
                    <p class="mt-2 whitespace-pre-line text-ink-soft">{{ $scholarship->benefits }}</p>
                </section>
            @endif

            @if ($scholarship->other_requirements)
                <section class="mt-6">
                    <h2 class="font-display text-xl font-semibold">Other requirements</h2>
                    <p class="mt-2 whitespace-pre-line text-ink-soft">{{ $scholarship->other_requirements }}</p>
                </section>
            @endif

            @if ($scholarship->required_documents)
                <section class="mt-6">
                    <h2 class="font-display text-xl font-semibold">Required documents</h2>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-ink-soft">
                        @foreach ($scholarship->required_documents as $document)
                            <li>{{ $document }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <div class="mt-8 border-t border-cream-dark pt-6">
                <a href="{{ $scholarship->official_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-full bg-aura-gradient px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    Visit official website<span class="sr-only"> for {{ $scholarship->name }}</span> &rarr;
                </a>
                @if ($scholarship->last_verified)
                    <p class="mt-3 text-xs text-ink-soft">Last verified {{ $scholarship->last_verified->format('j M Y') }}. Please confirm details on the official website.</p>
                @endif
            </div>
        </article>
    </div>
</x-app-layout>
