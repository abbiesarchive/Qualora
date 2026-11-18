<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Services\EligibilityService;
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

    public function show(Request $request, Scholarship $scholarship, EligibilityService $eligibilityService): View
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
        $profile = $user->profile?->loadMissing('languages', 'studyPlan');

        $isSaved = $user->savedScholarships()->where('scholarship_id', $scholarship->id)->exists();
        $userApplication = $user->applications()->where('scholarship_id', $scholarship->id)->first();

        $match = $eligibilityService->check($profile, $scholarship);

        return view('scholarships.show', [
            'scholarship' => $scholarship,
            'isSaved' => $isSaved,
            'userApplication' => $userApplication,
            'match' => $match,
            'hasProfile' => $profile !== null,
        ]);
    }
}
