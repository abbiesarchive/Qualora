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
            'type' => ['nullable', 'in:full,partial'],
            'category' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'in:domestic,abroad'],
            'view' => ['nullable', 'in:all,saved'],
        ]);

        $closingSoon = Scholarship::query()
            ->where('is_active', true)
            ->whereDate('deadline', '>=', today())
            ->orderBy('deadline')
            ->limit(4)
            ->get();

        $scholarships = Scholarship::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('open_date')
                    ->orWhereDate('open_date', '<=', today());
            })
            ->where(function ($query) {
                $query->whereNull('deadline')
                    ->orWhereDate('deadline', '>=', today());
            })
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
            ->when($filters['type'] ?? null, function ($query, string $type) {
                $query->where('scholarship_type', $type);
            })
            ->when($filters['category'] ?? null, function ($query, string $category) {
                $query->where('scholarship_category', $category);
            })
            ->when($filters['location'] ?? null, function ($query, string $location) {
                $query->where('is_abroad', $location === 'abroad');
            })
            ->withExists([
                'savedBy as is_saved' => fn ($query) => $query->where('user_id', $request->user()->getKey()),
                'applications as is_applied' => fn ($query) => $query->where('user_id', $request->user()->getKey()),
            ])
            ->orderBy('deadline')
            ->paginate(9)
            ->withQueryString();

        return view('scholarships.index', [
            'scholarships' => $scholarships,
            'filters' => $filters,
            'closingSoon' => $closingSoon,
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
                    'label' => 'Cumulative GPA',
                    'requirement' => "Minimum {$eligibility->min_gpa}",
                    'user_value' => $profile?->gpa ? number_format($profile->gpa, 2) : 'Not provided',
                    'status' => $profile?->gpa === null ? 'unknown' : ($meets ? 'pass' : 'fail'),
                ];
            }

            if ($eligibility->min_semester !== null || $eligibility->max_semester !== null) {
                $min = $eligibility->min_semester ?? 1;
                $max = $eligibility->max_semester ?? 14;
                $meets = $profile?->semester !== null && $profile->semester >= $min && $profile->semester <= $max;
                $evaluations[] = [
                    'label' => 'Current semester',
                    'requirement' => "Semester {$min} to {$max}",
                    'user_value' => $profile?->semester ? "Semester {$profile->semester}" : 'Not provided',
                    'status' => $profile?->semester === null ? 'unknown' : ($meets ? 'pass' : 'fail'),
                ];
            }

            if ($eligibility->max_age !== null) {
                $meets = $profile?->age !== null && $profile->age <= $eligibility->max_age;
                $evaluations[] = [
                    'label' => 'Maximum age',
                    'requirement' => "Up to {$eligibility->max_age} years",
                    'user_value' => $profile?->age ? "{$profile->age} years" : 'Not provided',
                    'status' => $profile?->age === null ? 'unknown' : ($meets ? 'pass' : 'fail'),
                ];
            }

            if ($eligibility->requires_financial_need) {
                $meets = $profile?->has_financial_need === true;
                $evaluations[] = [
                    'label' => 'Financial need',
                    'requirement' => 'Requires financial assistance',
                    'user_value' => $profile?->has_financial_need ? 'Yes (Needs assistance)' : ($profile?->has_financial_need === false ? 'No' : 'Not provided'),
                    'status' => $profile?->has_financial_need === null ? 'unknown' : ($meets ? 'pass' : 'fail'),
                ];
            }

            if ($eligibility->min_university_accreditation) {
                $meets = $profile?->university_accreditation !== null && strcasecmp($profile->university_accreditation, $eligibility->min_university_accreditation) <= 0;
                $evaluations[] = [
                    'label' => 'University accreditation',
                    'requirement' => "Minimum accreditation: {$eligibility->min_university_accreditation}",
                    'user_value' => $profile?->university_accreditation ? "Accreditation {$profile->university_accreditation}" : 'Not provided',
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
