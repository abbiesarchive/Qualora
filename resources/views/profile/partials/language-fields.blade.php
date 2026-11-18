@php
    $selectClass = 'mt-1 block w-full rounded-xl border-cream-dark bg-white shadow-sm focus:border-brand-500 focus:ring-brand-500';
@endphp

<div>
    <x-input-label for="{{ $formId }}-language_name" :value="__('Language')" />
    <select id="{{ $formId }}-language_name" name="language_name" class="{{ $selectClass }}" x-model="form.language_name" required>
        <option value="">{{ __('Select a language') }}</option>
        @foreach (\App\Http\Controllers\UserLanguageController::LANGUAGES as $name)
            <option value="{{ $name }}" @selected(old('language_name', $language?->language_name) === $name)>{{ $name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('language_name')" />
</div>

<div>
    <x-input-label for="{{ $formId }}-certificate_type" :value="__('Test taken')" />
    <select id="{{ $formId }}-certificate_type" name="certificate_type" class="{{ $selectClass }}" x-model="form.certificate_type">
        <option value="">{{ __('No test yet') }}</option>
        @foreach (\App\Http\Controllers\UserLanguageController::TESTS as $test)
            <option value="{{ $test }}" @selected(old('certificate_type', $language?->certificate_type) === $test)>{{ $test }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('certificate_type')" />
</div>

<div>
    <x-input-label for="{{ $formId }}-certificate_score" :value="__('Score')" />
    <x-text-input id="{{ $formId }}-certificate_score" name="certificate_score" type="text" maxlength="20" class="mt-1 block w-full" x-model="form.certificate_score" :value="old('certificate_score', $language?->certificate_score)" />
    <x-input-error class="mt-2" :messages="$errors->get('certificate_score')" />
</div>
