<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\UserStudyPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class UserStudyPlanController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $profile = $request->user()->profile()->firstOrCreate([]);

        if ($profile->studyPlan()->exists()) {
            return Redirect::route('profile.edit')->with('status', 'study-plan-already-exists');
        }

        $profile->studyPlan()->create($validated);

        return Redirect::route('profile.edit')->with('status', 'study-plan-created');
    }

    public function update(Request $request, string $userStudyPlan): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $studyPlan = $this->profileForAuthenticatedUser()
            ->studyPlan()
            ->whereKey($userStudyPlan)
            ->firstOrFail();

        $studyPlan->update($validated);

        return Redirect::route('profile.edit')->with('status', 'study-plan-updated');
    }

    public function destroy(string $userStudyPlan): RedirectResponse
    {
        $this->profileForAuthenticatedUser()
            ->studyPlan()
            ->whereKey($userStudyPlan)
            ->firstOrFail()
            ->delete();

        return Redirect::route('profile.edit')->with('status', 'study-plan-deleted');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            'study_field_category' => ['nullable', 'string', Rule::in(UserStudyPlan::FIELDS)],
            'preferred_country' => ['nullable', 'string', Rule::in(UserStudyPlan::LOCATIONS)],
        ];
    }

    private function profileForAuthenticatedUser(): Profile
    {
        return Profile::query()
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
