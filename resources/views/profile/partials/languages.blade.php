<section>
    <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-lg font-medium text-gray-900">{{ __('Language') }}</h2>
            <p class="mt-1 text-sm text-gray-600">{{ __('Manage your languages and certificates.') }}</p>
        </div>

        <details class="group" @if ($errors->hasAny(['language_name', 'proficiency_level', 'certificate_type', 'certificate_score', 'certificate_expiry'])) open @endif>
            <summary class="inline-flex cursor-pointer list-none items-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                {{ __('Add Language') }}
            </summary>
            <form method="post" action="{{ route('profile.languages.store') }}" class="mt-4 space-y-6 rounded-xl border border-cream-dark p-4">
                @csrf
                @include('profile.partials.language-fields', ['language' => null, 'formId' => 'language-create'])
                <x-primary-button>{{ __('Save Language') }}</x-primary-button>
            </form>
        </details>
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

    @if ($languages->isEmpty())
        <p class="mt-6 rounded-xl border border-cream-dark bg-cream/40 p-4 text-sm text-gray-600">
            {{ __('No languages added yet.') }}
        </p>
    @else
        <div class="mt-6 space-y-4">
            @foreach ($languages as $language)
                <article class="rounded-xl border border-cream-dark p-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="font-medium text-gray-900">{{ $language->language_name }}</h3>
                            <dl class="mt-3 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-gray-500">{{ __('Proficiency Level') }}</dt>
                                    <dd class="text-gray-900">{{ $language->proficiency_level ?: __('Not provided') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">{{ __('Certificate Type') }}</dt>
                                    <dd class="text-gray-900">{{ $language->certificate_type ?: __('Not provided') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">{{ __('Certificate Score') }}</dt>
                                    <dd class="text-gray-900">{{ $language->certificate_score ?: __('Not provided') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">{{ __('Certificate Expiry') }}</dt>
                                    <dd class="text-gray-900">{{ $language->certificate_expiry?->format('Y-m-d') ?: __('Not provided') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="flex shrink-0 items-center gap-3">
                            <details class="group">
                                <summary class="cursor-pointer rounded-xl border border-cream-dark px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-cream focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    {{ __('Edit') }}
                                </summary>
                                <form method="post" action="{{ route('profile.languages.update', $language->getKey()) }}" class="mt-4 space-y-6 rounded-xl border border-cream-dark p-4 sm:min-w-96">
                                    @csrf
                                    @method('patch')
                                    @include('profile.partials.language-fields', ['language' => $language, 'formId' => 'language-'.$language->getKey()])
                                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                                </form>
                            </details>

                            <form method="post" action="{{ route('profile.languages.destroy', $language->getKey()) }}" onsubmit="return confirm('Delete this language? This action cannot be undone.');">
                                @csrf
                                @method('delete')
                                <x-danger-button>{{ __('Delete') }}</x-danger-button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
