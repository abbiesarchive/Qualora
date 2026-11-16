@php
    $firstName = \Illuminate\Support\Str::of($user->name)->before(' ');
    $statCards = [
        ['label' => 'Tracked', 'value' => $stats['tracked'], 'hint' => 'applications'],
        ['label' => 'Submitted', 'value' => $stats['submitted'], 'hint' => 'sent to the provider'],
        ['label' => 'Accepted', 'value' => $stats['accepted'], 'hint' => 'congratulations'],
        ['label' => 'Saved', 'value' => $stats['saved'], 'hint' => 'scholarships'],
    ];
@endphp

<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-aura-glow pointer-events-none" aria-hidden="true"></div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aura-700">Your dashboard</p>
                <h1 class="mt-1 font-display text-3xl sm:text-4xl font-semibold tracking-tight">
                    Welcome, {{ $firstName }}.
                </h1>
                <p class="mt-2 text-ink-soft max-w-xl">
                    Keep track of where you have applied and when results are due.
                </p>
            </div>
            <a href="{{ route('scholarships.index') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-aura-gradient rounded-full font-semibold text-sm text-white shadow-sm shadow-brand-500/30 hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-white">
                Find scholarships
            </a>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <dl class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ($statCards as $card)
                <div class="rounded-2xl bg-white ring-1 ring-cream-dark p-5">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-ink-soft">{{ $card['label'] }}</dt>
                    <dd class="mt-1 font-display text-3xl font-semibold text-ink">{{ $card['value'] }}</dd>
                    <p class="text-xs text-ink-soft">{{ $card['hint'] }}</p>
                </div>
            @endforeach
        </dl>

        @include('applications._tracker')
    </div>
</x-app-layout>
