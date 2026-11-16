<?php

namespace App\Http\Controllers;

use App\Models\UserApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $applications = $user->applications()
            ->with('scholarship')
            ->whereHas('scholarship')
            ->orderByDesc('applied_at')
            ->get();

        $upcomingAnnouncements = $applications
            ->filter(fn (UserApplication $app) => $app->scholarship->announcement_date?->isFuture())
            ->sortBy(fn (UserApplication $app) => $app->scholarship->announcement_date);

        return view('dashboard', [
            'user' => $user,
            'applications' => $applications,
            'upcomingAnnouncements' => $upcomingAnnouncements,
            'stats' => [
                'tracked' => $applications->count(),
                'submitted' => $applications->whereNotIn('status', ['preparing', 'withdrawn'])->count(),
                'accepted' => $applications->where('status', 'accepted')->count(),
                'saved' => $user->savedScholarships()->count(),
            ],
        ]);
    }
}
