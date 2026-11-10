<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account details and student information used for scholarship recommendations.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="border-t border-cream-dark pt-6">
            <h3 class="text-lg font-medium text-gray-900">{{ __('Student Information') }}</h3>
            <p class="mt-1 text-sm text-gray-600">{{ __('These details are optional and can be updated at any time.') }}</p>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="campus" :value="__('Campus')" />
                    <x-text-input id="campus" name="campus" type="text" class="mt-1 block w-full" :value="old('campus', $profile?->campus)" maxlength="255" />
                    <x-input-error class="mt-2" :messages="$errors->get('campus')" />
                </div>

                <div>
                    <x-input-label for="major" :value="__('Major')" />
                    <x-text-input id="major" name="major" type="text" class="mt-1 block w-full" :value="old('major', $profile?->major)" maxlength="255" />
                    <x-input-error class="mt-2" :messages="$errors->get('major')" />
                </div>

                <div>
                    <x-input-label for="semester" :value="__('Current Semester')" />
                    <x-text-input id="semester" name="semester" type="number" min="1" max="255" class="mt-1 block w-full" :value="old('semester', $profile?->semester)" />
                    <x-input-error class="mt-2" :messages="$errors->get('semester')" />
                </div>

                <div>
                    <x-input-label for="gpa" :value="__('GPA (4.0 scale)')" />
                    <x-text-input id="gpa" name="gpa" type="number" min="0" max="4" step="0.01" class="mt-1 block w-full" :value="old('gpa', $profile?->gpa)" />
                    <x-input-error class="mt-2" :messages="$errors->get('gpa')" />
                </div>

                <div>
                    <x-input-label for="age" :value="__('Age')" />
                    <x-text-input id="age" name="age" type="number" min="1" max="255" class="mt-1 block w-full" :value="old('age', $profile?->age)" />
                    <x-input-error class="mt-2" :messages="$errors->get('age')" />
                </div>

                <div>
                    <x-input-label for="nationality" :value="__('Nationality')" />
                    <x-text-input id="nationality" name="nationality" type="text" class="mt-1 block w-full" :value="old('nationality', $profile?->nationality)" maxlength="255" />
                    <x-input-error class="mt-2" :messages="$errors->get('nationality')" />
                </div>

                <div>
                    <x-input-label for="has_financial_need" :value="__('Do you have financial need?')" />
                    @php($financialNeed = (string) old('has_financial_need', $profile?->has_financial_need === null ? '' : (int) $profile->has_financial_need))
                    <select id="has_financial_need" name="has_financial_need" class="mt-1 block w-full rounded-xl border-cream-dark bg-white shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="" @selected($financialNeed === '')>{{ __('Prefer not to say') }}</option>
                        <option value="1" @selected($financialNeed === '1')>{{ __('Yes') }}</option>
                        <option value="0" @selected($financialNeed === '0')>{{ __('No') }}</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('has_financial_need')" />
                </div>

                <div>
                    <x-input-label for="english_test_type" :value="__('English Test')" />
                    <x-text-input id="english_test_type" name="english_test_type" type="text" class="mt-1 block w-full" :value="old('english_test_type', $profile?->english_test_type)" placeholder="IELTS, TOEFL, etc." maxlength="255" />
                    <x-input-error class="mt-2" :messages="$errors->get('english_test_type')" />
                </div>

                <div>
                    <x-input-label for="english_score" :value="__('English Test Score')" />
                    <x-text-input id="english_score" name="english_score" type="number" min="0" max="9999.9" step="0.1" class="mt-1 block w-full" :value="old('english_score', $profile?->english_score)" />
                    <x-input-error class="mt-2" :messages="$errors->get('english_score')" />
                </div>

                <div>
                    <x-input-label for="preferred_scholarship_type" :value="__('Preferred Scholarship Type')" />
                    @php($preferredType = old('preferred_scholarship_type', $profile?->preferred_scholarship_type))
                    <select id="preferred_scholarship_type" name="preferred_scholarship_type" class="mt-1 block w-full rounded-xl border-cream-dark bg-white shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="" @selected($preferredType === '')>{{ __('No preference') }}</option>
                        <option value="full" @selected($preferredType === 'full')>{{ __('Full') }}</option>
                        <option value="partial" @selected($preferredType === 'partial')>{{ __('Partial') }}</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('preferred_scholarship_type')" />
                </div>

                <div>
                    <x-input-label for="open_to_abroad" :value="__('Open to studying abroad?')" />
                    @php($openToAbroad = (string) old('open_to_abroad', $profile?->open_to_abroad === null ? '' : (int) $profile->open_to_abroad))
                    <select id="open_to_abroad" name="open_to_abroad" class="mt-1 block w-full rounded-xl border-cream-dark bg-white shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="" @selected($openToAbroad === '')>{{ __('No preference') }}</option>
                        <option value="1" @selected($openToAbroad === '1')>{{ __('Yes') }}</option>
                        <option value="0" @selected($openToAbroad === '0')>{{ __('No') }}</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('open_to_abroad')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="achievements" :value="__('Achievements')" />
                    <textarea id="achievements" name="achievements" rows="3" class="mt-1 block w-full rounded-xl border-cream-dark shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('achievements', $profile?->achievements) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('achievements')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="organizations" :value="__('Organizations and Activities')" />
                    <textarea id="organizations" name="organizations" rows="3" class="mt-1 block w-full rounded-xl border-cream-dark shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('organizations', $profile?->organizations) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('organizations')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="goals" :value="__('Study or Career Goals')" />
                    <textarea id="goals" name="goals" rows="3" class="mt-1 block w-full rounded-xl border-cream-dark shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('goals', $profile?->goals) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('goals')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="circumstances" :value="__('Additional Circumstances (Optional)')" />
                    <textarea id="circumstances" name="circumstances" rows="3" class="mt-1 block w-full rounded-xl border-cream-dark shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('circumstances', $profile?->circumstances) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('circumstances')" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
