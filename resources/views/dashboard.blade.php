@php
    $daysLeft = fn ($date) => $date ? (int) today()->diffInDays($date->copy()->startOfDay(), false) : null;
    $countdown = function ($date) use ($daysLeft) {
        $d = $daysLeft($date);
        return match (true) {
            $d === null => 'No deadline listed',
            $d < 0 => 'Closed',
            $d === 0 => 'Closes today',
            $d === 1 => '1 day left',
            default => "$d days left",
        };
    };
    $firstName = \Illuminate\Support\Str::of($user->name)->before(' ');
@endphp

<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-aura-glow pointer-events-none" aria-hidden="true"></div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
            <p class="text-sm font-semibold text-aura-700">Your dashboard</p>
            <h1 class="mt-1 font-display text-3xl sm:text-4xl font-semibold tracking-tight">
                Welcome, {{ $firstName }}.
            </h1>
            <p class="mt-2 text-ink-soft max-w-xl">
                {{ $completePercent === 100
                    ? 'Your profile looks complete. You may be eligible for more than you think.'
                    : 'Tell us a little about yourself and we will look at the whole picture. Every field is optional.' }}
            </p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 grid gap-6 lg:grid-cols-3">
        {{-- Profile completeness --}}
        <section class="lg:col-span-1 rounded-2xl bg-white ring-1 ring-cream-dark p-6" aria-labelledby="profile-heading">
            <h2 id="profile-heading" class="font-display text-xl font-semibold">Your profile</h2>

            <div class="mt-4 flex items-center justify-between text-sm">
                <span class="text-ink-soft">{{ $completePercent }}% filled in</span>
            </div>
            <div class="mt-2 h-2 rounded-full bg-cream-dark overflow-hidden" role="progressbar" aria-valuenow="{{ $completePercent }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-aura-gradient" style="width: {{ $completePercent }}%"></div>
            </div>

            @if ($missing->isNotEmpty())
                <p class="mt-4 text-sm text-ink-soft">Not added yet:</p>
                <ul class="mt-2 flex flex-wrap gap-2">
                    @foreach ($missing as $label)
                        <li class="rounded-full bg-aura-100 text-aura-700 text-xs font-medium px-3 py-1">{{ $label }}</li>
                    @endforeach
                </ul>
                <p class="mt-4 text-xs text-ink-soft">Rules we can't check yet stay "unknown", never failed.</p>
            @else
                <p class="mt-4 text-sm text-sage-800">All set. Nothing is missing.</p>
            @endif

            <a href="{{ route('profile.edit') }}" class="mt-6 inline-flex items-center justify-center px-5 py-2.5 bg-aura-gradient rounded-full font-semibold text-sm text-white shadow-sm shadow-brand-500/30 hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-white">
                {{ $hasProfile ? 'Update my details' : 'Add my details' }}
            </a>
        </section>

        <div class="lg:col-span-2 grid gap-6">
            {{-- Saved --}}
            <section class="rounded-2xl bg-white ring-1 ring-cream-dark p-6" aria-labelledby="saved-heading">
                <div class="flex items-center justify-between gap-4">
                    <h2 id="saved-heading" class="font-display text-xl font-semibold">Saved scholarships</h2>
                    <a href="{{ route('scholarships.index', ['view' => 'saved']) }}" class="shrink-0 text-sm font-semibold text-brand-700 hover:text-brand-900">View all</a>
                </div>

                @forelse ($saved as $s)
                    @if ($loop->first)<ul class="mt-4 divide-y divide-cream-dark">@endif
                    <li class="py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-medium truncate">{{ $s->name }}</p>
                            <p class="text-sm text-ink-soft truncate">{{ $s->provider }}</p>
                        </div>
                        <span @class([
                            'shrink-0 rounded-full text-xs font-semibold px-3 py-1',
                            'bg-clay-100 text-clay-800' => ($daysLeft($s->deadline) ?? 99) <= 7,
                            'bg-sage-100 text-sage-800' => ($daysLeft($s->deadline) ?? 99) > 7,
                        ])>{{ $countdown($s->deadline) }}</span>
                    </li>
                    @if ($loop->last)</ul>@endif
                @empty
                    <p class="mt-3 text-sm text-ink-soft">Nothing saved yet. Scholarships you save will appear here with a deadline countdown.</p>
                @endforelse
            </section>

            {{-- Closing soon --}}
            <section class="rounded-2xl bg-white ring-1 ring-cream-dark p-6" aria-labelledby="soon-heading">
                <h2 id="soon-heading" class="font-display text-xl font-semibold">Closing soon</h2>
                <p class="mt-1 text-xs text-ink-soft">Open scholarships by deadline. Always verify on the official site.</p>

                @forelse ($closingSoon as $s)
                    @if ($loop->first)<ul class="mt-4 divide-y divide-cream-dark">@endif
                    <li class="py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-medium truncate">{{ $s->name }}</p>
                            <p class="text-sm text-ink-soft truncate">
                                {{ $s->provider }} · Closes {{ $s->deadline->format('j M Y') }}
                                @if ($s->last_verified) · Verified {{ $s->last_verified->format('j M Y') }} @endif
                            </p>
                        </div>
                        <a href="{{ $s->official_url }}" target="_blank" rel="noopener noreferrer" class="shrink-0 text-sm font-semibold text-brand-700 hover:text-brand-900">
                            Official site<span class="sr-only"> for {{ $s->name }}</span> &rarr;
                        </a>
                    </li>
                    @if ($loop->last)</ul>@endif
                @empty
                    <p class="mt-3 text-sm text-ink-soft">No open scholarships are listed right now. Check back soon.</p>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
