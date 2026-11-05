<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    <x-application-logo class="h-8 w-8 text-brand-500" />
    <span class="font-display text-xl font-semibold tracking-tight text-ink">{{ config('app.name') }}</span>
</span>
