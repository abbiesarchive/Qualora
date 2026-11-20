<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    @php
        // The form starts "unsaved" when the AI pre-filled it or validation sent it back,
        // because those values are not in the database yet.
        $pendingPrefill = count(session('ai_prefill', []));
        $startUnsaved = $pendingPrefill > 0 || $errors->hasAny(['name', 'email', 'campus', 'major', 'semester', 'gpa', 'age', 'nationality', 'has_financial_need', 'study_field_category', 'preferred_country']);
    @endphp

    <div class="py-12 pb-32"
        x-data="{
            dirty: false,
            forced: @js((bool) $startUnsaved),
            baseline: '',
            submitting: false,
            showLeave: false,
            leaveUrl: null,
            snapshot() {
                const form = document.getElementById('profile-form');
                return JSON.stringify([...new FormData(form)].filter(([k]) => k !== '_token' && k !== '_method'));
            },
            check() { this.dirty = this.forced || this.snapshot() !== this.baseline; },
            init() {
                this.baseline = this.snapshot();
                this.dirty = this.forced;
                document.getElementById('profile-form').addEventListener('submit', () => { this.submitting = true; });
                // Reload, close tab or typed address: the browser shows its own generic warning.
                window.addEventListener('beforeunload', (e) => {
                    if (this.dirty && !this.submitting) { e.preventDefault(); e.returnValue = ''; }
                });
                // In-app links: show our own warning instead.
                document.addEventListener('click', (e) => {
                    const link = e.target.closest('a[href]');
                    if (!this.dirty || this.submitting || !link) return;
                    if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
                    if (link.target === '_blank' || link.hasAttribute('download')) return;
                    const url = new URL(link.href, location.href);
                    if (url.protocol === 'javascript:' || (url.pathname === location.pathname && url.search === location.search)) return;
                    e.preventDefault();
                    this.leaveUrl = link.href;
                    this.showLeave = true;
                }, true);
            },
            leave() { this.submitting = true; window.location.href = this.leaveUrl; },
        }"
        x-on:input.window="check()"
        x-on:change.window="check()"
        x-on:keydown.escape.window="showLeave = false"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- AI profile fill: pre-fills the form below. Nothing is saved until the student presses Save. --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" x-data="{ loading: false }">
                <h3 class="text-lg font-medium text-gray-900">{{ __('Tell us about yourself') }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ __('Write a few sentences in your own words and we will fill in the form below for you to check. For example: your major, semester, GPA, age and campus.') }}</p>

                <div class="mt-4 rounded-2xl bg-cream/40 p-4 text-sm text-gray-700">
                    <p class="font-semibold text-gray-900">{{ __('What you can mention') }}</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li>{{ __('Your university or campus and your major') }}</li>
                        <li>{{ __('Your current semester (a number, like "semester 5")') }}</li>
                        <li>{{ __('Your GPA on a 4.0 scale (like "GPA 3.4 out of 4")') }}</li>
                        <li>{{ __('Your age and nationality') }}</li>
                        <li>{{ __('Whether you need financial support, only if you want to say so') }}</li>
                        <li>{{ __('Language tests and scores (like "IELTS 6.5")') }}</li>
                        <li>{{ __('Organizations, internships, jobs, volunteering, competitions or projects') }}</li>
                        <li>{{ __('What you would like to study and where, if you have a preference') }}</li>
                    </ul>
                    <p class="mt-3 text-xs text-gray-500">{{ __('We only fill in what you actually write. If a GPA uses another scale (like 10 or 100), or you only mention your year, we leave it blank so you can add it yourself. Nothing is saved until you check it and press Save.') }}</p>
                </div>

                @if (session('ai_error'))
                    <p class="mt-4 rounded-2xl bg-clay-50 p-3 text-sm text-clay-800" role="alert">{{ session('ai_error') }}</p>
                @endif

                @if (session('ai_added') !== null)
                    <p class="mt-4 rounded-2xl bg-sage-50 p-3 text-sm text-sage-900" role="status">
                        {{ trans_choice('{0} Nothing was added.|{1} Added :count item to your profile.|[2,*] Added :count items to your profile.', (int) session('ai_added'), ['count' => (int) session('ai_added')]) }}
                    </p>
                @endif

                @if ($pendingPrefill > 0)
                    <p class="mt-4 rounded-2xl bg-sage-50 p-3 text-sm text-sage-900" role="status">
                        {{ trans_choice('{1} We filled in :count detail. Please check it below, then press Save.|[2,*] We filled in :count details. Please check them below, then press Save.', $pendingPrefill, ['count' => $pendingPrefill]) }}
                    </p>
                @endif

                <form method="POST" action="{{ route('ai.extract-profile') }}" class="mt-4" @submit="loading = true">
                    @csrf
                    <textarea name="text" rows="4" minlength="10" maxlength="4000" required
                        class="block w-full rounded-xl border-cream-dark bg-white shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        placeholder="{{ __('e.g. I am a 5th semester Informatics student, 20 years old, with a GPA of 3.4.') }}">{{ old('text') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('text')" />

                    <p class="mt-2 text-xs text-gray-500">{{ __('Your text is sent to Google\'s Gemini AI to read it, and Qualora does not store it. Please leave out anything you would not want an AI service to process. You can always fill in the form by hand.') }}</p>

                    <button type="submit" x-bind:disabled="loading"
                        class="mt-3 inline-flex items-center gap-2 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:opacity-60">
                        <span x-show="!loading">{{ __('Fill in my profile') }}</span>
                        <span x-show="loading" x-cloak>{{ __('Reading your text') }}</span>
                    </button>
                </form>
            </div>

            {{-- Suggested languages / experiences. Nothing is added until the student ticks and confirms. --}}
            @php($suggestions = session('ai_suggestions'))
            @if (! empty($suggestions['languages']) || ! empty($suggestions['experiences']))
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" id="ai-suggestions">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('We also found these') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Untick anything that is not right, then add the rest to your profile. Nothing is added until you press the button.') }}</p>

                    <form method="POST" action="{{ route('ai.suggestions') }}" class="mt-4 space-y-5">
                        @csrf

                        @if (! empty($suggestions['languages']))
                            <fieldset>
                                <legend class="text-sm font-semibold text-gray-900">{{ __('Language') }}</legend>
                                <div class="mt-2 space-y-2">
                                    @foreach ($suggestions['languages'] as $i => $language)
                                        <label class="flex items-start gap-3 rounded-2xl border border-cream-dark p-3 text-sm">
                                            <input type="checkbox" name="languages[]" value="{{ $i }}" checked class="mt-1 rounded border-cream-dark text-brand-600 focus:ring-brand-500">
                                            <span>
                                                <span class="font-medium text-gray-900">{{ __($language['language_name']) }}</span>
                                                @if ($language['certificate_type'])
                                                    <span class="text-gray-600">&middot; {{ $language['certificate_type'] }}{{ $language['certificate_score'] ? ' '.$language['certificate_score'] : '' }}</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif

                        @if (! empty($suggestions['experiences']))
                            <fieldset>
                                <legend class="text-sm font-semibold text-gray-900">{{ __('Experience') }}</legend>
                                <div class="mt-2 space-y-2">
                                    @foreach ($suggestions['experiences'] as $i => $experience)
                                        <label class="flex items-start gap-3 rounded-2xl border border-cream-dark p-3 text-sm">
                                            <input type="checkbox" name="experiences[]" value="{{ $i }}" checked class="mt-1 rounded border-cream-dark text-brand-600 focus:ring-brand-500">
                                            <span>
                                                <span class="font-medium text-gray-900">{{ $experience['experience_name'] }}</span>
                                                @if ($experience['experience_type'])
                                                    <span class="ml-1 rounded bg-cream-dark px-1.5 py-0.5 text-[10px] font-semibold text-gray-600">{{ __($experience['experience_type']) }}</span>
                                                @endif
                                                @if ($experience['description'])
                                                    <span class="mt-0.5 block text-gray-600">{{ $experience['description'] }}</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif

                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit" class="rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">{{ __('Add selected to my profile') }}</button>
                            <button type="submit" name="dismiss" value="1" class="rounded-full border border-cream-dark px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-cream">{{ __('No thanks') }}</button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="w-full">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="w-full">
                    @include('profile.partials.study-plan-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="w-full">
                    @include('profile.partials.languages')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="w-full">
                    @include('profile.partials.experiences')
                </div>
            </div>
        </div>

        {{-- One floating Save for the whole profile form (student info + preferences). --}}
        <div class="pointer-events-none fixed inset-x-0 bottom-4 z-40 flex justify-center px-4">
            <div class="pointer-events-auto flex items-center gap-4 rounded-full bg-white px-5 py-3 shadow-lg ring-1 ring-cream-dark">
                <p class="text-sm" x-bind:class="dirty ? 'font-medium text-amber-700' : 'text-gray-500'" role="status" aria-live="polite">
                    <span x-show="dirty">{{ __('You have unsaved changes') }}</span>
                    <span x-show="!dirty" x-cloak>{{ session('status') === 'profile-updated' ? __('Saved.') : __('No changes yet') }}</span>
                </p>
                <button type="submit" form="profile-form" x-bind:disabled="!dirty"
                    class="rounded-full bg-brand-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500">
                    {{ __('Save') }}
                </button>
            </div>
        </div>

        {{-- Leave warning for in-app links --}}
        <div x-show="showLeave" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4" role="dialog" aria-modal="true" aria-labelledby="leave-title">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-xl" x-on:click.outside="showLeave = false">
                <h3 id="leave-title" class="text-lg font-semibold text-gray-900">{{ __('Leave without saving?') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('You have changes that are not saved yet. If you leave now, they will be lost.') }}</p>
                <div class="mt-5 flex flex-wrap justify-end gap-3">
                    <button type="button" x-on:click="showLeave = false"
                        class="rounded-full bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Stay on this page') }}</button>
                    <button type="button" x-on:click="leave()"
                        class="rounded-full border border-cream-dark px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-cream">{{ __('Leave without saving') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
