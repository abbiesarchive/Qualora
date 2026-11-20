<?php

namespace App\Http\Controllers\Ai;

use App\Exceptions\GeminiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExtractProfileRequest;
use App\Services\ProfileExtractionService;
use Illuminate\Http\RedirectResponse;

class ExtractProfileController extends Controller
{
    /** Session key holding language/experience suggestions waiting for confirmation. */
    public const SESSION_KEY = 'ai_suggestions';

    /**
     * Session key holding AI pre-filled form values. They stay until the student
     * saves the profile (ProfileController@update clears them), so a page refresh
     * or adding the suggested items never loses them.
     */
    public const PREFILL_KEY = 'ai_prefill';

    /**
     * POST /ai/extract-profile: runs only when the student clicks the button.
     * It never saves anything. Form fields pre-fill the profile form; languages
     * and experiences become suggestions the student must tick and confirm.
     */
    public function __invoke(ExtractProfileRequest $request, ProfileExtractionService $service): RedirectResponse
    {
        try {
            $result = $service->extract($request->validated('text'));
        } catch (GeminiException $e) {
            return redirect()->route('profile.edit')->with('ai_error', $e->getMessage());
        }

        $suggestions = $this->withoutDuplicates($request, $result['languages'], $result['experiences']);

        if ($result['fields'] === [] && $suggestions['languages'] === [] && $suggestions['experiences'] === []) {
            return redirect()->route('profile.edit')
                ->with('ai_error', __('We could not find any profile details in that text. Try adding things like your major, semester and GPA, or fill in the form by hand.'));
        }

        $request->session()->forget(self::SESSION_KEY);

        if ($suggestions['languages'] !== [] || $suggestions['experiences'] !== []) {
            $request->session()->put(self::SESSION_KEY, $suggestions);
        }

        if ($result['fields'] !== []) {
            $request->session()->put(self::PREFILL_KEY, $result['fields']);
        }

        return redirect()->route('profile.edit');
    }

    /**
     * Hide suggestions the student already has on their profile.
     *
     * @param  list<array<string, ?string>>  $languages
     * @param  list<array<string, ?string>>  $experiences
     * @return array{languages: list<array<string, ?string>>, experiences: list<array<string, ?string>>}
     */
    private function withoutDuplicates(ExtractProfileRequest $request, array $languages, array $experiences): array
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            return ['languages' => $languages, 'experiences' => $experiences];
        }

        $haveLanguages = $profile->languages->map(fn ($l) => $this->key($l->language_name, $l->certificate_type, $l->certificate_score))->all();
        $haveExperiences = $profile->experiences->map(fn ($x) => $this->key($x->experience_type, $x->experience_name))->all();

        return [
            'languages' => array_values(array_filter($languages, fn ($l) => ! in_array(
                $this->key($l['language_name'], $l['certificate_type'], $l['certificate_score']), $haveLanguages, true))),
            'experiences' => array_values(array_filter($experiences, fn ($x) => ! in_array(
                $this->key($x['experience_type'], $x['experience_name']), $haveExperiences, true))),
        ];
    }

    private function key(?string ...$parts): string
    {
        return mb_strtolower(implode('|', array_map(fn ($p) => trim((string) $p), $parts)));
    }
}
