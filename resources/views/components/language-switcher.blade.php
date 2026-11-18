{{-- EN | ID toggle. Works for guests too; the choice is kept in a cookie. --}}
<form method="POST" action="{{ route('language.switch') }}" {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full bg-white p-0.5 ring-1 ring-cream-dark text-xs font-semibold']) }} aria-label="{{ __('Interface language') }}">
    @csrf
    @foreach (\App\Support\Locales::SUPPORTED as $code => $name)
        <button
            type="submit"
            name="locale"
            value="{{ $code }}"
            lang="{{ $code }}"
            title="{{ $name }}"
            @class([
                'rounded-full px-2.5 py-1 transition focus:outline-none focus:ring-2 focus:ring-brand-500',
                'bg-aura-gradient text-white shadow-sm' => app()->getLocale() === $code,
                'text-ink-soft hover:text-brand-700' => app()->getLocale() !== $code,
            ])
            @if (app()->getLocale() === $code) aria-current="true" @endif
        >{{ strtoupper($code) }}</button>
    @endforeach
</form>
