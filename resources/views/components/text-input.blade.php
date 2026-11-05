@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-cream-dark bg-white focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-sm']) }}>
