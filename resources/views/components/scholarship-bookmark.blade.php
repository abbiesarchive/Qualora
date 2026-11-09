<form method="POST" action="{{ $isSaved ? route('scholarships.bookmark.destroy', $scholarship) : route('scholarships.bookmark.store', $scholarship) }}">
    @csrf
    @if ($isSaved)
        @method('DELETE')
    @endif
    <button
        type="submit"
        @class([
            'inline-flex h-10 w-10 items-center justify-center rounded-full transition focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2',
            'text-brand-700 hover:bg-brand-50' => $isSaved,
            'text-ink-soft hover:bg-cream-dark hover:text-brand-700' => ! $isSaved,
        ])
        aria-label="{{ $isSaved ? 'Remove '.$scholarship->name.' from saved scholarships' : 'Save '.$scholarship->name }}"
        aria-pressed="{{ $isSaved ? 'true' : 'false' }}"
        title="{{ $isSaved ? 'Remove from saved scholarships' : 'Save scholarship' }}"
    >
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="{{ $isSaved ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 4.5A2.25 2.25 0 0 1 9 2.25h6a2.25 2.25 0 0 1 2.25 2.25v17.25L12 18l-5.25 3.75V4.5Z" />
        </svg>
        <span class="sr-only">{{ $isSaved ? 'Saved' : 'Save' }}</span>
    </button>
</form>
