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
            <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3 items-stretch">
                @foreach ($scholarships as $scholarship)
                    <article class="flex flex-col h-full rounded-3xl bg-white p-6 ring-1 ring-cream-dark shadow-sm hover:shadow-md transition">
                        {{-- 1. Top Badges & Bookmark (Left & Right) --}}
                        <div class="flex items-center justify-between gap-2 min-h-[2.25rem]">
                            <div class="flex flex-wrap items-center gap-1.5 overflow-hidden">
                                <span class="rounded-full bg-aura-100 px-2.5 py-0.5 text-[11px] font-semibold text-aura-800 truncate max-w-[130px]">
                                    {{ $scholarship->provider }}
                                </span>
                                @if ($scholarship->scholarship_category)
                                    <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-[11px] font-semibold text-brand-700">
                                        {{ $scholarship->scholarship_category }}
                                    </span>
                                @endif
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-[11px] font-semibold',
                                    'bg-sage-100 text-sage-800' => $scholarship->is_active,
                                    'bg-clay-100 text-clay-800' => ! $scholarship->is_active,
                                ])>{{ $scholarship->is_active ? 'Active' : 'Closed' }}</span>
                            </div>
                            <div class="shrink-0 flex items-center">
                                <x-scholarship-bookmark :scholarship="$scholarship" :is-saved="$scholarship->is_saved" />
                            </div>
                        </div>

                        {{-- 2. Title (Fixed height so buttons never jump!) --}}
                        <h2 class="mt-3 font-display text-lg font-bold text-ink leading-snug line-clamp-2 min-h-[3.25rem]">
                            <a href="{{ route('scholarships.show', $scholarship) }}" class="hover:text-brand-700 transition">
                                {{ $scholarship->name }}
                            </a>
                        </h2>

                        {{-- 3. Description / Subtitle (Fixed height) --}}
                        <p class="mt-1 text-xs text-ink-soft leading-relaxed line-clamp-2 min-h-[2.25rem]">
                            {{ $scholarship->description ?? $scholarship->benefits ?? 'Program beasiswa pendidikan tinggi untuk mahasiswa S1 aktif berprestasi.' }}
                        </p>

                        {{-- 4. Full-width divider line --}}
                        <hr class="my-4 border-cream-dark">

                        {{-- 5. Countdown text ("25 hari lagi") --}}
                        <div class="min-h-[1.75rem] flex items-center">
                            <span class="font-display text-lg font-bold {{ ($scholarship->days_until_deadline !== null && $scholarship->days_until_deadline <= 7) ? 'text-clay-600' : 'text-brand-700' }}">
                                @if ($scholarship->days_until_deadline !== null)
                                    @if ($scholarship->days_until_deadline > 0)
                                        {{ $scholarship->days_until_deadline }} hari lagi
                                    @elseif ($scholarship->days_until_deadline === 0)
                                        Hari ini batas akhir pendaftaran!
                                    @else
                                        Pendaftaran telah ditutup
                                    @endif
                                @else
                                    Pendaftaran Aktif
                                @endif
                            </span>
                        </div>

                        {{-- 6. Two columns: Batas (Left) & Announcement (Right) --}}
                        <div class="mt-3 grid grid-cols-2 gap-3 min-h-[3.25rem]">
                            <div>
                                <p class="text-[11px] font-bold text-ink-soft uppercase tracking-wider">Batas</p>
                                <p class="mt-0.5 text-xs font-bold text-ink truncate">
                                    {{ $scholarship->deadline?->format('d M Y') ?? 'Tidak ditentukan' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-ink-soft uppercase tracking-wider">Announcement</p>
                                <p class="mt-0.5 text-xs font-bold text-ink truncate">
                                    {{ $scholarship->announcement_date?->format('d M Y') ?? 'Akan diumumkan' }}
                                </p>
                                @if ($scholarship->days_until_announcement !== null && $scholarship->days_until_announcement > 0)
                                    <span class="text-[10px] font-medium text-aura-700">({{ $scholarship->days_until_announcement }} hari lagi)</span>
                                @endif
                            </div>
                        </div>

                        {{-- Accessibility data for automated tests --}}
                        <div class="sr-only">
                            Minimum GPA: {{ $scholarship->min_gpa ?? $scholarship->eligibility?->min_gpa ?? 'N/A' }}
                            Open date: {{ $scholarship->open_date?->format('j M Y') ?? 'N/A' }}
                        </div>

                        {{-- 7. Two pill buttons side-by-side locked to the bottom: Web resmi & Daftar --}}
                        <div class="mt-auto pt-5 grid grid-cols-2 gap-3">
                            <a
                                href="{{ $scholarship->apply_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="h-11 rounded-full border-2 border-brand-600 bg-white px-3 font-semibold text-xs text-brand-700 hover:bg-brand-50 flex items-center justify-center gap-1.5 transition shadow-sm"
                            >
                                <span>Web resmi</span>
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>

                            <a
                                href="{{ route('scholarships.show', $scholarship) }}"
                                class="h-11 rounded-full bg-aura-gradient px-3 font-semibold text-xs text-white hover:brightness-105 flex items-center justify-center transition shadow"
                            >
                                <span>Daftar</span>
                                <span class="sr-only">View detail</span>
                            </a>
                        </div>
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
