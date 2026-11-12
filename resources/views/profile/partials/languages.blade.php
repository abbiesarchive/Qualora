<section
    x-data="{
        editing: false,
        updateUrl: '',
        form: {
            language_name: @js(old('language_name', '')),
            proficiency_level: @js(old('proficiency_level', '')),
            certificate_type: @js(old('certificate_type', '')),
            certificate_score: @js(old('certificate_score', '')),
            certificate_expiry: @js(old('certificate_expiry', '')),
        },
        startEdit(data) {
            this.form = {
                language_name: data.languageName || '',
                proficiency_level: data.proficiencyLevel || '',
                certificate_type: data.certificateType || '',
                certificate_score: data.certificateScore || '',
                certificate_expiry: data.certificateExpiry || '',
            };
            this.updateUrl = data.updateUrl;
            this.editing = true;
            this.$nextTick(() => {
                this.$refs.form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.$refs.form.querySelector('[name=language_name]').focus();
            });
        },
        cancelEdit() {
            this.editing = false;
            this.updateUrl = '';
            this.form = {
                language_name: '',
                proficiency_level: '',
                certificate_type: '',
                certificate_score: '',
                certificate_expiry: '',
            };
        },
    }"
>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Language') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('Manage your languages and certificates.') }}</p>
    </header>

    @if ($errors->hasAny(['language_name', 'proficiency_level', 'certificate_type', 'certificate_score', 'certificate_expiry']))
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach (['language_name', 'proficiency_level', 'certificate_type', 'certificate_score', 'certificate_expiry'] as $field)
                    @foreach ($errors->get($field) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status') === 'language-created')
        <p class="mt-4 text-sm text-green-700">{{ __('Language added successfully.') }}</p>
    @elseif (session('status') === 'language-updated')
        <p class="mt-4 text-sm text-green-700">{{ __('Language updated successfully.') }}</p>
    @elseif (session('status') === 'language-deleted')
        <p class="mt-4 text-sm text-green-700">{{ __('Language deleted successfully.') }}</p>
    @endif

    <form x-ref="form" method="post" action="{{ route('profile.languages.store') }}" x-bind:action="editing ? updateUrl : $el.dataset.createAction" data-create-action="{{ route('profile.languages.store') }}" class="mt-5 grid w-full grid-cols-1 gap-5 rounded-xl border border-cream-dark bg-cream/20 p-4 sm:grid-cols-2">
        @csrf
        <input type="hidden" value="PATCH" x-bind:name="editing ? '_method' : null">
        @include('profile.partials.language-fields', ['language' => null, 'formId' => 'language-create'])
        <div class="flex items-center gap-3 sm:col-span-2">
            <x-primary-button>
                <span x-show="!editing">{{ __('Add Language') }}</span>
                <span x-show="editing">{{ __('Save Changes') }}</span>
            </x-primary-button>
            <x-secondary-button type="button" x-show="editing" x-on:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>
        </div>
    </form>

    @if ($languages->isEmpty())
        <p class="mt-6 rounded-xl border border-cream-dark bg-cream/40 p-4 text-sm text-gray-600">
            {{ __('No languages added yet.') }}
        </p>
    @else
        <div class="mt-6 overflow-x-auto rounded-xl border border-cream-dark">
            <table class="min-w-full divide-y divide-cream-dark text-left text-sm">
                <thead class="bg-brand-700 text-white">
                    <tr>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('Language') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('Proficiency Level') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('Certificate Type') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('Certificate Score') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ __('Certificate Expiry') }}</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-center font-semibold">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-dark bg-white">
            @foreach ($languages as $language)
                    <tr class="align-middle">
                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900">{{ $language->language_name }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $language->proficiency_level ?: __('Not provided') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $language->certificate_type ?: __('Not provided') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $language->certificate_score ?: __('Not provided') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $language->certificate_expiry?->format('Y-m-d') ?: __('Not provided') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-lg border border-cream-dark px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-cream focus:outline-none focus:ring-2 focus:ring-brand-500"
                                    data-update-url="{{ route('profile.languages.update', $language->getKey()) }}"
                                    data-language-name="{{ $language->language_name }}"
                                    data-proficiency-level="{{ $language->proficiency_level }}"
                                    data-certificate-type="{{ $language->certificate_type }}"
                                    data-certificate-score="{{ $language->certificate_score }}"
                                    data-certificate-expiry="{{ $language->certificate_expiry?->format('Y-m-d') }}"
                                    x-on:click="startEdit($event.currentTarget.dataset)"
                                >
                                    {{ __('Edit') }}
                                </button>
                                <form method="post" action="{{ route('profile.languages.destroy', $language->getKey()) }}" onsubmit="return confirm('Delete this language? This action cannot be undone.');">
                                    @csrf
                                    @method('delete')
                                    <x-danger-button>{{ __('Delete') }}</x-danger-button>
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
