<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\SchoolManager\VerificationController;
use App\Http\Controllers\SchoolManager\SubmissionController;
use App\Http\Controllers\SchoolManager\UpdateRequestController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('schools.index');
    }

    return redirect()->route('login');
});

Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{school}', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{school}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
});

Route::middleware(['auth', 'role:school_manager'])
    ->prefix('school-manager')
    ->name('school-manager.')
    ->group(function () {

        // dashboard (accessible to all school_manager users)
        Route::get('/', function () {
            return view('school-manager.dashboard');
        })->name('dashboard');

        //  verification is ALWAYS allowed
        Route::get('/verify', [VerificationController::class, 'index'])->name('verify.index');
        Route::get('/verify/create', [VerificationController::class, 'create'])->name('verify.create');
        Route::post('/verify', [VerificationController::class, 'store'])->name('verify.store');

        //  submissions require approved verification
        Route::middleware(['verified_manager'])->group(function () {
            Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
            Route::get('/submissions/create', [SubmissionController::class, 'create'])->name('submissions.create');
            Route::post('/submissions', [SubmissionController::class, 'store'])->name('submissions.store');
            Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');

            Route::get('/updates', [UpdateRequestController::class, 'index'])->name('updates.index');
            Route::get('/updates/create', [UpdateRequestController::class, 'create'])->name('updates.create');
            Route::post('/updates', [UpdateRequestController::class, 'store'])->name('updates.store');
            Route::get('/updates/{update}', [UpdateRequestController::class, 'show'])->name('updates.show');
        });
    });
require __DIR__.'/auth.php';