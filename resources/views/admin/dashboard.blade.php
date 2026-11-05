<x-app-layout>
    <x-slot name="title">Admin dashboard</x-slot>

    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-aura-glow pointer-events-none" aria-hidden="true"></div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
            <p class="text-sm font-semibold text-aura-700">Administration</p>
            <h1 class="mt-1 font-display text-3xl sm:text-4xl font-semibold tracking-tight">Scholarship management</h1>
            <p class="mt-2 text-ink-soft">Manage the scholarships shown to students.</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <section class="overflow-hidden rounded-2xl bg-white ring-1 ring-cream-dark" aria-labelledby="scholarships-heading">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-cream-dark p-5 sm:p-6">
                <div>
                    <h2 id="scholarships-heading" class="font-display text-xl font-semibold">Scholarships</h2>
                    <p class="mt-1 text-sm text-ink-soft">{{ $scholarships->total() }} total</p>
                </div>
                <button type="button" disabled title="Available in a future update" class="inline-flex cursor-not-allowed items-center justify-center rounded-full bg-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 opacity-80">
                    Add scholarship
                </button>
            </div>

            @if ($scholarships->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-cream-dark text-left text-sm">
                        <thead class="bg-cream/70 text-xs uppercase tracking-wide text-ink-soft">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-semibold">Scholarship</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Provider</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Min GPA</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Open date</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Type</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                                <th scope="col" class="px-5 py-3 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-dark">
                            @foreach ($scholarships as $scholarship)
                                <tr class="align-top">
                                    <th scope="row" class="px-5 py-4 font-semibold text-ink">
                                        {{ $scholarship->name }}
                                    </th>
                                    <td class="px-5 py-4 text-ink-soft">{{ $scholarship->provider }}</td>
                                    <td class="px-5 py-4 text-ink-soft">{{ $scholarship->min_gpa ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-ink-soft">
                                        {{ $scholarship->open_date?->format('j M Y') ?? '—' }}
                                    </td>
                                    <td class="px-5 py-4 text-ink-soft">
                                        {{ $scholarship->scholarship_type ? ucfirst($scholarship->scholarship_type) : '—' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span @class([
                                            'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                                            'bg-sage-100 text-sage-800' => $scholarship->is_active,
                                            'bg-clay-100 text-clay-800' => ! $scholarship->is_active,
                                        ])>{{ $scholarship->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <button type="button" disabled title="Available in a future update" class="cursor-not-allowed rounded-full border border-cream-dark px-3 py-1.5 text-xs font-semibold text-gray-500 opacity-70">
                                                Edit
                                            </button>
                                            <button type="button" disabled title="Available in a future update" class="cursor-not-allowed rounded-full border border-clay-200 px-3 py-1.5 text-xs font-semibold text-clay-700 opacity-70">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-cream-dark px-5 py-4">
                    {{ $scholarships->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <h3 class="font-display text-lg font-semibold">No scholarships yet</h3>
                    <p class="mt-2 text-sm text-ink-soft">The scholarship list will appear here when data is added.</p>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
