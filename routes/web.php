<?php

use App\Http\Controllers\Admin\FormSubmissionController as AdminFormSubmissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VentureDiagnosticController as AdminVentureDiagnosticController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect(auth()->check() ? '/dashboard' : '/login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Admin routes - protected by permissions
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users management
        Route::middleware('can:view users')->group(function () {
            Route::resource('users', UserController::class);
        });

        // Roles management
        Route::middleware('can:view roles')->group(function () {
            Route::resource('roles', RoleController::class);
        });

        // Venture Diagnostics management
        Route::prefix('diagnostics')->name('diagnostics.')->group(function () {
            Route::get('/', [AdminVentureDiagnosticController::class, 'index'])->name('index');
            Route::get('{diagnostic}', [AdminVentureDiagnosticController::class, 'show'])->name('show');
            Route::put('{diagnostic}', [AdminVentureDiagnosticController::class, 'update'])->name('update');
            Route::delete('{diagnostic}', [AdminVentureDiagnosticController::class, 'destroy'])->name('destroy');
        });

        // Form Submissions management
        Route::prefix('submissions')->name('submissions.')->group(function () {
            Route::get('/', [AdminFormSubmissionController::class, 'index'])->name('index');
            Route::get('{submission}', [AdminFormSubmissionController::class, 'show'])->name('show');
            Route::put('{submission}', [AdminFormSubmissionController::class, 'update'])->name('update');
            Route::delete('{submission}', [AdminFormSubmissionController::class, 'destroy'])->name('destroy');
        });
    });
});

require __DIR__.'/settings.php';
