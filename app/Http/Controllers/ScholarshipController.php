<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
            'type' => ['nullable', 'in:full,partial'],
            'category' => ['nullable', 'string', 'max:50'],
            'view' => ['nullable', 'in:all,saved'],
        ]);

        $scholarships = Scholarship::query()
            ->with(['eligibility', 'benefitsList'])
            ->when(($filters['view'] ?? 'all') === 'saved', function ($query) use ($request) {
                $query->whereHas('savedBy', fn ($query) => $query->where('user_id', $request->user()->getKey()));
            })
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('provider', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function ($query, string $status) {
                $query->where('is_active', $status === 'active');
            })
            ->when($filters['type'] ?? null, function ($query, string $type) {
                $query->where('scholarship_type', $type);
            })
            ->when($filters['category'] ?? null, function ($query, string $category) {
                $query->where('scholarship_category', $category);
            })
            ->withExists([
                'savedBy as is_saved' => fn ($query) => $query->where('user_id', $request->user()->getKey()),
                'applications as is_applied' => fn ($query) => $query->where('user_id', $request->user()->getKey()),
            ])
            ->orderByDesc('is_active')
            ->orderBy('deadline')
            ->paginate(9)
            ->withQueryString();

        return view('scholarships.index', [
            'scholarships' => $scholarships,
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Scholarship $scholarship): View
    {
        $scholarship->load([
            'eligibility',
            'studyRequirements',
            'languageRequirements',
            'benefitsList',
            'documentsList',
            'selections',
        ]);

        $user = $request->user();
        $profile = $user->profile;

        $isSaved = $user->savedScholarships()->where('scholarship_id', $scholarship->id)->exists();
        $userApplication = $user->applications()->where('scholarship_id', $scholarship->id)->first();

        // Calculate eligibility comparison
        $eligibility = $scholarship->eligibility;
        $evaluations = [];

        if ($eligibility) {
            if ($eligibility->min_gpa !== null) {
                $meets = $profile?->gpa !== null && $profile->gpa >= $eligibility->min_gpa;
                $evaluations[] = [
                    'label' => 'Indeks Prestasi Kumulatif (IPK)',
                    'requirement' => "Minimal {$eligibility->min_gpa}",
                    'user_value' => $profile?->gpa ? number_format($profile->gpa, 2) : 'Belum diisi',
                    'status' => $profile?->gpa === null ? 'unknown' : ($meets ? 'pass' : 'fail'),
                ];
            }

            if ($eligibility->min_semester !== null || $eligibility->max_semester !== null) {
                $min = $eligibility->min_semester ?? 1;
                $max = $eligibility->max_semester ?? 14;
                $meets = $profile?->semester !== null && $profile->semester >= $min && $profile->semester <= $max;
                $evaluations[] = [
                    'label' => 'Semester Aktif',
                    'requirement' => "Semester {$min} s/d {$max}",
                    'user_value' => $profile?->semester ? "Semester {$profile->semester}" : 'Belum diisi',
                    'status' => $profile?->semester === null ? 'unknown' : ($meets ? 'pass' : 'fail'),
                ];
            }

            if ($eligibility->max_age !== null) {
                $meets = $profile?->age !== null && $profile->age <= $eligibility->max_age;
                $evaluations[] = [
                    'label' => 'Batas Usia Maksimal',
                    'requirement' => "Maksimal {$eligibility->max_age} tahun",
                    'user_value' => $profile?->age ? "{$profile->age} tahun" : 'Belum diisi',
                    'status' => $profile?->age === null ? 'unknown' : ($meets ? 'pass' : 'fail'),
                ];
            }

            if ($eligibility->requires_financial_need) {
                $meets = $profile?->has_financial_need === true;
                $evaluations[] = [
                    'label' => 'Bantuan Ekonomi / Finansial',
                    'requirement' => 'Memerlukan bantuan finansial (Need-based)',
                    'user_value' => $profile?->has_financial_need ? 'Ya (Membutuhkan)' : ($profile?->has_financial_need === false ? 'Tidak' : 'Belum diisi'),
                    'status' => $profile?->has_financial_need === null ? 'unknown' : ($meets ? 'pass' : 'fail'),
                ];
            }

            if ($eligibility->min_university_accreditation) {
                $meets = $profile?->university_accreditation !== null && strcasecmp($profile->university_accreditation, $eligibility->min_university_accreditation) <= 0;
                $evaluations[] = [
                    'label' => 'Akreditasi Kampus',
                    'requirement' => "Minimal akreditasi {$eligibility->min_university_accreditation}",
                    'user_value' => $profile?->university_accreditation ? "Akreditasi {$profile->university_accreditation}" : 'Belum diisi',
                    'status' => $profile?->university_accreditation === null ? 'unknown' : ($meets ? 'pass' : 'fail'),
                ];
            }
        }

        return view('scholarships.show', [
            'scholarship' => $scholarship,
            'isSaved' => $isSaved,
            'userApplication' => $userApplication,
            'evaluations' => $evaluations,
            'hasProfile' => $profile !== null,
        ]);
    }
}
