<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Ai\ExtractProfileController;
use App\Http\Requests\AccountUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        // Pending AI pre-fill shows up through old(), unless the student's own failed
        // Save already put their input there. now() lasts for this request only.
        $prefill = $request->session()->get(ExtractProfileController::PREFILL_KEY);

        if ($prefill && $request->session()->getOldInput() === []) {
            $request->session()->now('_old_input', $prefill);
        }

        $profile = Profile::query()
            ->where('user_id', Auth::id())
            ->first();

        return view('profile.edit', [
            'user' => $request->user(),
            'profile' => $profile,
            'studyPlan' => $profile?->studyPlan,
            'languages' => $profile?->languages()->orderBy('language_name')->get() ?? collect(),
            'experiences' => $profile?->experiences()->orderBy('experience_name')->get() ?? collect(),
        ]);
    }

    public function editAccount(Request $request): View
    {
        return view('account.edit', [
            'user' => $request->user(),
        ]);
    }

    public function updateAccount(AccountUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('account.edit')->with('status', 'account-updated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $profileFields = [
            'campus', 'major', 'semester', 'gpa', 'age', 'nationality',
            'has_financial_need',
        ];
        $studyPlanFields = ['study_field_category', 'preferred_country'];
        $profileData = Arr::only($validated, $profileFields);
        $studyPlanData = Arr::only($validated, $studyPlanFields);
        $userData = Arr::except($validated, [...$profileFields, ...$studyPlanFields]);
        $user = $request->user();

        DB::transaction(function () use ($user, $userData, $profileData, $studyPlanData): void {
            $user->fill($userData);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            $profile = $profileData !== [] ? $user->profile()->updateOrCreate([], $profileData) : $user->profile;

            // Preferences ride along with the same Save. Don't create an empty row.
            if ($studyPlanData !== []) {
                $profile ??= $user->profile()->create([]);
                $existing = $profile->studyPlan;

                if ($existing || array_filter($studyPlanData) !== []) {
                    $profile->studyPlan()->updateOrCreate([], $studyPlanData);
                }
            }
        });

        // The pre-filled values are now saved (or replaced), so stop re-applying them.
        $request->session()->forget(ExtractProfileController::PREFILL_KEY);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
