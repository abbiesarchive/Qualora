<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Services\RealtimeBroadcaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedScholarshipController extends Controller
{
    public function store(Request $request, Scholarship $scholarship, RealtimeBroadcaster $realtime): RedirectResponse
    {
        $saved = $request->user()->savedScholarships()->firstOrCreate([
            'scholarship_id' => $scholarship->getKey(),
        ]);

        if ($saved->wasRecentlyCreated) {
            $realtime->savedScholarshipsChanged($request->user());
        }

        return back()->with('status', __('Scholarship saved successfully.'));
    }

    public function destroy(Request $request, Scholarship $scholarship, RealtimeBroadcaster $realtime): RedirectResponse
    {
        $removed = $request->user()
            ->savedScholarships()
            ->where('scholarship_id', $scholarship->getKey())
            ->delete();

        if ($removed > 0) {
            $realtime->savedScholarshipsChanged($request->user());
        }

        return back()->with('status', __('Scholarship removed from saved scholarships.'));
    }
}
