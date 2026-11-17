@php
    $showingApplications = request()->routeIs('applications.index');
    $showingSaved = ! $showingApplications && (($filters['view'] ?? 'all') === 'saved');
    $showingAll = ! $showingApplications && ! $showingSaved;
@endphp

<nav class="mb-5 inline-flex rounded-full bg-white p-1 ring-1 ring-cream-dark" aria-label="Scholarship sections">
    <a
        href="{{ route('scholarships.index') }}"
        @class([
            'rounded-full px-4 py-2 text-sm font-semibold transition',
            'bg-aura-gradient text-white shadow-sm' => $showingAll,
            'text-ink-soft hover:text-brand-700' => ! $showingAll,
        ])
        @if ($showingAll) aria-current="page" @endif
    >All scholarships</a>
    <a
        href="{{ route('scholarships.index', ['view' => 'saved']) }}"
        @class([
            'rounded-full px-4 py-2 text-sm font-semibold transition',
            'bg-aura-gradient text-white shadow-sm' => $showingSaved,
            'text-ink-soft hover:text-brand-700' => ! $showingSaved,
        ])
        @if ($showingSaved) aria-current="page" @endif
    >Saved</a>
    <a
        href="{{ route('applications.index') }}"
        @class([
            'rounded-full px-4 py-2 text-sm font-semibold transition',
            'bg-aura-gradient text-white shadow-sm' => $showingApplications,
            'text-ink-soft hover:text-brand-700' => ! $showingApplications,
        ])
        @if ($showingApplications) aria-current="page" @endif
    >My applications</a>
</nav>
