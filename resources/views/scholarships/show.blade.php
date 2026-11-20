<x-app-layout>
    <x-slot name="title">{{ $scholarship->name }}</x-slot>

    <div x-data="{ openTrackModal: false }" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-20">
        {{-- Flash Status Message --}}
        @if (session('status'))
            <div class="mb-6 rounded-2xl bg-sage-50 border border-sage-200 p-4 text-sm font-medium text-sage-900 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-sage-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-brand-700 hover:underline">{{ __('View application tracker') }} &rarr;</a>
            </div>
        @endif

        {{-- Top Back Navigation (<-) --}}
        <div class="mb-4">
            <a href="{{ route('scholarships.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-900 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>{{ __('Back to scholarship list') }}</span>
            </a>
        </div>

        {{-- MAIN HERO CARD (SEPERTI SKETSA GAMBAR) --}}
        <article class="rounded-3xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark shadow-sm">
            {{-- 1. Top Badges (Left) & Bookmark (Right) --}}
            <div class="flex items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-aura-100 px-3.5 py-1 text-xs font-semibold text-aura-800">
                        {{ $scholarship->provider }}
                    </span>
                    @if ($scholarship->scholarship_category)
                        <span class="rounded-full bg-brand-50 px-3.5 py-1 text-xs font-semibold text-brand-700">
                            {{ __($scholarship->scholarship_category) }}
                        </span>
                    @endif
                    @if ($scholarship->scholarship_type)
                        <span class="rounded-full bg-cream-dark px-3.5 py-1 text-xs font-semibold text-ink-soft">
                            {{ __(ucfirst($scholarship->scholarship_type)) }}
                        </span>
                    @endif
                    <span @class([
                        'rounded-full px-3.5 py-1 text-xs font-semibold',
                        'bg-sage-100 text-sage-800' => $scholarship->is_active,
                        'bg-clay-100 text-clay-800' => ! $scholarship->is_active,
                    ])>{{ $scholarship->is_active ? __('Active') : __('Closed') }}</span>
                </div>

                <div class="shrink-0 flex items-center">
                    <x-scholarship-bookmark :scholarship="$scholarship" :is-saved="$isSaved" />
                </div>
            </div>

            {{-- 2. Title --}}
            <h1 class="mt-4 font-display text-2xl sm:text-3xl font-bold tracking-tight text-ink">
                {{ $scholarship->name }}
            </h1>

            {{-- 3. Short description / subtitle --}}
            <p class="mt-2 text-sm text-ink-soft leading-relaxed">
                {{ $scholarship->description ?? $scholarship->benefits ?? __('A higher education scholarship program for outstanding undergraduate students.') }}
            </p>

            {{-- 4. Full-width divider line --}}
            <hr class="my-6 border-cream-dark">

            {{-- 5. Countdown text ("25 days remaining") --}}
            <div>
                <p class="font-display text-2xl sm:text-3xl font-bold {{ ($scholarship->days_until_deadline !== null && $scholarship->days_until_deadline <= 7) ? 'text-clay-600' : 'text-brand-700' }}">
                    @if ($scholarship->days_until_deadline !== null)
                        @if ($scholarship->days_until_deadline > 0)
                            {{ __(':count days remaining', ['count' => $scholarship->days_until_deadline]) }}
                        @elseif ($scholarship->days_until_deadline === 0)
                            {{ __('Application deadline is today!') }}
                        @else
                            {{ __('Applications are closed') }}
                        @endif
                    @else
                        {{ __('Applications are open') }}
                    @endif
                </p>
            </div>

            {{-- 6. Two columns: Deadline (Left) & Announcement (Right) --}}
            <div class="mt-4 grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-bold text-ink-soft uppercase tracking-wider">{{ __('Deadline') }}</p>
                    <p class="mt-1 font-bold text-base sm:text-lg text-ink">
                        {{ $scholarship->deadline?->translatedFormat('d M Y') ?? __('Not specified') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-bold text-ink-soft uppercase tracking-wider">{{ __('Announcement') }}</p>
                    <p class="mt-1 font-bold text-base sm:text-lg text-ink">
                        {{ $scholarship->announcement_date?->translatedFormat('d M Y') ?? __('To be announced') }}
                    </p>
                    @if ($scholarship->days_until_announcement !== null && $scholarship->days_until_announcement > 0)
                        <span class="text-xs font-medium text-aura-700">({{ __(':count days remaining', ['count' => $scholarship->days_until_announcement]) }})</span>
                    @endif
                </div>
            </div>

            {{-- 7. Two pill buttons side-by-side at the bottom: Official website & Apply --}}
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Button 1: Official website --}}
                <a
                    href="{{ $scholarship->apply_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="h-12 rounded-full border-2 border-brand-600 bg-white px-6 font-semibold text-sm sm:text-base text-brand-700 hover:bg-brand-50 flex items-center justify-center gap-2 transition shadow-sm"
                >
                    <span>{{ __('Official website') }}</span>
                    <span class="sr-only">{{ __('Visit official website') }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>

                {{-- Button 2: Apply --}}
                @if ($userApplication)
                    <button
                        type="button"
                        @click="openTrackModal = true"
                        class="h-12 rounded-full bg-brand-50 border-2 border-brand-500 px-6 font-semibold text-sm sm:text-base text-brand-800 hover:bg-brand-100 flex items-center justify-center gap-2 transition shadow-sm"
                    >
                        <span>{{ __('Status: :status', ['status' => $userApplication->status_label]) }}</span>
                        <span class="text-xs underline text-brand-600">({{ __('Update') }})</span>
                    </button>
                @else
                    <button
                        type="button"
                        @click="openTrackModal = true"
                        class="h-12 rounded-full bg-aura-gradient px-6 font-semibold text-sm sm:text-base text-white hover:brightness-105 flex items-center justify-center gap-2 transition shadow-md"
                    >
                        <span>{{ __('Apply') }}</span>
                    </button>
                @endif
            </div>
        </article>

        {{-- DETAIL SECTIONS BELOW THE HERO CARD --}}

        {{-- 1. HOW YOUR PROFILE MATCHES (from EligibilityService, never from AI) --}}
        @php
            $headline = match ($match['status']) {
                'eligible' => [__('You may be eligible'), 'bg-sage-100 text-sage-800'],
                'almost' => [__('Almost there'), 'bg-aura-100 text-aura-800'],
                default => [__('Not a match yet'), 'bg-clay-100 text-clay-800'],
            };
        @endphp
        <section class="mt-10 rounded-3xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark shadow-sm" aria-labelledby="match-heading">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <h2 id="match-heading" class="font-display text-xl font-bold text-ink">{{ __('How your profile matches') }}</h2>
                    <p class="text-xs text-ink-soft mt-0.5">
                        {{ __(':met of :total requirements met.', ['met' => $match['met'], 'total' => $match['total']]) }} {{ __('Always verify on the official site, this is not a guarantee.') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $headline[1] }}">{{ $headline[0] }}</span>
                    @if (! $hasProfile)
                        <a href="{{ route('profile.edit') }}" class="text-xs font-semibold text-brand-700 hover:underline">
                            {{ __('Add my details') }} &rarr;
                        </a>
                    @endif
                </div>
            </div>

            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach ($match['checks'] as $check)
                    @php
                        $state = match (true) {
                            $check['passed'] === true => [__('Met'), 'bg-sage-100 text-sage-800', 'border-sage-200 bg-sage-50/50'],
                            $check['passed'] === false && $check['near_miss'] => [__('Almost'), 'bg-aura-100 text-aura-800', 'border-aura-200 bg-aura-50/50'],
                            $check['passed'] === false => [__('Not met'), 'bg-clay-100 text-clay-800', 'border-clay-200 bg-clay-50/50'],
                            default => [__('Add your data'), 'bg-cream-dark text-ink-soft', 'border-cream-dark bg-white'],
                        };
                    @endphp
                    <li class="rounded-2xl border p-4 flex items-start justify-between gap-3 {{ $state[2] }}">
                        <div>
                            <p class="text-xs font-semibold text-ink-soft">{{ $check['label'] }}</p>
                            <p class="text-sm font-bold text-ink mt-0.5">{{ $check['requirement'] }}</p>
                            <p class="text-xs mt-1 text-ink-soft">{{ $check['detail'] }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $state[1] }}">{{ $state[0] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- 2. SCHOLARSHIP BENEFITS --}}
        @if ($scholarship->benefitsList->isNotEmpty() || $scholarship->benefits)
            <section class="mt-8 rounded-3xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark shadow-sm">
                <h2 class="font-display text-xl font-bold text-ink mb-4">{{ __('Scholarship Benefits & Coverage') }}</h2>
                @if ($scholarship->benefitsList->isNotEmpty())
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($scholarship->benefitsList as $benefit)
                            <div class="rounded-2xl bg-cream/40 p-4 border border-cream-dark">
                                <span class="rounded-full bg-white px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-aura-800 border border-cream-dark">
                                    {{ __($benefit->benefit_type) }}
                                </span>
                                <h3 class="font-semibold text-sm text-ink mt-2">{{ $benefit->benefit_name }}</h3>
                                @if ($benefit->amount)
                                    <p class="text-xs font-bold text-brand-700 mt-1">
                                        {{ $benefit->currency }} {{ number_format($benefit->amount, 0, ',', '.') }}
                                    </p>
                                @endif
                                @if ($benefit->benefit_description)
                                    <p class="text-xs text-ink-soft mt-1">{{ $benefit->benefit_description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm whitespace-pre-line text-ink-soft">{{ $scholarship->benefits }}</p>
                @endif
            </section>
        @endif

        {{-- AI EXPLANATION (stored scholarship data only; generated on button click, then cached) --}}
        <section id="ai-explanation" class="mt-8 rounded-3xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark shadow-sm" x-data="{ loading: false }">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-display text-xl font-bold text-ink">{{ __('Explained simply') }}</h2>
                    <p class="text-xs text-ink-soft">{{ __('AI-written summary based only on the details above. It does not decide whether you qualify.') }}</p>
                </div>
            </div>

            @if (session('ai_error'))
                <p class="mt-4 rounded-2xl bg-clay-50 p-3 text-sm text-clay-800" role="alert">{{ session('ai_error') }}</p>
            @endif

            @if ($scholarship->ai_explanation)
                <p class="mt-4 text-sm leading-relaxed text-ink whitespace-pre-line">{{ $scholarship->ai_explanation }}</p>

                @if (is_array($scholarship->ai_checklist) && $scholarship->ai_checklist)
                    <h3 class="mt-5 text-sm font-bold text-ink">{{ __('Suggested steps') }}</h3>
                    <ol class="mt-2 list-decimal space-y-1 pl-5 text-sm text-ink-soft">
                        @foreach ($scholarship->ai_checklist as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>
                @endif

                <p class="mt-4 text-xs text-ink-soft">{{ __('AI-generated. Please verify every detail on the official site.') }}</p>
            @else
                <form method="POST" action="{{ route('ai.explain') }}" class="mt-4" @submit="loading = true">
                    @csrf
                    <input type="hidden" name="scholarship_id" value="{{ $scholarship->id }}">
                    <button type="submit" x-bind:disabled="loading"
                        class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:opacity-60">
                        <span x-show="!loading">{{ __('Explain this scholarship') }}</span>
                        <span x-show="loading" x-cloak>{{ __('Writing your explanation') }}</span>
                    </button>
                </form>
            @endif
        </section>

        {{-- 3. REQUIRED DOCUMENT CHECKLIST --}}
        <section class="mt-8 rounded-3xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark shadow-sm" x-data="{ checkedDocs: {} }">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="font-display text-xl font-bold text-ink">{{ __('Application Document Checklist') }}</h2>
                    <p class="text-xs text-ink-soft">{{ __('Check off documents you have ready before applying on the official website.') }}</p>
                </div>
            </div>

            @if ($scholarship->documentsList->isNotEmpty())
                <div class="mt-4 space-y-2">
                    @foreach ($scholarship->documentsList as $doc)
                        <label class="flex items-start gap-3 rounded-2xl border border-cream-dark p-3.5 hover:bg-cream/30 transition cursor-pointer">
                            <input
                                type="checkbox"
                                x-model="checkedDocs[{{ $doc->id }}]"
                                class="mt-1 rounded border-cream-dark text-brand-600 focus:ring-brand-500"
                            >
                            <div class="flex-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-sm text-ink" :class="{ 'line-through text-ink-soft': checkedDocs[{{ $doc->id }}] }">
                                        {{ $doc->document_name }}
                                    </span>
                                    @if ($doc->is_required)
                                        <span class="rounded bg-clay-50 px-1.5 py-0.5 text-[10px] font-bold text-clay-700">{{ __('Required') }}</span>
                                    @endif
                                </div>
                                @if ($doc->document_description)
                                    <p class="text-ink-soft mt-0.5">{{ $doc->document_description }}</p>
                                @endif
                            </div>
                            @if ($doc->template_url)
                                <a href="{{ $doc->template_url }}" target="_blank" class="shrink-0 text-xs font-semibold text-brand-700 hover:underline">
                                    {{ __('Download Template') }} &rarr;
                                </a>
                            @endif
                        </label>
                    @endforeach
                </div>
            @elseif ($scholarship->required_documents)
                <ul class="mt-4 space-y-2">
                    @foreach ($scholarship->required_documents as $idx => $doc)
                        <li class="flex items-center gap-3 rounded-2xl border border-cream-dark p-3 text-xs">
                            <input type="checkbox" class="rounded border-cream-dark text-brand-600 focus:ring-brand-500">
                            <span class="font-medium text-ink">{{ $doc }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-ink-soft mt-3">{{ __("Please check the organizer's official website for the complete list of required documents.") }}</p>
            @endif
        </section>

        {{-- 4. SELECTION PROCESS & TIMELINE --}}
        @if ($scholarship->selections->isNotEmpty())
            <section class="mt-8 rounded-3xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark shadow-sm">
                <h2 class="font-display text-xl font-bold text-ink mb-1">{{ __('Selection Process & Timeline') }}</h2>
                <p class="text-xs text-ink-soft mb-6">{{ __('The application process through the announcement of scholarship recipients.') }}</p>

                <div class="relative pl-6 border-l-2 border-brand-200 space-y-6">
                    @foreach ($scholarship->selections as $stage)
                        <div class="relative">
                            <div class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-brand-600 ring-4 ring-white"></div>
                            <div class="rounded-2xl bg-cream/30 p-4 border border-cream-dark">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="font-bold text-sm text-ink">{{ $stage->stage_order }}. {{ $stage->stage_name }}</span>
                                    @if ($stage->estimated_date)
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-aura-800 border border-cream-dark">
                                            {{ $stage->estimated_date->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                                @if ($stage->stage_description)
                                    <p class="text-xs text-ink-soft mt-2">{{ $stage->stage_description }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- MODAL: TRACK MY APPLICATION --}}
        <div
            x-show="openTrackModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                <div
                    x-show="openTrackModal"
                    @click="openTrackModal = false"
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"
                ></div>

                <div
                    x-show="openTrackModal"
                    class="relative transform overflow-hidden rounded-3xl bg-white p-6 text-left shadow-xl transition-all sm:w-full sm:max-w-lg"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-cream-dark">
                        <h3 class="font-display text-lg font-bold text-ink">{{ __('Track My Application') }}</h3>
                        <button type="button" @click="openTrackModal = false" class="text-ink-soft hover:text-ink">
                            &times;
                        </button>
                    </div>

                    <form action="{{ route('applications.store', $scholarship) }}" method="POST" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-ink mb-1">{{ __('Current Application Status') }}</label>
                            <select name="status" class="w-full rounded-xl border-cream-dark text-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="submitted" @selected(($userApplication->status ?? '') === 'submitted')>{{ __('Submitted on the official website') }}</option>
                                <option value="preparing" @selected(($userApplication->status ?? '') === 'preparing')>{{ __('Preparing documents') }}</option>
                                <option value="interview" @selected(($userApplication->status ?? '') === 'interview')>{{ __('Advanced to interview') }}</option>
                                <option value="accepted" @selected(($userApplication->status ?? '') === 'accepted')>{{ __('Accepted for the scholarship') }}</option>
                                <option value="rejected" @selected(($userApplication->status ?? '') === 'rejected')>{{ __('Not selected') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-ink mb-1">{{ __('Date submitted on the official website') }}</label>
                            <input
                                type="date"
                                name="applied_at"
                                value="{{ old('applied_at', $userApplication?->applied_at?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                                class="w-full rounded-xl border-cream-dark text-sm focus:border-brand-500 focus:ring-brand-500"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-ink mb-1">{{ __('Registration number / Application ID (Optional)') }}</label>
                            <input
                                type="text"
                                name="registration_number"
                                placeholder="{{ __('e.g. BU-2026-98124') }}"
                                value="{{ old('registration_number', $userApplication?->registration_number ?? '') }}"
                                class="w-full rounded-xl border-cream-dark text-sm focus:border-brand-500 focus:ring-brand-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-ink mb-1">{{ __('Personal notes (Optional)') }}</label>
                            <textarea
                                name="notes"
                                rows="3"
                                placeholder="{{ __('e.g. Registered with my university email; interview scheduled for Dec 15 at 10 AM via Zoom.') }}"
                                class="w-full rounded-xl border-cream-dark text-sm focus:border-brand-500 focus:ring-brand-500"
                            >{{ old('notes', $userApplication?->notes ?? '') }}</textarea>
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-2 border-t border-cream-dark">
                            <button
                                type="button"
                                @click="openTrackModal = false"
                                class="rounded-full px-4 py-2 text-xs font-semibold text-ink-soft hover:bg-cream"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="rounded-full bg-aura-gradient px-5 py-2 text-xs font-semibold text-white shadow hover:brightness-105"
                            >
                                {{ __('Save to tracker') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
