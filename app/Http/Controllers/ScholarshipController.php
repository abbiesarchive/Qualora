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
