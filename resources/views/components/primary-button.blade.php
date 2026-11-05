<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-aura-gradient border border-transparent rounded-full font-semibold text-sm text-white shadow-sm shadow-brand-500/30 hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-cream active:brightness-95 transition duration-150 ease-in-out']) }}>
    {{ $slot }}
</button>
