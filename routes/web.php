<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedScholarshipController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\Admin\ScholarshipController as AdminScholarshipController;
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

    // User Application Tracking & Announcement Monitoring
    Route::get('/my-applications', [\App\Http\Controllers\UserApplicationController::class, 'index'])
        ->middleware('verified')
        ->name('applications.index');
    Route::post('/scholarships/{scholarship}/apply-track', [\App\Http\Controllers\UserApplicationController::class, 'store'])
        ->middleware('verified')
        ->name('applications.store');
    Route::patch('/my-applications/{application}', [\App\Http\Controllers\UserApplicationController::class, 'update'])
        ->middleware('verified')
        ->name('applications.update');
    Route::delete('/my-applications/{application}', [\App\Http\Controllers\UserApplicationController::class, 'destroy'])
        ->middleware('verified')
        ->name('applications.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
