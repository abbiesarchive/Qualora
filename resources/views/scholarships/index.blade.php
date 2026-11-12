<x-app-layout>
    <x-slot name="title">Scholarship</x-slot>

    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-aura-glow pointer-events-none" aria-hidden="true"></div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
            <p class="text-sm font-semibold text-aura-700">Find your next opportunity</p>
            <h1 class="mt-1 font-display text-3xl sm:text-4xl font-semibold tracking-tight">Scholarships</h1>
            <p class="mt-2 text-ink-soft max-w-xl">Browse scholarships and review their eligibility details.</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="mb-5 inline-flex rounded-full bg-white p-1 ring-1 ring-cream-dark" aria-label="Scholarship list">
            <a
                href="{{ route('scholarships.index') }}"
                @class([
                    'rounded-full px-4 py-2 text-sm font-semibold transition',
                    'bg-aura-gradient text-white shadow-sm' => ($filters['view'] ?? 'all') === 'all',
                    'text-ink-soft hover:text-brand-700' => ($filters['view'] ?? 'all') !== 'all',
                ])
                @if (($filters['view'] ?? 'all') === 'all') aria-current="page" @endif
            >All scholarships</a>
            <a
                href="{{ route('scholarships.index', ['view' => 'saved']) }}"
                @class([
                    'rounded-full px-4 py-2 text-sm font-semibold transition',
                    'bg-aura-gradient text-white shadow-sm' => ($filters['view'] ?? 'all') === 'saved',
                    'text-ink-soft hover:text-brand-700' => ($filters['view'] ?? 'all') !== 'saved',
                ])
                @if (($filters['view'] ?? 'all') === 'saved') aria-current="page" @endif
            >Saved</a>
        </div>

        <form method="GET" action="{{ route('scholarships.index') }}" class="rounded-2xl bg-white ring-1 ring-cream-dark p-4 sm:p-5">
            <input type="hidden" name="view" value="{{ $filters['view'] ?? 'all' }}">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label class="sm:col-span-2">
                    <span class="block text-sm font-medium text-ink mb-1">Search scholarships</span>
                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search by name or provider"
                        class="w-full rounded-xl border-cream-dark bg-white text-ink focus:border-brand-500 focus:ring-brand-500"
                    >
                </label>

                <label>
                    <span class="block text-sm font-medium text-ink mb-1">Scholarship type</span>
                    <select name="type" class="w-full rounded-xl border-cream-dark bg-white text-ink focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All types</option>
                        <option value="full" @selected(($filters['type'] ?? '') === 'full')>Full</option>
                        <option value="partial" @selected(($filters['type'] ?? '') === 'partial')>Partial</option>
                    </select>
                </label>

                <label>
                    <span class="block text-sm font-medium text-ink mb-1">Status</span>
                    <select name="status" class="w-full rounded-xl border-cream-dark bg-white text-ink focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All statuses</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                    </select>
                </label>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-aura-gradient px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    Search
                </button>
                @if (! empty($filters['search']) || ! empty($filters['status']) || ! empty($filters['type']))
                    <a href="{{ route('scholarships.index', ($filters['view'] ?? 'all') === 'saved' ? ['view' => 'saved'] : []) }}" class="text-sm font-semibold text-brand-700 hover:text-brand-900">Clear filters</a>
                @endif
                <p class="text-sm text-ink-soft sm:ms-auto">
                    {{ $scholarships->total() }} {{ \Illuminate\Support\Str::plural('scholarship', $scholarships->total()) }} found
                </p>
            </div>
        </form>

        @if ($scholarships->isNotEmpty())
            <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($scholarships as $scholarship)
                    <article class="flex flex-col rounded-2xl bg-white p-6 ring-1 ring-cream-dark">
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="font-display text-xl font-semibold leading-snug">
                                <a href="{{ route('scholarships.show', $scholarship) }}" class="hover:text-brand-700 transition">
                                    {{ $scholarship->name }}
                                </a>
                            </h2>
                            <div class="flex shrink-0 items-center gap-2">
                                <x-scholarship-bookmark :scholarship="$scholarship" :is-saved="$scholarship->is_saved" />
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-sage-100 text-sage-800' => $scholarship->is_active,
                                    'bg-clay-100 text-clay-800' => ! $scholarship->is_active,
                                ])>{{ $scholarship->is_active ? 'Active' : 'Closed' }}</span>
                            </div>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                            <span class="font-medium text-ink-soft">{{ $scholarship->provider }}</span>
                            @if ($scholarship->scholarship_category)
                                <span class="rounded bg-cream-dark px-2 py-0.5 font-semibold text-ink-soft">{{ $scholarship->scholarship_category }}</span>
                            @endif
                            @if ($scholarship->is_applied)
                                <span class="rounded bg-brand-50 px-2 py-0.5 font-bold text-brand-700 border border-brand-200">
                                    &check; Sedang Kamu Pantau
                                </span>
                            @endif
                        </div>

                        <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-ink-soft">Minimum GPA</dt>
                                <dd class="mt-1 font-semibold">{{ $scholarship->min_gpa ?? $scholarship->eligibility?->min_gpa ?? 'Tidak ditentukan' }}</dd>
                            </div>
                            <div>
                                <dt class="text-ink-soft">Open date</dt>
                                <dd class="mt-1 font-semibold">{{ $scholarship->open_date?->format('j M Y') ?? 'Tidak ditentukan' }}</dd>
                            </div>
                            <div>
                                <dt class="text-ink-soft">Batas Pendaftaran</dt>
                                <dd class="mt-1 font-semibold">
                                    {{ $scholarship->deadline?->format('j M Y') ?? 'Tidak ditentukan' }}
                                    @if ($scholarship->days_until_deadline !== null && $scholarship->days_until_deadline >= 0)
                                        <span class="block text-xs font-normal text-clay-700">({{ $scholarship->days_until_deadline }} hari lagi)</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-ink-soft">Tipe Pendanaan</dt>
                                <dd class="mt-1 font-semibold">{{ $scholarship->scholarship_type ? ucfirst($scholarship->scholarship_type) : 'Umum' }}</dd>
                            </div>
                        </dl>

                        <a href="{{ route('scholarships.show', $scholarship) }}" class="mt-6 inline-flex items-center justify-center rounded-full border border-brand-600 px-4 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                            View detail<span class="sr-only"> for {{ $scholarship->name }}</span>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $scholarships->links() }}
            </div>
        @else
            <section class="mt-6 rounded-2xl bg-white p-10 text-center ring-1 ring-cream-dark">
                <h2 class="font-display text-xl font-semibold">
                    {{ ($filters['view'] ?? 'all') === 'saved' ? 'No saved scholarships yet' : 'No scholarships found' }}
                </h2>
                <p class="mt-2 text-sm text-ink-soft">
                    {{ ($filters['view'] ?? 'all') === 'saved' ? 'Save scholarships with the bookmark icon and they will appear here.' : 'Try changing your search or filters.' }}
                </p>
            </section>
        @endif
    </div>
</x-app-layout>
