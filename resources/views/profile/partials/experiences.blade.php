<section>
    <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-lg font-medium text-gray-900">{{ __('Experience') }}</h2>
            <p class="mt-1 text-sm text-gray-600">{{ __('Manage your work, education, and other experience.') }}</p>
        </div>

        <details class="group" @if ($errors->hasAny(['experience_type', 'experience_name', 'position', 'organization_name', 'description', 'start_date', 'end_date'])) open @endif>
            <summary class="inline-flex cursor-pointer list-none items-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                {{ __('Add Experience') }}
            </summary>
            <form method="post" action="{{ route('profile.experiences.store') }}" class="mt-4 space-y-6 rounded-xl border border-cream-dark p-4">
                @csrf
                @include('profile.partials.experience-fields', ['experience' => null, 'formId' => 'experience-create'])
                <x-primary-button>{{ __('Save Experience') }}</x-primary-button>
            </form>
        </details>
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

    @if ($experiences->isEmpty())
        <p class="mt-6 rounded-xl border border-cream-dark bg-cream/40 p-4 text-sm text-gray-600">
            {{ __('No experiences added yet.') }}
        </p>
    @else
        <div class="mt-6 space-y-4">
            @foreach ($experiences as $experience)
                <article class="rounded-xl border border-cream-dark p-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h3 class="font-medium text-gray-900">{{ $experience->experience_name }}</h3>
                            @if ($experience->position || $experience->organization_name)
                                <p class="mt-1 text-sm text-gray-700">
                                    {{ collect([$experience->position, $experience->organization_name])->filter()->join(' · ') }}
                                </p>
                            @endif
                            <dl class="mt-3 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-gray-500">{{ __('Experience Type') }}</dt>
                                    <dd class="text-gray-900">{{ $experience->experience_type ?: __('Not provided') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">{{ __('Organization Name') }}</dt>
                                    <dd class="text-gray-900">{{ $experience->organization_name ?: __('Not provided') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">{{ __('Start Date') }}</dt>
                                    <dd class="text-gray-900">{{ $experience->start_date?->format('Y-m-d') ?: __('Not provided') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">{{ __('End Date') }}</dt>
                                    <dd class="text-gray-900">{{ $experience->end_date?->format('Y-m-d') ?: __('Present') }}</dd>
                                </div>
                            </dl>
                            @if ($experience->description)
                                <div class="mt-3">
                                    <p class="text-sm text-gray-500">{{ __('Description') }}</p>
                                    <p class="mt-1 whitespace-pre-line text-sm text-gray-900">{{ $experience->description }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex shrink-0 items-center gap-3">
                            <details class="group">
                                <summary class="cursor-pointer rounded-xl border border-cream-dark px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-cream focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    {{ __('Edit') }}
                                </summary>
                                <form method="post" action="{{ route('profile.experiences.update', $experience->getKey()) }}" class="mt-4 space-y-6 rounded-xl border border-cream-dark p-4 sm:min-w-96">
                                    @csrf
                                    @method('patch')
                                    @include('profile.partials.experience-fields', ['experience' => $experience, 'formId' => 'experience-'.$experience->getKey()])
                                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                                </form>
                            </details>

                            <form method="post" action="{{ route('profile.experiences.destroy', $experience->getKey()) }}" onsubmit="return confirm('Delete this experience? This action cannot be undone.');">
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
