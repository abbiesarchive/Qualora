<?php

namespace App\Http\Controllers;

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
        $profile = Profile::query()
            ->where('user_id', Auth::id())
            ->first();

        return view('profile.edit', [
            'user' => $request->user(),
            'profile' => $profile,
            'studyPlan' => $profile?->studyPlan,
            'languages' => $profile?->languages()->orderBy('language_name')->get() ?? collect(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $profileFields = [
            'campus', 'major', 'semester', 'gpa', 'age', 'nationality',
            'has_financial_need', 'faculty', 'gender', 'city_of_residence',
            'phone_number', 'graduation_year', 'graduation_status',
            'university_accreditation',
        ];
        $profileData = Arr::only($validated, $profileFields);
        $userData = Arr::except($validated, $profileFields);
        $user = $request->user();

        DB::transaction(function () use ($user, $userData, $profileData): void {
            $user->fill($userData);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            if ($profileData !== []) {
                $user->profile()->updateOrCreate([], $profileData);
            }
        });

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
