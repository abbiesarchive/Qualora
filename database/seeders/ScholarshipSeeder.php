<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use Illuminate\Database\Seeder;

class ScholarshipSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) today()->format('Y');

        $scholarshipsData = [
            [
                'name' => 'Djarum Beasiswa Plus 2026',
                'provider' => 'Djarum Foundation',
                'scholarship_type' => 'Private',
                'scholarship_category' => 'Fully Funded',
                'min_gpa' => 3.20,
                'min_semester' => 4,
                'max_semester' => 4,
                'max_age' => 23,
                'deadline' => today()->addDays(20),
                'announcement_date' => today()->addDays(40),
                'official_url' => 'https://djarumbeasiswaplus.org',
                'application_link' => 'https://djarumbeasiswaplus.org/register',
                'description' => 'Program beasiswa prestasi yang memberikan dana tunjangan belajar dan pelatihan soft skills kepemimpinan, komunikasi, serta wawasan kebangsaan selama satu tahun.',
                'eligibility' => [
                    'min_gpa' => 3.20,
                    'min_semester' => 4,
                    'max_semester' => 4,
                    'max_age' => 23,
                    'requires_work_experience' => false,
                    'additional_notes' => 'Aktif berorganisasi baik di dalam maupun luar kampus.',
                ],
                'benefits' => [
                    ['type' => 'Living Allowance', 'name' => 'Uang Saku Bulanan', 'amount' => 1000000, 'currency' => 'IDR', 'desc' => 'Diberikan setiap bulan selama 1 tahun.'],
                    ['type' => 'Mentorship', 'name' => 'Pelatihan Soft Skills', 'amount' => null, 'desc' => 'Nation Building, Character Building, & Leadership Development.'],
                ],
                'documents' => [
                    ['name' => 'Transkrip Nilai Semester 1–3', 'desc' => 'Dilegalisir oleh pihak fakultas / universitas.', 'required' => true],
                    ['name' => 'Surat Keterangan Aktif Organisasi', 'desc' => 'Menyertakan SK kepengurusan atau sertifikat kepanitiaan.', 'required' => true],
                    ['name' => 'Pasfoto 4x6 Berwarna', 'desc' => 'Memakai jas almamater kampus.', 'required' => true],
                    ['name' => 'Surat Pernyataan Tidak Menerima Beasiswa Lain', 'desc' => 'Format surat disediakan oleh pihak kampus.', 'required' => true],
                ],
                'selections' => [
                    ['name' => 'Pendaftaran Online & Berkas', 'order' => 1, 'date' => today()->addDays(20), 'desc' => 'Submit formulir di website resmi djarum.'],
                    ['name' => 'Tes Tertulis & Psikotes', 'order' => 2, 'date' => today()->addDays(28), 'desc' => 'Tes potensi akademik online.'],
                    ['name' => 'Wawancara', 'order' => 3, 'date' => today()->addDays(35), 'desc' => 'Wawancara mendalam bersama tim Djarum Foundation.'],
                    ['name' => 'Pengumuman Penerima Beasiswa', 'order' => 4, 'date' => today()->addDays(40), 'desc' => 'Pengumuman resmi di portal Djarum Beasiswa Plus.'],
                ],
            ],
            [
                'name' => 'Beasiswa Unggulan Kemendikbudristek',
                'provider' => 'Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi',
                'scholarship_type' => 'Government',
                'scholarship_category' => 'Fully Funded',
                'min_gpa' => 3.25,
                'min_semester' => 1,
                'max_semester' => 3,
                'max_age' => 22,
                'deadline' => today()->addDays(15),
                'announcement_date' => today()->addDays(35),
                'official_url' => 'https://beasiswaunggulan.kemdikbud.go.id',
                'application_link' => 'https://beasiswaunggulan.kemdikbud.go.id/pendaftaran',
                'description' => 'Beasiswa penuh pemerintah bagi mahasiswa S1 berprestasi tingkat nasional maupun internasional untuk menunjang biaya kuliah dan biaya hidup selama masa studi.',
                'eligibility' => [
                    'min_gpa' => 3.25,
                    'min_semester' => 1,
                    'max_semester' => 3,
                    'max_age' => 22,
                    'min_university_accreditation' => 'B',
                    'additional_notes' => 'Memiliki sertifikat prestasi minimal tingkat provinsi atau karya ilmiah.',
                ],
                'benefits' => [
                    ['type' => 'Tuition', 'name' => 'Pembiayaan Biaya Kuliah (UKT)', 'amount' => null, 'desc' => 'Ditanggung penuh sampai lulus.'],
                    ['type' => 'Living Allowance', 'name' => 'Biaya Hidup Bulanan', 'amount' => 1400000, 'currency' => 'IDR', 'desc' => 'Bantuan akomodasi dan biaya hidup bulanan.'],
                    ['type' => 'Book Allowance', 'name' => 'Biaya Buku', 'amount' => 2000000, 'currency' => 'IDR', 'desc' => 'Bantuan dana buku tahunan.'],
                ],
                'documents' => [
                    ['name' => 'Kartu Tanda Mahasiswa (KTM)', 'desc' => 'Scan berwarna asli.', 'required' => true],
                    ['name' => 'Transkrip Nilai Terakhir', 'desc' => 'IPK minimal 3.25 skala 4.00.', 'required' => true],
                    ['name' => 'Esai 3–5 Halaman', 'desc' => 'Tema: Kontribusiku untuk Indonesia.', 'required' => true],
                    ['name' => 'Surat Rekomendasi Dosen/Dekan', 'desc' => 'Ditandatangani oleh pejabat fakultas.', 'required' => true],
                ],
                'selections' => [
                    ['name' => 'Pendaftaran & Seleksi Administrasi', 'order' => 1, 'date' => today()->addDays(15), 'desc' => 'Verifikasi keabsahan dokumen online.'],
                    ['name' => 'Pengumuman Seleksi Administrasi', 'order' => 2, 'date' => today()->addDays(25), 'desc' => 'Hasil seleksi berkas.'],
                    ['name' => 'Wawancara Kebangsaan', 'order' => 3, 'date' => today()->addDays(30), 'desc' => 'Wawancara panel via daring.'],
                    ['name' => 'Pengumuman Hasil Akhir', 'order' => 4, 'date' => today()->addDays(35), 'desc' => 'Pengumuman penetapan SK penerima beasiswa.'],
                ],
            ],
            [
                'name' => 'Beasiswa Karya Salemba Empat (KSE)',
                'provider' => 'Yayasan Karya Salemba Empat',
                'scholarship_type' => 'Private',
                'scholarship_category' => 'Partial',
                'min_gpa' => 3.00,
                'min_semester' => 2,
                'max_semester' => 6,
                'deadline' => today()->addDays(8),
                'announcement_date' => today()->addDays(22),
                'official_url' => 'https://kse.or.id',
                'application_link' => 'https://beasiswa.or.id',
                'description' => 'Program bantuan finansial dan pembinaan kepemimpinan mahasiswa di 35 Perguruan Tinggi Negeri mitra KSE di seluruh Indonesia.',
                'eligibility' => [
                    'min_gpa' => 3.00,
                    'min_semester' => 2,
                    'max_semester' => 6,
                    'requires_financial_need' => true,
                    'additional_notes' => 'Diutamakan mahasiswa dari keluarga berpenghasilan rendah yang memiliki semangat kepemimpinan tinggi.',
                ],
                'benefits' => [
                    ['type' => 'Living Allowance', 'name' => 'Tunjangan Biaya Hidup', 'amount' => 750000, 'currency' => 'IDR', 'desc' => 'Diberikan setiap bulan.'],
                    ['type' => 'Mentorship', 'name' => 'Leadership Camp KSE', 'amount' => null, 'desc' => 'Pelatihan kepemimpinan dan kewirausahaan.'],
                ],
                'documents' => [
                    ['name' => 'Curriculum Vitae (CV) KSE', 'desc' => 'Gunakan template format resmi KSE.', 'required' => true],
                    ['name' => 'Transkrip Nilai Legalisir', 'desc' => 'Transkrip resmi sampai semester terakhir.', 'required' => true],
                    ['name' => 'Slip Gaji Orang Tua / Surat Keterangan Penghasilan', 'desc' => 'Disahkan oleh kelurahan atau instansi tempat bekerja.', 'required' => true],
                ],
                'selections' => [
                    ['name' => 'Batas Submit Berkas', 'order' => 1, 'date' => today()->addDays(8), 'desc' => 'Submit formulir di portal beasiswa.or.id.'],
                    ['name' => 'Seleksi Berkas & Rekomendasi Kampus', 'order' => 2, 'date' => today()->addDays(14), 'desc' => 'Screening oleh paguyuban KSE kampus.'],
                    ['name' => 'Pengumuman Penerima KSE', 'order' => 3, 'date' => today()->addDays(22), 'desc' => 'Pengumuman resmi SK beasiswa.'],
                ],
            ],
            [
                'name' => 'Bank Indonesia Scholarship 2026',
                'provider' => 'Bank Indonesia',
                'scholarship_type' => 'Government',
                'scholarship_category' => 'Fully Funded',
                'min_gpa' => 3.00,
                'min_semester' => 3,
                'max_semester' => 6,
                'max_age' => 23,
                'deadline' => today()->addDays(25),
                'announcement_date' => today()->addDays(45),
                'official_url' => 'https://bi.go.id/id/karier/beasiswa',
                'application_link' => 'https://bi.go.id/id/karier/beasiswa/apply',
                'description' => 'Program beasiswa dari Bank Indonesia untuk menciptakan generasi muda yang memiliki integritas dan kepedulian sosial tinggi melalui komunitas Generasi Baru Indonesia (GenBI).',
                'eligibility' => [
                    'min_gpa' => 3.00,
                    'min_semester' => 3,
                    'max_semester' => 6,
                    'max_age' => 23,
                    'requires_financial_need' => false,
                    'additional_notes' => 'Bersedia aktif dalam wadah komunitas GenBI Bank Indonesia.',
                ],
                'benefits' => [
                    ['type' => 'Living Allowance', 'name' => 'Bantuan Biaya Belajar', 'amount' => 1000000, 'currency' => 'IDR', 'desc' => 'Bantuan dana bulanan.'],
                    ['type' => 'Mentorship', 'name' => 'Wadah Komunitas GenBI', 'amount' => null, 'desc' => 'Jejaring mahasiswa berprestasi dan program sosial BI.'],
                ],
                'documents' => [
                    ['name' => 'Formulir Biodata A1 Beasiswa BI', 'desc' => 'Formulir resmi dari kantor perwakilan BI setempat.', 'required' => true],
                    ['name' => 'Transkrip Nilai Akademik', 'desc' => 'IPK minimal 3.00.', 'required' => true],
                    ['name' => 'Motivation Letter Bahasa Indonesia', 'desc' => 'Motivasi bergabung dengan GenBI.', 'required' => true],
                ],
                'selections' => [
                    ['name' => 'Penutupan Registrasi', 'order' => 1, 'date' => today()->addDays(25), 'desc' => 'Batas upload berkas di portal kampus & BI.'],
                    ['name' => 'Seleksi Wawancara BI', 'order' => 2, 'date' => today()->addDays(36), 'desc' => 'Wawancara panel perwakilan Bank Indonesia.'],
                    ['name' => 'Pengumuman Hasil Seleksi', 'order' => 3, 'date' => today()->addDays(45), 'desc' => 'Pengumuman resmi nama penerima beasiswa BI.'],
                ],
            ],
            [
                'name' => 'Beasiswa BCA Finance Peduli 2026',
                'provider' => 'PT BCA Finance',
                'scholarship_type' => 'Corporate',
                'scholarship_category' => 'Partial',
                'min_gpa' => 3.20,
                'min_semester' => 4,
                'max_semester' => 6,
                'deadline' => today()->addDays(18),
                'announcement_date' => today()->addDays(38),
                'official_url' => 'https://bcafinance.co.id/beasiswa',
                'application_link' => 'https://bcafinance.co.id/beasiswa/register',
                'description' => 'Bantuan dana pendidikan perguruan tinggi dari BCA Finance untuk mahasiswa berprestasi yang sedang menempuh semester 4 hingga semester 6.',
                'eligibility' => [
                    'min_gpa' => 3.20,
                    'min_semester' => 4,
                    'max_semester' => 6,
                    'additional_notes' => 'Terbuka untuk PTN dan PTS terakreditasi minimal B.',
                ],
                'benefits' => [
                    ['type' => 'Tuition', 'name' => 'Bantuan Dana Kuliah', 'amount' => 3500000, 'currency' => 'IDR', 'desc' => 'Bantuan per semester.'],
                ],
                'documents' => [
                    ['name' => 'Transkrip Nilai Semester 1–4', 'desc' => 'IPK minimal 3.20.', 'required' => true],
                    ['name' => 'Surat Keterangan Tidak Sedang Menerima Beasiswa', 'desc' => 'Dari bagian kemahasiswaan.', 'required' => true],
                ],
                'selections' => [
                    ['name' => 'Pendaftaran Tahap 1', 'order' => 1, 'date' => today()->addDays(18), 'desc' => 'Submit data online di website BCA Finance.'],
                    ['name' => 'Verifikasi Berkas', 'order' => 2, 'date' => today()->addDays(28), 'desc' => 'Pengiriman dokumen fisik bagi yang lolos.'],
                    ['name' => 'Pengumuman Penerima', 'order' => 3, 'date' => today()->addDays(38), 'desc' => 'Pengumuman pemenang beasiswa.'],
                ],
            ],
        ];

        foreach ($scholarshipsData as $data) {
            $eligibility = $data['eligibility'] ?? [];
            $benefits = $data['benefits'] ?? [];
            $documents = $data['documents'] ?? [];
            $selections = $data['selections'] ?? [];

            unset($data['eligibility'], $data['benefits'], $data['documents'], $data['selections']);

            $scholarship = Scholarship::firstOrNew(['name' => $data['name'], 'year' => $year]);
            $scholarship->forceFill($data + [
                'level' => 'S1',
                'year' => $year,
                'open_date' => today()->subDays(10),
                'last_verified' => today(),
                'is_active' => true,
            ])->save();

            // 1. Sync Eligibility
            if ($eligibility) {
                $scholarship->eligibility()->updateOrCreate([], $eligibility);
            }

            // 2. Sync Benefits
            $scholarship->benefitsList()->delete();
            foreach ($benefits as $b) {
                $scholarship->benefitsList()->create([
                    'benefit_type' => $b['type'],
                    'benefit_name' => $b['name'],
                    'amount' => $b['amount'],
                    'currency' => $b['currency'] ?? 'IDR',
                    'benefit_description' => $b['desc'] ?? null,
                    'coverage_status' => 'Fully Covered',
                ]);
            }

            // 3. Sync Documents
            $scholarship->documentsList()->delete();
            foreach ($documents as $d) {
                $scholarship->documentsList()->create([
                    'document_name' => $d['name'],
                    'document_description' => $d['desc'] ?? null,
                    'is_required' => $d['required'] ?? true,
                ]);
            }

            // 4. Sync Selection Stages
            $scholarship->selections()->delete();
            foreach ($selections as $s) {
                $scholarship->selections()->create([
                    'stage_name' => $s['name'],
                    'stage_order' => $s['order'],
                    'estimated_date' => $s['date'],
                    'stage_description' => $s['desc'] ?? null,
                    'is_required' => true,
                ]);
            }
        }
    }
}
