<?php

namespace App\Http\Controllers;

use App\Models\UserApplication;
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

        $applications = $user->applications()
            ->with('scholarship')
            ->orderByDesc('applied_at')
            ->get();
        $applicationCounts = [
            'total' => $applications->count(),
            'accepted' => $applications->where('status', 'accepted')->count(),
            'rejected' => $applications->where('status', 'rejected')->count(),
        ];
        $upcomingAnnouncements = $applications->filter(function (UserApplication $application) {
            $scholarship = $application->scholarship;

            return $scholarship?->announcement_date && $scholarship->announcement_date->isFuture();
        })->sortBy(fn (UserApplication $application) => $application->scholarship->announcement_date);

        return view('dashboard', [
            'user' => $user,
            'hasProfile' => $profile !== null,
            'missing' => $missing,
            'completePercent' => (int) round(($total - $missing->count()) / $total * 100),
            'applications' => $applications,
            'applicationCounts' => $applicationCounts,
            'upcomingAnnouncements' => $upcomingAnnouncements,
        ]);
    }
}
