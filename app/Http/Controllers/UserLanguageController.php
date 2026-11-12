<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class UserLanguageController extends Controller
{
    private const PROFICIENCY_LEVELS = [
        'Beginner',
        'Elementary',
        'Intermediate',
        'Upper Intermediate',
        'Advanced',
        'Proficient / Native',
    ];

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $profile = $request->user()->profile()->firstOrCreate([]);
        $profile->languages()->create($validated);

        return Redirect::route('profile.edit')->with('status', 'language-created');
    }

    public function update(Request $request, string $userLanguage): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $this->profileForAuthenticatedUser()
            ->languages()
            ->whereKey($userLanguage)
            ->firstOrFail()
            ->update($validated);

        return Redirect::route('profile.edit')->with('status', 'language-updated');
    }

    public function destroy(string $userLanguage): RedirectResponse
    {
        $this->profileForAuthenticatedUser()
            ->languages()
            ->whereKey($userLanguage)
            ->firstOrFail()
            ->delete();

        return Redirect::route('profile.edit')->with('status', 'language-deleted');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            'language_name' => ['required', 'string', 'max:255'],
            'proficiency_level' => ['nullable', 'string', 'in:'.implode(',', self::PROFICIENCY_LEVELS)],
            'certificate_type' => ['nullable', 'string', 'max:255'],
            'certificate_score' => ['nullable', 'string'],
            'certificate_expiry' => ['nullable', 'date'],
        ];
    }

    private function profileForAuthenticatedUser(): Profile
    {
        return Profile::query()
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
