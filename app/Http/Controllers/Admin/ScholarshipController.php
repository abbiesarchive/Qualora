<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Support\ScholarshipRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    public function index(): View
    {
        return view('admin.scholarships.index', [
            'scholarships' => Scholarship::query()
                ->orderByDesc('created_at')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.scholarships.create', [
            'scholarship' => new Scholarship,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(ScholarshipRules::rules($request->input('year')));
        $validated = ScholarshipRules::normalizeListFields($validated);

        $scholarship = Scholarship::create($validated);

        return redirect()
            ->route('admin.scholarships.show', $scholarship)
            ->with('status', 'Scholarship added successfully.');
    }

    public function show(Scholarship $scholarship): View
    {
        return view('admin.scholarships.show', compact('scholarship'));
    }

    public function edit(Scholarship $scholarship): View
    {
        return view('admin.scholarships.edit', compact('scholarship'));
    }

    public function update(Request $request, Scholarship $scholarship): RedirectResponse
    {
        $validated = $request->validate(ScholarshipRules::rules($request->input('year'), $scholarship->id));
        $validated = ScholarshipRules::normalizeListFields($validated);

        $scholarship->update($validated);

        return redirect()
            ->route('admin.scholarships.show', $scholarship)
            ->with('status', 'Scholarship updated successfully.');
    }

    public function destroy(Scholarship $scholarship): RedirectResponse
    {
        $scholarship->delete();

        return redirect()
            ->route('admin.scholarships.index')
            ->with('status', 'Scholarship deleted successfully.');
    }
}
