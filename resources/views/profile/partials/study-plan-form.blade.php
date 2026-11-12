<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Study Plan') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Add or update your preferred study details. All fields are optional.') }}
        </p>
    </header>

    <form method="post" action="{{ $studyPlan ? route('profile.study-plan.update', $studyPlan->getKey()) : route('profile.study-plan.store') }}" class="mt-6 space-y-6">
        @csrf
        @if ($studyPlan)
            @method('patch')
        @endif

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <x-input-label for="desired_major" :value="__('Desired Major')" />
                <x-text-input id="desired_major" name="desired_major" type="text" class="mt-1 block w-full" :value="old('desired_major', $studyPlan?->desired_major)" />
                <x-input-error class="mt-2" :messages="$errors->get('desired_major')" />
            </div>

            <div>
                <x-input-label for="study_field_category" :value="__('Study Field Category')" />
                <x-text-input id="study_field_category" name="study_field_category" type="text" class="mt-1 block w-full" :value="old('study_field_category', $studyPlan?->study_field_category)" />
                <x-input-error class="mt-2" :messages="$errors->get('study_field_category')" />
            </div>

            <div>
                <x-input-label for="preferred_country" :value="__('Preferred Country')" />
                <x-text-input id="preferred_country" name="preferred_country" type="text" class="mt-1 block w-full" :value="old('preferred_country', $studyPlan?->preferred_country)" />
                <x-input-error class="mt-2" :messages="$errors->get('preferred_country')" />
            </div>

            <div>
                <x-input-label for="preferred_university" :value="__('Preferred University')" />
                <x-text-input id="preferred_university" name="preferred_university" type="text" class="mt-1 block w-full" :value="old('preferred_university', $studyPlan?->preferred_university)" />
                <x-input-error class="mt-2" :messages="$errors->get('preferred_university')" />
            </div>

            <div>
                <x-input-label for="study_mode" :value="__('Study Mode')" />
                <x-text-input id="study_mode" name="study_mode" type="text" class="mt-1 block w-full" :value="old('study_mode', $studyPlan?->study_mode)" />
                <x-input-error class="mt-2" :messages="$errors->get('study_mode')" />
            </div>

            <div>
                <x-input-label for="preferred_start_year" :value="__('Preferred Start Year')" />
                <x-text-input id="preferred_start_year" name="preferred_start_year" type="number" min="1" max="9999" class="mt-1 block w-full" :value="old('preferred_start_year', $studyPlan?->preferred_start_year)" />
                <x-input-error class="mt-2" :messages="$errors->get('preferred_start_year')" />
            </div>

            <div>
                <x-input-label for="preferred_duration" :value="__('Preferred Duration')" />
                <x-text-input id="preferred_duration" name="preferred_duration" type="text" class="mt-1 block w-full" :value="old('preferred_duration', $studyPlan?->preferred_duration)" />
                <x-input-error class="mt-2" :messages="$errors->get('preferred_duration')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ $studyPlan ? __('Update Study Plan') : __('Save Study Plan') }}</x-primary-button>

            @if (session('status') === 'study-plan-created' || session('status') === 'study-plan-updated')
                <p class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @elseif (session('status') === 'study-plan-already-exists')
                <p class="text-sm text-amber-700">{{ __('A study plan already exists. Update the existing plan instead.') }}</p>
            @endif
        </div>
    </form>
</section>
