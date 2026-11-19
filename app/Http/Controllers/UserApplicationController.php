<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Models\UserApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserApplicationController extends Controller
{
    /**
     * Log a scholarship application tracked by the user.
     */
    public function store(Request $request, Scholarship $scholarship): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:preparing,submitted,interview,accepted,rejected,withdrawn'],
            'applied_at' => ['required', 'date'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->applications()->updateOrCreate(
            ['scholarship_id' => $scholarship->id],
            $validated
        );

        return back()->with('status', __('Your scholarship application has been saved. You can now track announcement dates.'));
    }

    /**
     * Update application status or notes.
     */
    public function update(Request $request, UserApplication $application): RedirectResponse
    {
        if ($application->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:preparing,submitted,interview,accepted,rejected,withdrawn'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $application->update($validated);

        return back()->with('status', __('Your application status has been updated.'));
    }

    /**
     * Remove a scholarship from tracking.
     */
    public function destroy(Request $request, UserApplication $application): RedirectResponse
    {
        if ($application->user_id !== $request->user()->id) {
            abort(403);
        }

        $application->delete();

        return back()->with('status', __('The scholarship has been removed from your application tracker.'));
    }
}
