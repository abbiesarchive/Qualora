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

    <form method="post" action="{{ $studyPlan ? route('profile.study-plan.update', $studyPlan->getKey()) : route('profile.study-plan.store') }}" class="mt-6 space-y-6">
        @csrf
        @if ($studyPlan)
            @method('patch')
        @endif

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <x-input-label for="study_field_category" :value="__('Field of interest')" />
                <select id="study_field_category" name="study_field_category" class="{{ $selectClass }}">
                    <option value="">{{ __('No preference') }}</option>
                    @foreach (\App\Models\UserStudyPlan::FIELDS as $field)
                        <option value="{{ $field }}" @selected(old('study_field_category', $studyPlan?->study_field_category) === $field)>{{ $field }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('study_field_category')" />
            </div>

            <div>
                <x-input-label for="preferred_country" :value="__('Where would you like to study?')" />
                <select id="preferred_country" name="preferred_country" class="{{ $selectClass }}">
                    <option value="">{{ __('No preference') }}</option>
                    @foreach (\App\Models\UserStudyPlan::LOCATIONS as $location)
                        <option value="{{ $location }}" @selected(old('preferred_country', $studyPlan?->preferred_country) === $location)>{{ $location }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('preferred_country')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save preferences') }}</x-primary-button>

            @if (session('status') === 'study-plan-created' || session('status') === 'study-plan-updated')
                <p class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @elseif (session('status') === 'study-plan-already-exists')
                <p class="text-sm text-amber-700">{{ __('Your preferences already exist. Update them instead.') }}</p>
            @endif
        </div>
    </form>
</section>
