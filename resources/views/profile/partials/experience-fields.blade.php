<div>
    <x-input-label for="{{ $formId }}-experience_type" :value="__('Experience Type')" />
    <select id="{{ $formId }}-experience_type" name="experience_type" class="mt-1 block w-full rounded-xl border-cream-dark bg-white shadow-sm focus:border-brand-500 focus:ring-brand-500" x-model="form.experience_type">
        <option value="">{{ __('Select a type') }}</option>
        @foreach (['Organization', 'Internship', 'Work Experience', 'Volunteer', 'Competition', 'Leadership', 'Project', 'Other'] as $type)
            <option value="{{ $type }}" @selected(old('experience_type', $experience?->experience_type) === $type)>{{ $type }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('experience_type')" />
</div>

<div>
    <x-input-label for="{{ $formId }}-experience_name" :value="__('Experience Name')" />
    <x-text-input id="{{ $formId }}-experience_name" name="experience_name" type="text" class="mt-1 block w-full" x-model="form.experience_name" :value="old('experience_name', $experience?->experience_name)" required />
    <x-input-error class="mt-2" :messages="$errors->get('experience_name')" />
</div>

<div>
    <x-input-label for="{{ $formId }}-position" :value="__('Position')" />
    <x-text-input id="{{ $formId }}-position" name="position" type="text" class="mt-1 block w-full" x-model="form.position" :value="old('position', $experience?->position)" />
    <x-input-error class="mt-2" :messages="$errors->get('position')" />
</div>

<div>
    <x-input-label for="{{ $formId }}-organization_name" :value="__('Organization Name')" />
    <x-text-input id="{{ $formId }}-organization_name" name="organization_name" type="text" class="mt-1 block w-full" x-model="form.organization_name" :value="old('organization_name', $experience?->organization_name)" />
    <x-input-error class="mt-2" :messages="$errors->get('organization_name')" />
</div>

<div class="sm:col-span-2">
    <x-input-label for="{{ $formId }}-description" :value="__('Description')" />
    <textarea id="{{ $formId }}-description" name="description" rows="4" class="mt-1 block w-full rounded-xl border-cream-dark bg-white shadow-sm focus:border-brand-500 focus:ring-brand-500" x-model="form.description">{{ old('description', $experience?->description) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
</div>

<div class="grid gap-6 sm:col-span-2 sm:grid-cols-2">
    <div>
        <x-input-label for="{{ $formId }}-start_date" :value="__('Start Date')" />
        <x-text-input id="{{ $formId }}-start_date" name="start_date" type="date" class="mt-1 block w-full" x-model="form.start_date" :value="old('start_date', $experience?->start_date?->format('Y-m-d'))" />
        <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
    </div>

    <div>
        <x-input-label for="{{ $formId }}-end_date" :value="__('End Date')" />
        <x-text-input id="{{ $formId }}-end_date" name="end_date" type="date" class="mt-1 block w-full" x-model="form.end_date" :value="old('end_date', $experience?->end_date?->format('Y-m-d'))" />
        <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
    </div>
</div>
