<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Modules\AuthManagement\Controllers\UserController;
use App\Modules\AuthManagement\Controllers\RoleController;
use App\Modules\AuthManagement\Controllers\PermissionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profil User (Semua user login boleh akses)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Auth Management - Super Admin Only
    Route::middleware(['role:super-admin'])->group(function () {
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('roles', RoleController::class);
            Route::resource('permissions', PermissionController::class);
        });
    });
});

require __DIR__ . '/auth.php';
