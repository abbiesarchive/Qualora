<x-app-layout>
    <x-slot name="title">{{ $scholarship->name }}</x-slot>

    <div x-data="{ openTrackModal: false }" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-20">
        {{-- Flash Status Message --}}
        @if (session('status'))
            <div class="mb-6 rounded-2xl bg-sage-50 border border-sage-200 p-4 text-sm font-medium text-sage-900 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-sage-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
                <a href="{{ route('applications.index') }}" class="text-xs font-semibold text-brand-700 hover:underline">Lihat Pemantauan &rarr;</a>
            </div>
        @endif

        {{-- Top Back Navigation (<-) --}}
        <div class="mb-4">
            <a href="{{ route('scholarships.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-900 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke daftar beasiswa</span>
            </a>
        </div>

        {{-- MAIN HERO CARD (SEPERTI SKETSA GAMBAR) --}}
        <article class="rounded-3xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark shadow-sm">
            {{-- 1. Top Badges (Left) & Bookmark (Right) --}}
            <div class="flex items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-aura-100 px-3.5 py-1 text-xs font-semibold text-aura-800">
                        {{ $scholarship->provider }}
                    </span>
                    @if ($scholarship->scholarship_category)
                        <span class="rounded-full bg-brand-50 px-3.5 py-1 text-xs font-semibold text-brand-700">
                            {{ $scholarship->scholarship_category }}
                        </span>
                    @endif
                    @if ($scholarship->scholarship_type)
                        <span class="rounded-full bg-cream-dark px-3.5 py-1 text-xs font-semibold text-ink-soft">
                            {{ ucfirst($scholarship->scholarship_type) }}
                        </span>
                    @endif
                    <span @class([
                        'rounded-full px-3.5 py-1 text-xs font-semibold',
                        'bg-sage-100 text-sage-800' => $scholarship->is_active,
                        'bg-clay-100 text-clay-800' => ! $scholarship->is_active,
                    ])>{{ $scholarship->is_active ? 'Active' : 'Closed' }}</span>
                </div>

                <div class="shrink-0 flex items-center">
                    <x-scholarship-bookmark :scholarship="$scholarship" :is-saved="$isSaved" />
                </div>
            </div>

            {{-- 2. Title --}}
            <h1 class="mt-4 font-display text-2xl sm:text-3xl font-bold tracking-tight text-ink">
                {{ $scholarship->name }}
            </h1>

            {{-- 3. Short description / subtitle --}}
            <p class="mt-2 text-sm text-ink-soft leading-relaxed">
                {{ $scholarship->description ?? $scholarship->benefits ?? 'Program beasiswa pendidikan tinggi untuk mahasiswa S1 berprestasi.' }}
            </p>

            {{-- 4. Full-width divider line --}}
            <hr class="my-6 border-cream-dark">

            {{-- 5. Countdown text ("25 hari lagi") --}}
            <div>
                <p class="font-display text-2xl sm:text-3xl font-bold {{ ($scholarship->days_until_deadline !== null && $scholarship->days_until_deadline <= 7) ? 'text-clay-600' : 'text-brand-700' }}">
                    @if ($scholarship->days_until_deadline !== null)
                        @if ($scholarship->days_until_deadline > 0)
                            {{ $scholarship->days_until_deadline }} hari lagi
                        @elseif ($scholarship->days_until_deadline === 0)
                            Hari ini batas akhir pendaftaran!
                        @else
                            Pendaftaran telah ditutup
                        @endif
                    @else
                        Pendaftaran Aktif
                    @endif
                </p>
            </div>

            {{-- 6. Two columns: Batas (Left) & Announcement (Right) --}}
            <div class="mt-4 grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-bold text-ink-soft uppercase tracking-wider">Batas</p>
                    <p class="mt-1 font-bold text-base sm:text-lg text-ink">
                        {{ $scholarship->deadline?->format('d M Y') ?? 'Tidak ditentukan' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-bold text-ink-soft uppercase tracking-wider">Announcement</p>
                    <p class="mt-1 font-bold text-base sm:text-lg text-ink">
                        {{ $scholarship->announcement_date?->format('d M Y') ?? 'Akan diumumkan' }}
                    </p>
                    @if ($scholarship->days_until_announcement !== null && $scholarship->days_until_announcement > 0)
                        <span class="text-xs font-medium text-aura-700">({{ $scholarship->days_until_announcement }} hari lagi)</span>
                    @endif
                </div>
            </div>

            {{-- 7. Two pill buttons side-by-side at the bottom: Web resmi & Daftar --}}
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Button 1: Web resmi --}}
                <a
                    href="{{ $scholarship->apply_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="h-12 rounded-full border-2 border-brand-600 bg-white px-6 font-semibold text-sm sm:text-base text-brand-700 hover:bg-brand-50 flex items-center justify-center gap-2 transition shadow-sm"
                >
                    <span>Web resmi</span>
                    <span class="sr-only">Visit official website</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>

                {{-- Button 2: Daftar --}}
                @if ($userApplication)
                    <button
                        type="button"
                        @click="openTrackModal = true"
                        class="h-12 rounded-full bg-brand-50 border-2 border-brand-500 px-6 font-semibold text-sm sm:text-base text-brand-800 hover:bg-brand-100 flex items-center justify-center gap-2 transition shadow-sm"
                    >
                        <span>Status: {{ $userApplication->status_label }}</span>
                        <span class="text-xs underline text-brand-600">(Ubah)</span>
                    </button>
                @else
                    <button
                        type="button"
                        @click="openTrackModal = true"
                        class="h-12 rounded-full bg-aura-gradient px-6 font-semibold text-sm sm:text-base text-white hover:brightness-105 flex items-center justify-center gap-2 transition shadow-md"
                    >
                        <span>Daftar</span>
                    </button>
                @endif
            </div>
        </article>

        {{-- DETAIL SECTIONS BELOW THE HERO CARD --}}

        {{-- 1. HASIL PENGECEKAN KELAYAKAN (ELIGIBILITY CHECK) --}}
        <section class="mt-10 rounded-3xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-display text-xl font-bold text-ink">Hasil Pengecekan Syarat Kamu</h2>
                    <p class="text-xs text-ink-soft mt-0.5">Pencocokan otomatis berdasarkan data profil akademik kamu.</p>
                </div>
                @if (!$hasProfile)
                    <a href="{{ route('profile.edit') }}" class="text-xs font-semibold text-brand-700 hover:underline">
                        Lengkapi Profil &rarr;
                    </a>
                @endif
            </div>

            @if (count($evaluations) > 0)
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($evaluations as $eval)
                        <div class="rounded-2xl border p-4 flex items-start justify-between gap-3 {{ $eval['status'] === 'pass' ? 'border-sage-200 bg-sage-50/50' : ($eval['status'] === 'fail' ? 'border-clay-200 bg-clay-50/50' : 'border-cream-dark bg-white') }}">
                            <div>
                                <p class="text-xs font-semibold text-ink-soft">{{ $eval['label'] }}</p>
                                <p class="text-sm font-bold text-ink mt-0.5">{{ $eval['requirement'] }}</p>
                                <p class="text-xs mt-1 text-ink-soft">Profil kamu: <span class="font-medium text-ink">{{ $eval['user_value'] }}</span></p>
                            </div>
                            <div class="shrink-0">
                                @if ($eval['status'] === 'pass')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-sage-100 px-2.5 py-1 text-xs font-semibold text-sage-800">
                                        &check; Memenuhi
                                    </span>
                                @elseif ($eval['status'] === 'fail')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-clay-100 px-2.5 py-1 text-xs font-semibold text-clay-800">
                                        &times; Belum Memenuhi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-cream-dark px-2.5 py-1 text-xs font-semibold text-ink-soft">
                                        ? Belum Diisi
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl bg-cream/30 p-4 text-sm text-ink-soft">
                    Kriteria kelayakan umum: Terbuka untuk seluruh mahasiswa aktif dengan kualifikasi standar.
                </div>
            @endif

            @if ($scholarship->eligibility?->additional_notes || $scholarship->other_requirements)
                <div class="mt-4 rounded-2xl bg-cream/40 p-4 text-xs text-ink-soft leading-relaxed">
                    <span class="font-bold text-ink">Catatan Kualifikasi Tambahan:</span>
                    {{ $scholarship->eligibility?->additional_notes ?? $scholarship->other_requirements }}
                </div>
            @endif
        </section>

        {{-- 2. FASILITAS & BENEFIT --}}
        @if ($scholarship->benefitsList->isNotEmpty() || $scholarship->benefits)
            <section class="mt-8 rounded-3xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark shadow-sm">
                <h2 class="font-display text-xl font-bold text-ink mb-4">Manfaat & Cakupan Beasiswa</h2>
                @if ($scholarship->benefitsList->isNotEmpty())
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($scholarship->benefitsList as $benefit)
                            <div class="rounded-2xl bg-cream/40 p-4 border border-cream-dark">
                                <span class="rounded-full bg-white px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-aura-800 border border-cream-dark">
                                    {{ $benefit->benefit_type }}
                                </span>
                                <h3 class="font-semibold text-sm text-ink mt-2">{{ $benefit->benefit_name }}</h3>
                                @if ($benefit->amount)
                                    <p class="text-xs font-bold text-brand-700 mt-1">
                                        {{ $benefit->currency }} {{ number_format($benefit->amount, 0, ',', '.') }}
                                    </p>
                                @endif
                                @if ($benefit->benefit_description)
                                    <p class="text-xs text-ink-soft mt-1">{{ $benefit->benefit_description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm whitespace-pre-line text-ink-soft">{{ $scholarship->benefits }}</p>
                @endif
            </section>
        @endif

        {{-- 3. CHECKLIST DOKUMEN PERSYARATAN --}}
        <section class="mt-8 rounded-3xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark shadow-sm" x-data="{ checkedDocs: {} }">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="font-display text-xl font-bold text-ink">Checklist Dokumen Pendaftaran</h2>
                    <p class="text-xs text-ink-soft">Centang dokumen yang sudah siap di perangkat kamu sebelum apply ke website resmi.</p>
                </div>
            </div>

            @if ($scholarship->documentsList->isNotEmpty())
                <div class="mt-4 space-y-2">
                    @foreach ($scholarship->documentsList as $doc)
                        <label class="flex items-start gap-3 rounded-2xl border border-cream-dark p-3.5 hover:bg-cream/30 transition cursor-pointer">
                            <input
                                type="checkbox"
                                x-model="checkedDocs[{{ $doc->id }}]"
                                class="mt-1 rounded border-cream-dark text-brand-600 focus:ring-brand-500"
                            >
                            <div class="flex-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-sm text-ink" :class="{ 'line-through text-ink-soft': checkedDocs[{{ $doc->id }}] }">
                                        {{ $doc->document_name }}
                                    </span>
                                    @if ($doc->is_required)
                                        <span class="rounded bg-clay-50 px-1.5 py-0.5 text-[10px] font-bold text-clay-700">Wajib</span>
                                    @endif
                                </div>
                                @if ($doc->document_description)
                                    <p class="text-ink-soft mt-0.5">{{ $doc->document_description }}</p>
                                @endif
                            </div>
                            @if ($doc->template_url)
                                <a href="{{ $doc->template_url }}" target="_blank" class="shrink-0 text-xs font-semibold text-brand-700 hover:underline">
                                    Download Template &rarr;
                                </a>
                            @endif
                        </label>
                    @endforeach
                </div>
            @elseif ($scholarship->required_documents)
                <ul class="mt-4 space-y-2">
                    @foreach ($scholarship->required_documents as $idx => $doc)
                        <li class="flex items-center gap-3 rounded-2xl border border-cream-dark p-3 text-xs">
                            <input type="checkbox" class="rounded border-cream-dark text-brand-600 focus:ring-brand-500">
                            <span class="font-medium text-ink">{{ $doc }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-ink-soft mt-3">Silakan periksa daftar dokumen lengkap pada website resmi penyelenggara.</p>
            @endif
        </section>

        {{-- 4. TAHAPAN SELEKSI & TIMELINE --}}
        @if ($scholarship->selections->isNotEmpty())
            <section class="mt-8 rounded-3xl bg-white p-6 sm:p-8 ring-1 ring-cream-dark shadow-sm">
                <h2 class="font-display text-xl font-bold text-ink mb-1">Tahapan Seleksi & Pengumuman</h2>
                <p class="text-xs text-ink-soft mb-6">Alur tahapan pendaftaran hingga penetapan penerima beasiswa.</p>

                <div class="relative pl-6 border-l-2 border-brand-200 space-y-6">
                    @foreach ($scholarship->selections as $stage)
                        <div class="relative">
                            <div class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-brand-600 ring-4 ring-white"></div>
                            <div class="rounded-2xl bg-cream/30 p-4 border border-cream-dark">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="font-bold text-sm text-ink">{{ $stage->stage_order }}. {{ $stage->stage_name }}</span>
                                    @if ($stage->estimated_date)
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-aura-800 border border-cream-dark">
                                            {{ $stage->estimated_date->format('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                                @if ($stage->stage_description)
                                    <p class="text-xs text-ink-soft mt-2">{{ $stage->stage_description }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- MODAL: CATAT PENDAFTARAN SAYA --}}
        <div
            x-show="openTrackModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                <div
                    x-show="openTrackModal"
                    @click="openTrackModal = false"
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"
                ></div>

                <div
                    x-show="openTrackModal"
                    class="relative transform overflow-hidden rounded-3xl bg-white p-6 text-left shadow-xl transition-all sm:w-full sm:max-w-lg"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-cream-dark">
                        <h3 class="font-display text-lg font-bold text-ink">Catat Pendaftaran Saya</h3>
                        <button type="button" @click="openTrackModal = false" class="text-ink-soft hover:text-ink">
                            &times;
                        </button>
                    </div>

                    <form action="{{ route('applications.store', $scholarship) }}" method="POST" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-ink mb-1">Status Lamaran Saat Ini</label>
                            <select name="status" class="w-full rounded-xl border-cream-dark text-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="submitted" @selected(($userApplication->status ?? '') === 'submitted')>Sudah Submit di Web Resmi</option>
                                <option value="preparing" @selected(($userApplication->status ?? '') === 'preparing')>Sedang Menyiapkan Berkas</option>
                                <option value="interview" @selected(($userApplication->status ?? '') === 'interview')>Lolos ke Tahap Wawancara</option>
                                <option value="accepted" @selected(($userApplication->status ?? '') === 'accepted')>Lolos / Diterima Beasiswa</option>
                                <option value="rejected" @selected(($userApplication->status ?? '') === 'rejected')>Belum Berhasil</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-ink mb-1">Tanggal Submit di Web Resmi</label>
                            <input
                                type="date"
                                name="applied_at"
                                value="{{ old('applied_at', $userApplication?->applied_at?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                                class="w-full rounded-xl border-cream-dark text-sm focus:border-brand-500 focus:ring-brand-500"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-ink mb-1">Nomor Registrasi / ID Pendaftaran (Opsional)</label>
                            <input
                                type="text"
                                name="registration_number"
                                placeholder="Cth: BU-2026-98124"
                                value="{{ old('registration_number', $userApplication?->registration_number ?? '') }}"
                                class="w-full rounded-xl border-cream-dark text-sm focus:border-brand-500 focus:ring-brand-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-ink mb-1">Catatan Pribadi (Opsional)</label>
                            <textarea
                                name="notes"
                                rows="3"
                                placeholder="Cth: Akun terdaftar dengan email kampus, interview dijadwalkan tanggal 15 Des jam 10 via Zoom."
                                class="w-full rounded-xl border-cream-dark text-sm focus:border-brand-500 focus:ring-brand-500"
                            >{{ old('notes', $userApplication?->notes ?? '') }}</textarea>
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-2 border-t border-cream-dark">
                            <button
                                type="button"
                                @click="openTrackModal = false"
                                class="rounded-full px-4 py-2 text-xs font-semibold text-ink-soft hover:bg-cream"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="rounded-full bg-aura-gradient px-5 py-2 text-xs font-semibold text-white shadow hover:brightness-105"
                            >
                                Simpan ke Pemantauan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
