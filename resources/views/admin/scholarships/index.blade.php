<x-app-layout>
    <x-slot name="title">Scholarship management</x-slot>

    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-aura-glow pointer-events-none" aria-hidden="true"></div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
            <p class="text-sm font-semibold text-aura-700">Administration</p>
            <div class="mt-1 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="font-display text-3xl sm:text-4xl font-semibold tracking-tight">Scholarships</h1>
                    <p class="mt-2 text-ink-soft">{{ $scholarships->total() }} scholarships</p>
                </div>
                <a href="{{ route('admin.scholarships.create') }}" class="inline-flex items-center justify-center rounded-full bg-aura-gradient px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    Add scholarship
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        @if (session('status'))
            <div role="status" class="mb-5 rounded-xl bg-sage-100 px-4 py-3 text-sm font-medium text-sage-800">{{ session('status') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl bg-white ring-1 ring-cream-dark" aria-label="Scholarship list">
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
                                    <th scope="row" class="px-5 py-4 font-semibold text-ink">{{ $scholarship->name }}</th>
                                    <td class="px-5 py-4 text-ink-soft">{{ $scholarship->provider }}</td>
                                    <td class="px-5 py-4 text-ink-soft">{{ $scholarship->min_gpa ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-ink-soft">{{ $scholarship->open_date?->format('j M Y') ?? '—' }}</td>
                                    <td class="px-5 py-4 text-ink-soft">{{ $scholarship->scholarship_type ? ucfirst($scholarship->scholarship_type) : '—' }}</td>
                                    <td class="px-5 py-4">
                                        <span @class([
                                            'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                                            'bg-sage-100 text-sage-800' => $scholarship->is_active,
                                            'bg-clay-100 text-clay-800' => ! $scholarship->is_active,
                                        ])>{{ $scholarship->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.scholarships.show', $scholarship) }}" class="rounded-full border border-cream-dark px-3 py-1.5 text-xs font-semibold text-ink hover:bg-cream">View</a>
                                            <a href="{{ route('admin.scholarships.edit', $scholarship) }}" class="rounded-full border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-50">Edit</a>
                                            <form method="POST" action="{{ route('admin.scholarships.destroy', $scholarship) }}" onsubmit="return confirm('Delete this scholarship? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-clay-200 px-3 py-1.5 text-xs font-semibold text-clay-700 hover:bg-clay-50">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-cream-dark px-5 py-4">{{ $scholarships->links() }}</div>
            @else
                <div class="px-6 py-12 text-center">
                    <h2 class="font-display text-lg font-semibold">No scholarships yet</h2>
                    <p class="mt-2 text-sm text-ink-soft">Add a scholarship to display it here.</p>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
