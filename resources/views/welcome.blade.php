<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Qualora helps S1 students find scholarships they may be eligible for. Discover where you belong.">

        <title>{{ config('app.name') }} · Discover where you belong.</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|fraunces:500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased bg-cream">
        {{-- nav --}}
        <header class="sticky top-0 z-50 bg-cream">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="/"><x-wordmark /></a>
                <nav class="flex items-center gap-2 sm:gap-4 text-sm font-medium">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-full bg-aura-gradient text-white shadow-sm shadow-brand-500/30 hover:brightness-105">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 text-ink-soft hover:text-ink">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-full bg-aura-gradient text-white shadow-sm shadow-brand-500/30 hover:brightness-105">Get started</a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden">
                <div class="absolute inset-0 bg-aura-glow pointer-events-none" aria-hidden="true"></div>
                <div class="relative max-w-3xl mx-auto px-4 sm:px-6 pt-16 pb-20 sm:pt-24 sm:pb-28 text-center">
                    <p class="inline-block rounded-full bg-aura-100 text-aura-700 text-xs font-semibold tracking-wide px-3 py-1">For enrolled S1 students</p>
                    <h1 class="mt-6 font-display text-4xl sm:text-6xl font-semibold leading-tight tracking-tight">
                        Discover where you <span class="text-transparent bg-clip-text bg-aura-gradient">belong.</span>
                    </h1>
                    <p class="mt-6 text-lg text-ink-soft max-w-xl mx-auto">
                        The right opportunity isn't always the most obvious one. Tell us about yourself and Qualora looks at the whole picture to show scholarships you may be eligible for, and how close you are to the rest.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row gap-3 justify-center">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-7 py-3 rounded-full bg-aura-gradient text-white font-semibold shadow-md shadow-brand-500/30 hover:brightness-105">Go to your dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-7 py-3 rounded-full bg-aura-gradient text-white font-semibold shadow-md shadow-brand-500/30 hover:brightness-105">Find my matches</a>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-7 py-3 rounded-full bg-white ring-1 ring-cream-dark font-semibold hover:bg-cream-dark">I already have an account</a>
                        @endauth
                    </div>
                </div>
            </section>

            {{-- How it works --}}
            <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <h2 class="font-display text-3xl font-semibold text-center">How it works</h2>
                <ol class="mt-10 grid gap-6 sm:grid-cols-3">
                    @foreach ([
                        ['1', 'Tell us about you', 'Fill in a short form, or paste a few lines about yourself. Every field is optional, and you always confirm before anything is saved.'],
                        ['2', 'See your matches', 'Each scholarship is checked rule by rule, so you see exactly why it fits, or what is missing.'],
                        ['3', 'Get ready to apply', 'Open a plain-language explanation, a document checklist and a deadline countdown for the ones you like.'],
                    ] as [$n, $title, $text])
                        <li class="rounded-2xl bg-white p-6 ring-1 ring-cream-dark shadow-sm">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-aura-gradient text-white font-semibold">{{ $n }}</span>
                            <h3 class="mt-4 font-display text-xl font-semibold">{{ $title }}</h3>
                            <p class="mt-2 text-ink-soft">{{ $text }}</p>
                        </li>
                    @endforeach
                </ol>
            </section>

            {{-- results look like --}}
            <section class="bg-cream-dark/60">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <h2 class="font-display text-3xl font-semibold text-center">Honest, gentle results</h2>
                    <p class="mt-3 text-center text-ink-soft max-w-xl mx-auto">No harsh verdicts. You see where you stand and what could change it.</p>

                    <div class="mt-10 grid gap-6 md:grid-cols-3">
                        <article class="rounded-2xl bg-white p-6 ring-1 ring-sage-300 shadow-sm">
                            <span class="inline-block rounded-full bg-sage-100 text-sage-800 text-xs font-semibold px-3 py-1">You may be eligible</span>
                            <h3 class="mt-4 font-display text-lg font-semibold">Example scholarship</h3>
                            <ul class="mt-3 space-y-1.5 text-sm text-ink-soft">
                                <li><span class="text-sage-600">✓</span> GPA 3.40, required 3.25</li>
                                <li><span class="text-sage-600">✓</span> Semester 5, open to 3 to 7</li>
                            </ul>
                        </article>

                        <article class="rounded-2xl bg-white p-6 ring-1 ring-glow-300 shadow-sm">
                            <span class="inline-block rounded-full bg-glow-100 text-glow-700 text-xs font-semibold px-3 py-1">Almost there</span>
                            <h3 class="mt-4 font-display text-lg font-semibold">Example scholarship</h3>
                            <ul class="mt-3 space-y-1.5 text-sm text-ink-soft">
                                <li><span class="text-glow-500">●</span> GPA 3.20, required 3.25. Just 0.05 away.</li>
                                <li><span class="text-sage-600">✓</span> Semester 5, open to 3 to 7</li>
                            </ul>
                        </article>

                        <article class="rounded-2xl bg-white p-6 ring-1 ring-clay-300 shadow-sm">
                            <span class="inline-block rounded-full bg-clay-100 text-clay-800 text-xs font-semibold px-3 py-1">Not a match yet</span>
                            <h3 class="mt-4 font-display text-lg font-semibold">Example scholarship</h3>
                            <ul class="mt-3 space-y-1.5 text-sm text-ink-soft">
                                <li><span class="text-clay-600">○</span> Open to semesters 7 to 8</li>
                                <li><span class="text-clay-600">○</span> Needs a different English test</li>
                            </ul>
                        </article>
                    </div>
                    <p class="mt-4 text-center text-xs text-ink-soft">Illustrative examples only.</p>
                </div>
            </section>

            {{-- closing call --}}
            @guest
                <section class="max-w-3xl mx-auto px-4 sm:px-6 py-20 text-center">
                    <h2 class="font-display text-3xl font-semibold">Ready to see where you fit?</h2>
                    <a href="{{ route('register') }}" class="mt-8 inline-flex items-center justify-center px-7 py-3 rounded-full bg-aura-gradient text-white font-semibold shadow-md shadow-brand-500/30 hover:brightness-105">Get started, it's free</a>
                </section>
            @endguest
        </main>

        <footer class="border-t border-cream-dark">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-sm text-ink-soft flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <x-wordmark class="opacity-80" />
                <p class="max-w-xl">Qualora shows scholarships you <em>may</em> be eligible for. It is not a guarantee. Always verify requirements and deadlines on each scholarship's official site.</p>
            </div>
        </footer>
    </body>
</html>
