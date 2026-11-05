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
        ]);

        $scholarships = Scholarship::query()
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
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('scholarships.index', [
            'scholarships' => $scholarships,
            'filters' => $filters,
        ]);
    }

    public function show(Scholarship $scholarship): View
    {
        return view('scholarships.show', compact('scholarship'));
    }
}
