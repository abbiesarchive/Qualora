@php
    $selectClass = 'mt-1 block w-full rounded-xl border-cream-dark bg-white shadow-sm focus:border-brand-500 focus:ring-brand-500';
@endphp

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('What are you looking for?') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Optional. This only helps us suggest scholarships that fit your interests. It is not an application, and it never decides whether you may be eligible.') }}
        </p>
    </header>

    {{-- These fields belong to #profile-form, so the one floating Save button saves them too. --}}
    <div class="mt-6 space-y-6">
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <x-input-label for="study_field_category" :value="__('Field of interest')" />
                <select id="study_field_category" name="study_field_category" form="profile-form" class="{{ $selectClass }}">
                    <option value="">{{ __('No preference') }}</option>
                    @foreach (\App\Models\UserStudyPlan::FIELDS as $field)
                        <option value="{{ $field }}" @selected(old('study_field_category', $studyPlan?->study_field_category) === $field)>{{ __($field) }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('study_field_category')" />
            </div>

            <div>
                <x-input-label for="preferred_country" :value="__('Where would you like to study?')" />
                <select id="preferred_country" name="preferred_country" form="profile-form" class="{{ $selectClass }}">
                    <option value="">{{ __('No preference') }}</option>
                    @foreach (\App\Models\UserStudyPlan::LOCATIONS as $location)
                        <option value="{{ $location }}" @selected(old('preferred_country', $studyPlan?->preferred_country) === $location)>{{ __($location) }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('preferred_country')" />
            </div>
        </div>
    </div>
</section>
