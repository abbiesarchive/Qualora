<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class UserLanguageController extends Controller
{
    public const LANGUAGES = ['English', 'Mandarin', 'Japanese', 'Korean', 'German', 'French', 'Arabic', 'Other'];

    public const TESTS = ['IELTS', 'TOEFL iBT', 'TOEFL ITP', 'TOEIC', 'Duolingo', 'JLPT', 'TOPIK', 'HSK', 'Other'];

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
            'language_name' => ['required', 'string', Rule::in(self::LANGUAGES)],
            'certificate_type' => ['nullable', 'string', Rule::in(self::TESTS)],
            'certificate_score' => ['nullable', 'string', 'max:20'],
        ];
    }

    private function profileForAuthenticatedUser(): Profile
    {
        return Profile::query()
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
