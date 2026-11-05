<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-white border border-cream-dark rounded-full font-semibold text-sm text-ink shadow-sm hover:bg-cream-dark focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-cream disabled:opacity-25 transition duration-150 ease-in-out']) }}>
    {{ $slot }}
</button>
