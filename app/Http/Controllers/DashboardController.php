<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Eligibility fields that count toward profile completeness (label => column). */
    private const PROFILE_FIELDS = [
        'GPA' => 'gpa',
        'Semester' => 'semester',
        'Major' => 'major',
        'Age' => 'age',
        'Campus' => 'campus',
        'Nationality' => 'nationality',
        'Financial need' => 'has_financial_need',
    ];

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $profile = $user->profile;

        // Missing values mean "unknown", so null (not false) is what counts as unfilled.
        $missing = collect(self::PROFILE_FIELDS)
            ->filter(fn (string $column) => $profile === null || $profile->{$column} === null || $profile->{$column} === '')
            ->keys()
            ->values();

        $total = count(self::PROFILE_FIELDS);

        $saved = $user->savedScholarships()
            ->with('scholarship')
            ->get()
            ->pluck('scholarship')
            ->filter()
            ->sortBy(fn (Scholarship $s) => $s->deadline?->timestamp ?? PHP_INT_MAX)
            ->values();

        $closingSoon = Scholarship::query()
            ->where('is_active', true)
            ->whereDate('deadline', '>=', today())
            ->orderBy('deadline')
            ->limit(4)
            ->get();

        $trackedApplications = $user->applications()
            ->with('scholarship')
            ->orderByDesc('applied_at')
            ->limit(3)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'hasProfile' => $profile !== null,
            'missing' => $missing,
            'completePercent' => (int) round(($total - $missing->count()) / $total * 100),
            'saved' => $saved,
            'closingSoon' => $closingSoon,
            'trackedApplications' => $trackedApplications,
        ]);
    }
}
