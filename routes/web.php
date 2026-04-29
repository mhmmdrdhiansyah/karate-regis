<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KontingenManagementController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Modules\AuthManagement\Controllers\UserController;
use App\Modules\AuthManagement\Controllers\RoleController;
use App\Modules\AuthManagement\Controllers\PermissionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profil User (Semua user login boleh akses)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/kontingen', [ProfileController::class, 'updateKontingen'])->name('profile.update.kontingen');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Auth Management
    Route::prefix('auth')->name('auth.')->group(function () {
        // Users - Panitia bisa akses (punya permission)
        Route::resource('users', UserController::class);

        // Roles & Permissions - Hanya super-admin
        Route::middleware(['role:super-admin'])->group(function () {
            Route::resource('roles', RoleController::class);
            Route::resource('permissions', PermissionController::class);
        });
    });

    // Kontingen Management
    Route::middleware(['permission:view kontingen|create kontingen|edit kontingen'])->group(function () {
        Route::resource('kontingen', KontingenManagementController::class)->except(['destroy']);
    });
    Route::middleware(['permission:delete kontingen'])->group(function () {
        Route::delete('kontingen/{kontingen}', [KontingenManagementController::class, 'destroy'])->name('kontingen.destroy');
    });

    // Peserta / Bank Peserta (Role Kontingen)
    Route::middleware(['permission:view participants|create participants|edit participants'])->group(function () {
        Route::resource('participants', ParticipantController::class)->except(['destroy']);
    });
    Route::middleware(['permission:delete participants'])->group(function () {
        Route::delete('participants/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');
    });
});

require __DIR__ . '/auth.php';
