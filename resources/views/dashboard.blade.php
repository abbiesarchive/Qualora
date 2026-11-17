@php
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

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
        {{-- Profile completeness --}}
        <section class="rounded-2xl bg-white ring-1 ring-cream-dark p-5 shadow-sm"
            aria-labelledby="profile-heading">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="profile-heading" class="font-display text-xl font-semibold text-ink">Your profile</h2>
                <span class="text-sm font-semibold text-brand-700">{{ $completePercent }}% filled in</span>
            </div>

            <div class="mt-3 h-2 rounded-full bg-cream-dark overflow-hidden" role="progressbar"
                aria-valuenow="{{ $completePercent }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-aura-gradient" style="width: {{ $completePercent }}%"></div>
            </div>

            @if ($missing->isNotEmpty())
                <div class="mt-3">
                    <ul class="flex flex-wrap items-center gap-2">
                        <li class="text-sm text-ink-soft">Not added yet:</li>
                        @foreach ($missing as $label)
                            <li class="rounded-full bg-aura-100 text-aura-700 text-xs font-medium px-3 py-1">
                                {{ $label }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-xs text-ink-soft">Rules we can't check yet stay "unknown", never failed.</p>
                </div>
            @else
                <p class="mt-3 text-sm text-sage-800">All set. Nothing is missing.</p>
            @endif

            <a href="{{ route('profile.edit') }}"
                class="mt-4 inline-flex items-center justify-center px-5 py-2.5 bg-aura-gradient rounded-full font-semibold text-sm text-white shadow-sm shadow-brand-500/30 hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-white">
                {{ $hasProfile ? 'Update my skills' : 'Add my skills' }}
            </a>
        </section>

        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <section class="flex min-h-[8rem] flex-col justify-between rounded-2xl border-t-4 border-brand-500 bg-white p-5 ring-1 ring-cream-dark shadow-sm"
                aria-labelledby="applications-total-heading">
                <h2 id="applications-total-heading" class="text-sm font-semibold text-ink-soft">Total Lamaran</h2>
                <p class="font-display text-4xl font-semibold text-brand-700">{{ $applicationCounts['total'] }}</p>
            </section>
            <section class="flex min-h-[8rem] flex-col justify-between rounded-2xl border-t-4 border-sage-500 bg-white p-5 ring-1 ring-cream-dark shadow-sm"
                aria-labelledby="applications-accepted-heading">
                <h2 id="applications-accepted-heading" class="text-sm font-semibold text-ink-soft">Diterima</h2>
                <p class="font-display text-4xl font-semibold text-sage-800">{{ $applicationCounts['accepted'] }}</p>
            </section>
            <section class="flex min-h-[8rem] flex-col justify-between rounded-2xl border-t-4 border-clay-500 bg-white p-5 ring-1 ring-cream-dark shadow-sm"
                aria-labelledby="applications-rejected-heading">
                <h2 id="applications-rejected-heading" class="text-sm font-semibold text-ink-soft">Ditolak</h2>
                <p class="font-display text-4xl font-semibold text-clay-700">{{ $applicationCounts['rejected'] }}</p>
            </section>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        @include('applications.tracker')

        <div class="mt-6">
            {{-- Closing soon --}}
            <section class="rounded-2xl bg-white ring-1 ring-cream-dark p-6" aria-labelledby="soon-heading">
                <h2 id="soon-heading" class="font-display text-xl font-semibold">Closing soon</h2>
                <p class="mt-1 text-xs text-ink-soft">Open scholarships by deadline. Always verify on the official site.
                </p>

                @forelse ($closingSoon as $s)
                    @if ($loop->first)
                        <ul class="mt-4 divide-y divide-cream-dark">
                    @endif
                    <li class="py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-medium truncate">{{ $s->name }}</p>
                            <p class="text-sm text-ink-soft truncate">
                                {{ $s->provider }} · Closes {{ $s->deadline->format('j M Y') }}
                                @if ($s->last_verified)
                                    · Verified {{ $s->last_verified->format('j M Y') }}
                                @endif
                            </p>
                        </div>
                        <a href="{{ $s->official_url }}" target="_blank" rel="noopener noreferrer"
                            class="shrink-0 text-sm font-semibold text-brand-700 hover:text-brand-900">
                            Official site<span class="sr-only"> for {{ $s->name }}</span> &rarr;
                        </a>
                    </li>
                    @if ($loop->last)
                        </ul>
                    @endif
                @empty
                    <p class="mt-3 text-sm text-ink-soft">No open scholarships are listed right now. Check back soon.
                    </p>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
