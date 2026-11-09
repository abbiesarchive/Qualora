<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedScholarshipController extends Controller
{
    public function store(Request $request, Scholarship $scholarship): RedirectResponse
    {
        $request->user()->savedScholarships()->firstOrCreate([
            'scholarship_id' => $scholarship->getKey(),
        ]);

        return back()->with('status', 'Scholarship saved successfully.');
    }

    public function destroy(Request $request, Scholarship $scholarship): RedirectResponse
    {
        $request->user()
            ->savedScholarships()
            ->where('scholarship_id', $scholarship->getKey())
            ->delete();

        return back()->with('status', 'Scholarship removed from saved scholarships.');
    }
}
