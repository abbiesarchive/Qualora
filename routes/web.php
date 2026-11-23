<?php

use App\Http\Controllers\Admin\ScholarshipController as AdminScholarshipController;
use App\Http\Controllers\Admin\ScholarshipImportController;
use App\Http\Controllers\Ai\ApplySuggestionsController;
use App\Http\Controllers\Ai\ExplainController;
use App\Http\Controllers\Ai\ExtractProfileController;
use App\Http\Controllers\Ai\ExtractScholarshipsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedScholarshipController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\UserApplicationController;
use App\Http\Controllers\UserExperienceController;
use App\Http\Controllers\UserLanguageController;
use App\Http\Controllers\UserStudyPlanController;
use Illuminate\Support\Facades\Route;

Route::post('/language', [LocaleController::class, 'update'])->name('language.switch');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Import routes come before the resource so "import" is not read as a scholarship id.
        Route::get('scholarships/import', [ScholarshipImportController::class, 'create'])->name('scholarships.import');
        Route::post('scholarships/import', [ScholarshipImportController::class, 'store'])->name('scholarships.import.store');
        Route::post('scholarships/import/fetch', [ScholarshipImportController::class, 'fetch'])
            ->middleware('throttle:10,1')
            ->name('scholarships.import.fetch');
        Route::delete('scholarships/import', [ScholarshipImportController::class, 'discard'])->name('scholarships.import.discard');
        Route::post('ai/extract-scholarships', ExtractScholarshipsController::class)
            ->middleware('throttle:5,1')
            ->name('ai.extract-scholarships');
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

    // User Application Tracking & Announcement Monitoring
    Route::post('/scholarships/{scholarship}/apply-track', [UserApplicationController::class, 'store'])
        ->middleware('verified')
        ->name('applications.store');
    Route::patch('/my-applications/{application}', [UserApplicationController::class, 'update'])
        ->middleware('verified')
        ->name('applications.update');
    Route::delete('/my-applications/{application}', [UserApplicationController::class, 'destroy'])
        ->middleware('verified')
        ->name('applications.destroy');

    // AI endpoints: explicit button clicks only, signed-in and verified, rate limited.
    Route::post('/ai/explain', ExplainController::class)
        ->middleware(['verified', 'throttle:5,1'])
        ->name('ai.explain');
    Route::post('/ai/extract-profile', ExtractProfileController::class)
        ->middleware(['verified', 'throttle:5,1'])
        ->name('ai.extract-profile');
    Route::post('/ai/suggestions', ApplySuggestionsController::class)
        ->middleware('verified')
        ->name('ai.suggestions');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/account', [ProfileController::class, 'editAccount'])->name('account.edit');
    Route::patch('/account', [ProfileController::class, 'updateAccount'])->name('account.update');
    Route::delete('/account', [ProfileController::class, 'destroy'])->name('account.destroy');
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
