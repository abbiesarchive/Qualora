<x-app-layout>
    <x-slot name="title">Edit scholarship</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 pb-16">
        <a href="{{ route('admin.scholarships.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-900">&larr; Back to scholarships</a>
        <h1 class="mt-4 font-display text-3xl font-semibold tracking-tight">Edit scholarship</h1>
        <p class="mt-2 text-ink-soft">{{ $scholarship->name }}</p>
        @include('admin.scholarships.form', ['isEdit' => true])
    </div>
</x-app-layout>
