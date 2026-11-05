<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        $validated = $request->validate($this->rules($request));
        $validated = $this->normalizeListFields($validated);

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
        $validated = $request->validate($this->rules($request, $scholarship));
        $validated = $this->normalizeListFields($validated);

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

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(Request $request, ?Scholarship $scholarship = null): array
    {
        $uniqueName = Rule::unique('scholarships', 'name')
            ->where(fn (Builder $query) => $query->where('year', $request->input('year')));

        if ($scholarship) {
            $uniqueName->ignore($scholarship->id);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'provider' => ['required', 'string', 'max:255'],
            'campus' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'min_gpa' => ['nullable', 'numeric', 'min:0', 'max:9.99'],
            'min_semester' => ['nullable', 'integer', 'min:1', 'max:255'],
            'max_semester' => ['nullable', 'integer', 'min:1', 'max:255'],
            'max_age' => ['nullable', 'integer', 'min:1', 'max:255'],
            'requires_financial_need' => ['nullable', 'boolean'],
            'majors' => ['nullable', 'string'],
            'allowed_nationalities' => ['nullable', 'string'],
            'english_test_type' => ['nullable', 'string', 'max:255'],
            'min_english_score' => ['nullable', 'numeric', 'min:0', 'max:9999.9'],
            'scholarship_type' => ['nullable', Rule::in(['full', 'partial'])],
            'is_abroad' => ['required', 'boolean'],
            'other_requirements' => ['nullable', 'string'],
            'required_documents' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'open_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
            'official_url' => ['required', 'url', 'max:255'],
            'last_verified' => ['required', 'date'],
            'year' => ['required', 'integer', 'min:2000', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeListFields(array $validated): array
    {
        foreach (['majors', 'allowed_nationalities', 'required_documents'] as $field) {
            $items = preg_split('/[\r\n,]+/', $validated[$field] ?? '') ?: [];
            $validated[$field] = array_values(array_unique(array_filter(array_map('trim', $items))));
        }

        return $validated;
    }
}
