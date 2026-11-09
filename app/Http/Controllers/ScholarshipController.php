<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
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
            'view' => ['nullable', 'in:all,saved'],
        ]);

        $scholarships = Scholarship::query()
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
            ->withExists([
                'savedBy as is_saved' => fn ($query) => $query->where('user_id', $request->user()->getKey()),
            ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('scholarships.index', [
            'scholarships' => $scholarships,
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Scholarship $scholarship): View
    {
        $scholarship->setAttribute(
            'is_saved',
            $request->user()->savedScholarships()->where('scholarship_id', $scholarship->getKey())->exists(),
        );

        return view('scholarships.show', compact('scholarship'));
    }
}
