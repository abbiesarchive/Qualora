<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplySuggestionsRequest;
use Illuminate\Http\RedirectResponse;

class ApplySuggestionsController extends Controller
{
    /**
     * POST /ai/suggestions: the student confirms (or dismisses) the AI's language
     * and experience suggestions. The data comes from the server-side session that
     * was validated at extraction time. The browser only says which ones to keep.
     */
    public function __invoke(ApplySuggestionsRequest $request): RedirectResponse
    {
        $suggestions = $request->session()->pull(ExtractProfileController::SESSION_KEY, []);
        $back = redirect()->route('profile.edit');

        if ($request->boolean('dismiss') || $suggestions === []) {
            return $back;
        }

        $profile = $request->user()->profile()->firstOrCreate([]);
        $added = 0;

        foreach ((array) $request->validated('languages', []) as $i) {
            if (isset($suggestions['languages'][$i])) {
                $profile->languages()->create($suggestions['languages'][$i]);
                $added++;
            }
        }

        foreach ((array) $request->validated('experiences', []) as $i) {
            if (isset($suggestions['experiences'][$i])) {
                $profile->experiences()->create($suggestions['experiences'][$i]);
                $added++;
            }
        }

        return $back->with('ai_added', $added);
    }
}
