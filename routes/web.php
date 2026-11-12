<?php

use App\Http\Controllers\Admin\ScholarshipController as AdminScholarshipController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedScholarshipController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\UserExperienceController;
use App\Http\Controllers\UserLanguageController;
use App\Http\Controllers\UserStudyPlanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('scholarships', AdminScholarshipController::class);
    });

Route::middleware('auth')->group(function () {
    Route::get('/saved-scholarships', fn () => redirect()->route('scholarships.index', ['view' => 'saved']))
        ->middleware('verified')
        ->name('scholarships.saved');
    Route::post('/scholarships/{scholarship}/bookmark', [SavedScholarshipController::class, 'store'])
        ->middleware('verified')
        ->name('scholarships.bookmark.store');
    Route::delete('/scholarships/{scholarship}/bookmark', [SavedScholarshipController::class, 'destroy'])
        ->middleware('verified')
        ->name('scholarships.bookmark.destroy');

    Route::get('/scholarships', [ScholarshipController::class, 'index'])
        ->middleware('verified')
        ->name('scholarships.index');
    Route::get('/scholarships/{scholarship}', [ScholarshipController::class, 'show'])
        ->middleware('verified')
        ->name('scholarships.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/study-plan', [UserStudyPlanController::class, 'store'])->name('profile.study-plan.store');
    Route::patch('/profile/study-plan/{userStudyPlan}', [UserStudyPlanController::class, 'update'])->name('profile.study-plan.update');
    Route::delete('/profile/study-plan/{userStudyPlan}', [UserStudyPlanController::class, 'destroy'])->name('profile.study-plan.destroy');
    Route::post('/profile/languages', [UserLanguageController::class, 'store'])->name('profile.languages.store');
    Route::patch('/profile/languages/{userLanguage}', [UserLanguageController::class, 'update'])->name('profile.languages.update');
    Route::delete('/profile/languages/{userLanguage}', [UserLanguageController::class, 'destroy'])->name('profile.languages.destroy');
    Route::post('/profile/experiences', [UserExperienceController::class, 'store'])->name('profile.experiences.store');
    Route::patch('/profile/experiences/{userExperience}', [UserExperienceController::class, 'update'])->name('profile.experiences.update');
    Route::delete('/profile/experiences/{userExperience}', [UserExperienceController::class, 'destroy'])->name('profile.experiences.destroy');
});

require __DIR__.'/auth.php';
