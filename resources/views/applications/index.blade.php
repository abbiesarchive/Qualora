<x-app-layout>
    <x-slot name="title">Pemantauan Lamaran Beasiswa</x-slot>

    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-aura-glow pointer-events-none" aria-hidden="true"></div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
            <p class="text-sm font-semibold text-aura-700">Application Tracker & Announcement Countdown</p>
            <h1 class="mt-1 font-display text-3xl sm:text-4xl font-semibold tracking-tight text-ink">
                Pemantauan Lamaran
            </h1>
            <p class="mt-2 text-ink-soft max-w-2xl">
                Pantau sisa hari menuju pengumuman dan kelola status beasiswa yang telah kamu daftarkan di website resmi penyelenggara.
            </p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        {{-- Flash message --}}
        @if (session('status'))
            <div class="mb-6 rounded-2xl bg-sage-50 border border-sage-200 p-4 text-sm font-medium text-sage-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-sage-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- UPCOMING ANNOUNCEMENTS COUNTDOWN CARDS --}}
        @if ($upcomingAnnouncements->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-sm font-bold text-ink uppercase tracking-wider mb-3">
                    Jadwal Pengumuman Terdekat
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($upcomingAnnouncements as $app)
                        @php
                            $s = $app->scholarship;
                            $days = $s->days_until_announcement;
                        @endphp
                        <div class="relative overflow-hidden rounded-2xl bg-white p-5 ring-1 ring-cream-dark shadow-sm border-l-4 border-aura-600">
                            <span class="text-[11px] font-bold text-aura-700 uppercase tracking-wider">
                                {{ $s->provider }}
                            </span>
                            <h3 class="font-display font-bold text-base text-ink mt-0.5 truncate">
                                {{ $s->name }}
                            </h3>
                            <div class="mt-4 flex items-baseline justify-between">
                                <div>
                                    <span class="text-2xl font-bold font-display {{ $days <= 5 ? 'text-clay-600' : 'text-brand-700' }}">
                                        {{ $days }}
                                    </span>
                                    <span class="text-xs font-semibold text-ink-soft">Hari lagi</span>
                                </div>
                                <span class="text-xs font-semibold text-ink-soft">
                                    {{ $s->announcement_date->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- TRACKED APPLICATIONS LIST --}}
        @if ($applications->isNotEmpty())
            <div class="space-y-4">
                @foreach ($applications as $app)
                    @php
                        $scholarship = $app->scholarship;
                    @endphp
                    <div x-data="{ editing: false }" class="rounded-3xl bg-white p-6 ring-1 ring-cream-dark shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-aura-100 px-3 py-0.5 text-xs font-semibold text-aura-800">
                                        {{ $scholarship->provider }}
                                    </span>
                                    @if ($scholarship->scholarship_category)
                                        <span class="rounded-full bg-cream-dark px-2.5 py-0.5 text-xs font-semibold text-ink-soft">
                                            {{ $scholarship->scholarship_category }}
                                        </span>
                                    @endif
                                    <span @class([
                                        'rounded-full px-3 py-0.5 text-xs font-bold',
                                        'bg-sage-100 text-sage-800' => in_array($app->status, ['submitted', 'accepted']),
                                        'bg-aura-100 text-aura-800' => $app->status === 'interview',
                                        'bg-clay-100 text-clay-800' => in_array($app->status, ['rejected', 'withdrawn']),
                                        'bg-cream-dark text-ink-soft' => $app->status === 'preparing',
                                    ])>
                                        {{ $app->status_label }}
                                    </span>
                                </div>

                                <h3 class="mt-2 font-display text-xl font-bold text-ink">
                                    <a href="{{ route('scholarships.show', $scholarship) }}" class="hover:text-brand-700 transition">
                                        {{ $scholarship->name }}
                                    </a>
                                </h3>

                                <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-xs text-ink-soft">
                                    <div>
                                        <span>Tanggal Submit:</span>
                                        <span class="font-semibold text-ink">{{ $app->applied_at->format('d M Y') }}</span>
                                    </div>
                                    @if ($app->registration_number)
                                        <div>
                                            <span>No. Registrasi:</span>
                                            <span class="font-semibold font-mono text-ink">{{ $app->registration_number }}</span>
                                        </div>
                                    @endif
                                    @if ($scholarship->announcement_date)
                                        <div>
                                            <span>Estimasi Pengumuman:</span>
                                            <span class="font-semibold text-brand-700">
                                                {{ $scholarship->announcement_date->format('d M Y') }}
                                                @if ($scholarship->days_until_announcement !== null && $scholarship->days_until_announcement > 0)
                                                    ({{ $scholarship->days_until_announcement }} hari lagi)
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                @if ($app->notes)
                                    <div class="mt-3 rounded-2xl bg-cream/40 p-3 text-xs text-ink-soft leading-relaxed">
                                        <span class="font-semibold text-ink">Catatan Pribadi:</span> {{ $app->notes }}
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex md:flex-col items-center md:items-end gap-2 shrink-0">
                                <a
                                    href="{{ $scholarship->apply_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-aura-gradient px-4 py-2 text-xs font-semibold text-white shadow hover:brightness-105 transition"
                                >
                                    <span>Buka Portal Resmi</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>

                                <button
                                    type="button"
                                    @click="editing = !editing"
                                    class="rounded-full border border-cream-dark px-3.5 py-1.5 text-xs font-semibold text-ink-soft hover:bg-cream transition"
                                >
                                    <span x-show="!editing">Update Status</span>
                                    <span x-show="editing">Tutup</span>
                                </button>
                            </div>
                        </div>

                        {{-- Inline Edit Form --}}
                        <div x-show="editing" x-cloak class="mt-4 pt-4 border-t border-cream-dark">
                            <form action="{{ route('applications.update', $app) }}" method="POST" class="space-y-3">
                                @csrf
                                @method('PATCH')

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-ink mb-1">Ubah Status</label>
                                        <select name="status" class="w-full rounded-xl border-cream-dark text-xs focus:border-brand-500 focus:ring-brand-500">
                                            <option value="preparing" @selected($app->status === 'preparing')>Persiapan Berkas</option>
                                            <option value="submitted" @selected($app->status === 'submitted')>Sudah Submit di Web Resmi</option>
                                            <option value="interview" @selected($app->status === 'interview')>Tahap Wawancara</option>
                                            <option value="accepted" @selected($app->status === 'accepted')>Lolos / Diterima</option>
                                            <option value="rejected" @selected($app->status === 'rejected')>Belum Berhasil</option>
                                            <option value="withdrawn" @selected($app->status === 'withdrawn')>Mengundurkan Diri</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-ink mb-1">No. Registrasi Akun</label>
                                        <input
                                            type="text"
                                            name="registration_number"
                                            value="{{ old('registration_number', $app->registration_number) }}"
                                            class="w-full rounded-xl border-cream-dark text-xs focus:border-brand-500 focus:ring-brand-500"
                                            placeholder="No. pendaftaran dari web publisher"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-ink mb-1">Catatan Tambahan</label>
                                    <textarea
                                        name="notes"
                                        rows="2"
                                        class="w-full rounded-xl border-cream-dark text-xs focus:border-brand-500 focus:ring-brand-500"
                                        placeholder="Catatan jadwal tes, link wawancara, akun login..."
                                    >{{ old('notes', $app->notes) }}</textarea>
                                </div>

                                <div class="flex items-center justify-between pt-2">
                                    <button
                                        type="button"
                                        @click="if (confirm('Yakin ingin menghapus lamaran ini dari pemantauan?')) $refs.deleteForm.submit();"
                                        class="text-xs text-clay-700 hover:underline"
                                    >
                                        Hapus dari Pemantauan
                                    </button>

                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="editing = false"
                                            class="rounded-full px-3 py-1 text-xs text-ink-soft hover:bg-cream"
                                        >
                                            Batal
                                        </button>
                                        <button
                                            type="submit"
                                            class="rounded-full bg-brand-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-700"
                                        >
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <form x-ref="deleteForm" action="{{ route('applications.destroy', $app) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-3xl bg-white p-12 text-center ring-1 ring-cream-dark">
                <div class="mx-auto w-12 h-12 rounded-full bg-brand-50 flex items-center justify-center text-brand-700 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="font-display text-xl font-bold text-ink">Belum ada lamaran yang dipantau</h3>
                <p class="mt-2 text-sm text-ink-soft max-w-md mx-auto">
                    Setelah mendaftar di website resmi beasiswa, kamu bisa klik tombol <span class="font-semibold text-ink">"Catat Pendaftaran Saya"</span> pada halaman beasiswa untuk memantau tanggal pengumuman di sini.
                </p>
                <div class="mt-6">
                    <a
                        href="{{ route('scholarships.index') }}"
                        class="inline-flex items-center justify-center rounded-full bg-aura-gradient px-5 py-2.5 text-sm font-semibold text-white shadow hover:brightness-105"
                    >
                        Jelajahi Beasiswa Sekarang
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

