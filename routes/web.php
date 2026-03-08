<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
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
    });
});

require __DIR__.'/settings.php';
