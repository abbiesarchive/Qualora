<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @auth
            @if (app(\App\Services\RealtimeBroadcaster::class)->enabled())
                {{-- Live updates via Supabase Realtime Broadcast. Publishable key is public by design. --}}
                <meta name="supabase-url" content="{{ config('services.supabase.url') }}">
                <meta name="supabase-publishable-key" content="{{ config('services.supabase.publishable_key') }}">
                <meta name="realtime-user-topic" content="{{ \App\Services\RealtimeBroadcaster::userTopic(auth()->user()) }}">
            @endif
        @endauth

        <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|fraunces:500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased">
        <div class="min-h-screen bg-cream">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        {{-- Live update notice, filled by resources/js/realtime.js --}}
        <div id="live-notice" hidden role="status" aria-live="polite"
            data-msg-refreshed="{{ __('This page was just updated with the latest scholarship information.') }}"
            data-msg-stale="{{ __('A scholarship on this page has changed. Refresh to see the latest details.') }}"
            data-msg-removed="{{ __('This scholarship is no longer listed. Please check the official site before applying.') }}"
            data-msg-saved="{{ __('Your saved scholarships were updated.') }}"
            class="fixed inset-x-4 bottom-4 z-50 mx-auto max-w-md rounded-2xl bg-white p-4 text-sm text-ink shadow-lg ring-1 ring-cream-dark sm:inset-x-auto sm:right-6">
            <div class="flex items-start gap-3">
                <p data-live-text class="flex-1"></p>
                <button type="button" data-live-refresh hidden
                    class="shrink-0 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white hover:bg-brand-700">{{ __('Refresh') }}</button>
                <button type="button" data-live-close class="shrink-0 text-ink-soft hover:text-ink" aria-label="{{ __('Close') }}">&times;</button>
            </div>
        </div>
    </body>
</html>
