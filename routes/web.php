<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\SchoolManagerApplicationController;

use App\Http\Controllers\SchoolManager\DashboardController as SchoolManagerDashboardController;
use App\Http\Controllers\SchoolManager\SchoolUpdateRequestController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UpdateReviewController;
use App\Http\Controllers\Admin\SchoolManagerApplicationReviewController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('schools.index');
});

Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
Route::get('/school-manager/apply', [SchoolManagerApplicationController::class, 'create'])->name('school-manager-applications.create');
Route::post('/school-manager/apply', [SchoolManagerApplicationController::class, 'store'])->name('school-manager-applications.store');
Route::get('/school-manager/applications/{application}', [SchoolManagerApplicationController::class, 'status'])->name('school-manager-applications.status');

Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{school}', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{school}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:school_manager'])->prefix('school-manager')->name('school-manager.')->group(function () {
    Route::get('/', [SchoolManagerDashboardController::class, 'index'])->name('dashboard');

    Route::get('/schools/{school}/edit', [SchoolUpdateRequestController::class, 'editSchool'])->name('schools.edit');
    Route::patch('/schools/{school}', [SchoolUpdateRequestController::class, 'updateSchool'])->name('schools.update');
    Route::post('/schools/{school}/deletion-request', [SchoolUpdateRequestController::class, 'requestDeletion'])->name('schools.deletion-request');

    Route::get('/updates', [SchoolUpdateRequestController::class, 'index'])->name('updates.index');
    Route::get('/updates/{id}', [SchoolUpdateRequestController::class, 'show'])->name('updates.show');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/manager-applications', [SchoolManagerApplicationReviewController::class, 'index'])->name('manager-applications.index');
    Route::get('/manager-applications/{schoolManagerApplication}', [SchoolManagerApplicationReviewController::class, 'show'])->name('manager-applications.show');
    Route::post('/manager-applications/{schoolManagerApplication}/approve', [SchoolManagerApplicationReviewController::class, 'approve'])->name('manager-applications.approve');
    Route::post('/manager-applications/{schoolManagerApplication}/reject', [SchoolManagerApplicationReviewController::class, 'reject'])->name('manager-applications.reject');

    Route::get('/updates', [UpdateReviewController::class, 'index'])->name('updates.index');
    Route::get('/updates/{schoolUpdateRequest}', [UpdateReviewController::class, 'show'])->name('updates.show');
    Route::post('/updates/{schoolUpdateRequest}/approve', [UpdateReviewController::class, 'approve'])->name('updates.approve');
    Route::post('/updates/{schoolUpdateRequest}/reject', [UpdateReviewController::class, 'reject'])->name('updates.reject');
});

require __DIR__.'/auth.php';
