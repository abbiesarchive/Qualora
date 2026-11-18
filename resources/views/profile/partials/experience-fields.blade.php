<div>
    <x-input-label for="{{ $formId }}-experience_type" :value="__('Type')" />
    <select id="{{ $formId }}-experience_type" name="experience_type" class="mt-1 block w-full rounded-xl border-cream-dark bg-white shadow-sm focus:border-brand-500 focus:ring-brand-500" x-model="form.experience_type">
        <option value="">{{ __('Select a type') }}</option>
        @foreach (\App\Http\Controllers\UserExperienceController::EXPERIENCE_TYPES as $type)
            <option value="{{ $type }}" @selected(old('experience_type', $experience?->experience_type) === $type)>{{ __($type) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('experience_type')" />
</div>

<div>
    <x-input-label for="{{ $formId }}-experience_name" :value="__('Title')" />
    <x-text-input id="{{ $formId }}-experience_name" name="experience_name" type="text" class="mt-1 block w-full" x-model="form.experience_name" :value="old('experience_name', $experience?->experience_name)" required />
    <x-input-error class="mt-2" :messages="$errors->get('experience_name')" />
</div>

<div class="sm:col-span-2">
    <x-input-label for="{{ $formId }}-description" :value="__('Short description (optional)')" />
    <textarea id="{{ $formId }}-description" name="description" rows="3" maxlength="1000" class="mt-1 block w-full rounded-xl border-cream-dark bg-white shadow-sm focus:border-brand-500 focus:ring-brand-500" x-model="form.description">{{ old('description', $experience?->description) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
</div>
