<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class UserExperienceController extends Controller
{
    public const EXPERIENCE_TYPES = [
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
        $validated = $request->validate($this->rules());
        $profile = $request->user()->profile()->firstOrCreate([]);
        $profile->experiences()->create($validated);

        return Redirect::route('profile.edit')->with('status', 'experience-created');
    }

    public function update(Request $request, string $userExperience): RedirectResponse
    {
        $validated = $request->validate($this->rules());
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
    private function rules(): array
    {
        return [
            'experience_type' => ['nullable', 'string', 'in:'.implode(',', self::EXPERIENCE_TYPES)],
            'experience_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function profileForAuthenticatedUser(): Profile
    {
        return Profile::query()
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
