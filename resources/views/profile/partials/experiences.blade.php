<section
    x-data="{
        editing: false,
        updateUrl: '',
        form: {
            experience_type: @js(old('experience_type', '')),
            experience_name: @js(old('experience_name', '')),
            position: @js(old('position', '')),
            organization_name: @js(old('organization_name', '')),
            description: @js(old('description', '')),
            start_date: @js(old('start_date', '')),
            end_date: @js(old('end_date', '')),
        },
        startEdit(data) {
            this.form = {
                experience_type: data.experienceType || '',
                experience_name: data.experienceName || '',
                position: data.position || '',
                organization_name: data.organizationName || '',
                description: data.description || '',
                start_date: data.startDate || '',
                end_date: data.endDate || '',
            };
            this.updateUrl = data.updateUrl;
            this.editing = true;
            this.$nextTick(() => {
                this.$refs.form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.$refs.form.querySelector('[name=experience_name]').focus();
            });
        },
        cancelEdit() {
            this.editing = false;
            this.updateUrl = '';
            this.form = {
                experience_type: '',
                experience_name: '',
                position: '',
                organization_name: '',
                description: '',
                start_date: '',
                end_date: '',
            };
        },
    }"
>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Experience') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('Manage your work, education, and other experience.') }}</p>
    </header>

    @if ($errors->hasAny(['experience_type', 'experience_name', 'position', 'organization_name', 'description', 'start_date', 'end_date']))
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach (['experience_type', 'experience_name', 'position', 'organization_name', 'description', 'start_date', 'end_date'] as $field)
                    @foreach ($errors->get($field) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status') === 'experience-created')
        <p class="mt-4 text-sm text-green-700">{{ __('Experience added successfully.') }}</p>
    @elseif (session('status') === 'experience-updated')
        <p class="mt-4 text-sm text-green-700">{{ __('Experience updated successfully.') }}</p>
    @elseif (session('status') === 'experience-deleted')
        <p class="mt-4 text-sm text-green-700">{{ __('Experience deleted successfully.') }}</p>
    @endif

    <form x-ref="form" method="post" action="{{ route('profile.experiences.store') }}" x-bind:action="editing ? updateUrl : $el.dataset.createAction" data-create-action="{{ route('profile.experiences.store') }}" class="mt-5 grid w-full grid-cols-1 gap-5 rounded-xl border border-cream-dark bg-cream/20 p-4 sm:grid-cols-2">
        @csrf
        <input type="hidden" value="PATCH" x-bind:name="editing ? '_method' : null">
        @include('profile.partials.experience-fields', ['experience' => null, 'formId' => 'experience-create'])
        <div class="flex items-center gap-3 sm:col-span-2">
            <x-primary-button>
                <span x-show="!editing">{{ __('Add Experience') }}</span>
                <span x-show="editing">{{ __('Save Changes') }}</span>
            </x-primary-button>
            <x-secondary-button type="button" x-show="editing" x-on:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>
        </div>
    </form>

    @if ($experiences->isEmpty())
        <p class="mt-6 rounded-xl border border-cream-dark bg-cream/40 p-4 text-sm text-gray-600">
            {{ __('No experiences added yet.') }}
        </p>
    @else
        <div class="mt-6 overflow-x-auto rounded-xl border border-cream-dark">
            <table class="min-w-full divide-y divide-cream-dark text-left text-sm">
                <thead class="bg-brand-700 text-white">
                    <tr>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('Experience Name') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('Experience Type') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('Position') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('Organization Name') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('Start Date') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('End Date') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('Description') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-center font-semibold">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-dark bg-white">
            @foreach ($experiences as $experience)
                    <tr class="align-middle">
                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900">{{ $experience->experience_name }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $experience->experience_type ?: __('Not provided') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $experience->position ?: __('Not provided') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $experience->organization_name ?: __('Not provided') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $experience->start_date?->format('Y-m-d') ?: __('Not provided') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $experience->end_date?->format('Y-m-d') ?: __('Present') }}</td>
                        <td class="max-w-xs px-4 py-3 text-gray-700"><span class="line-clamp-2" title="{{ $experience->description }}">{{ $experience->description ?: __('Not provided') }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-lg border border-cream-dark px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-cream focus:outline-none focus:ring-2 focus:ring-brand-500"
                                    data-update-url="{{ route('profile.experiences.update', $experience->getKey()) }}"
                                    data-experience-type="{{ $experience->experience_type }}"
                                    data-experience-name="{{ $experience->experience_name }}"
                                    data-position="{{ $experience->position }}"
                                    data-organization-name="{{ $experience->organization_name }}"
                                    data-description="{{ $experience->description }}"
                                    data-start-date="{{ $experience->start_date?->format('Y-m-d') }}"
                                    data-end-date="{{ $experience->end_date?->format('Y-m-d') }}"
                                    x-on:click="startEdit($event.currentTarget.dataset)"
                                >
                                    {{ __('Edit') }}
                                </button>
                                <form method="post" action="{{ route('profile.experiences.destroy', $experience->getKey()) }}" onsubmit="return confirm('Delete this experience? This action cannot be undone.');">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="inline-flex items-center rounded-lg border border-red-600 bg-red-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
            @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
