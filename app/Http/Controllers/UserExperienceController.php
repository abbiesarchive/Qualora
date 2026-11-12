<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class UserExperienceController extends Controller
{
    private const EXPERIENCE_TYPES = [
        'Organization',
        'Internship',
        'Work Experience',
        'Volunteer',
        'Competition',
        'Leadership',
        'Project',
        'Other',
    ];

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));
        $profile = $request->user()->profile()->firstOrCreate([]);
        $profile->experiences()->create($validated);

        return Redirect::route('profile.edit')->with('status', 'experience-created');
    }

    public function update(Request $request, string $userExperience): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));
        $this->profileForAuthenticatedUser()
            ->experiences()
            ->whereKey($userExperience)
            ->firstOrFail()
            ->update($validated);

        return Redirect::route('profile.edit')->with('status', 'experience-updated');
    }

    public function destroy(string $userExperience): RedirectResponse
    {
        $this->profileForAuthenticatedUser()
            ->experiences()
            ->whereKey($userExperience)
            ->firstOrFail()
            ->delete();

        return Redirect::route('profile.edit')->with('status', 'experience-deleted');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(Request $request): array
    {
        $rules = [
            'experience_type' => ['nullable', 'string', 'in:'.implode(',', self::EXPERIENCE_TYPES)],
            'experience_name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ];

        if ($request->filled('start_date')) {
            $rules['end_date'][] = 'after_or_equal:start_date';
        }

        return $rules;
    }

    private function profileForAuthenticatedUser(): Profile
    {
        return Profile::query()
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
