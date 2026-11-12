<div>
    <x-input-label for="{{ $formId }}-language_name" :value="__('Language Name')" />
    <x-text-input id="{{ $formId }}-language_name" name="language_name" type="text" class="mt-1 block w-full" x-model="form.language_name" :value="old('language_name', $language?->language_name)" required />
    <x-input-error class="mt-2" :messages="$errors->get('language_name')" />
</div>

<div>
    <x-input-label for="{{ $formId }}-proficiency_level" :value="__('Proficiency Level')" />
    <select id="{{ $formId }}-proficiency_level" name="proficiency_level" class="mt-1 block w-full rounded-xl border-cream-dark bg-white shadow-sm focus:border-brand-500 focus:ring-brand-500" x-model="form.proficiency_level">
        <option value="">{{ __('Select a level') }}</option>
        @foreach (['Beginner', 'Elementary', 'Intermediate', 'Upper Intermediate', 'Advanced', 'Proficient / Native'] as $level)
            <option value="{{ $level }}" @selected(old('proficiency_level', $language?->proficiency_level) === $level)>{{ $level }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('proficiency_level')" />
</div>

<div>
    <x-input-label for="{{ $formId }}-certificate_type" :value="__('Certificate Type')" />
    <x-text-input id="{{ $formId }}-certificate_type" name="certificate_type" type="text" class="mt-1 block w-full" x-model="form.certificate_type" :value="old('certificate_type', $language?->certificate_type)" />
    <x-input-error class="mt-2" :messages="$errors->get('certificate_type')" />
</div>

<div>
    <x-input-label for="{{ $formId }}-certificate_score" :value="__('Certificate Score')" />
    <x-text-input id="{{ $formId }}-certificate_score" name="certificate_score" type="text" class="mt-1 block w-full" x-model="form.certificate_score" :value="old('certificate_score', $language?->certificate_score)" />
    <x-input-error class="mt-2" :messages="$errors->get('certificate_score')" />
</div>

<div>
    <x-input-label for="{{ $formId }}-certificate_expiry" :value="__('Certificate Expiry')" />
    <x-text-input id="{{ $formId }}-certificate_expiry" name="certificate_expiry" type="date" class="mt-1 block w-full" x-model="form.certificate_expiry" :value="old('certificate_expiry', $language?->certificate_expiry?->format('Y-m-d'))" />
    <x-input-error class="mt-2" :messages="$errors->get('certificate_expiry')" />
</div>
