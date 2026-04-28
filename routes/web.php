<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KontingenManagementController;
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
});

    // TEMP: Participant preview routes (hapus setelah controller siap)
    Route::prefix('participants')->name('participants.')->group(function () {
        Route::get('/', fn () => view('participants.index', ['participants' => collect()]))->name('index');
        Route::get('/create', fn () => view('participants.create'))->name('create');
        Route::post('/', fn () => redirect()->route('participants.index'))->name('store');
        Route::get('/{id}/edit', fn ($id) => view('participants.edit', [
            'participant' => \App\Models\Participant::factory()->make([
                'type' => \App\Enums\ParticipantType::Athlete,
                'name' => 'John Doe',
                'nik' => '1234567890123456',
                'birth_date' => '2000-01-15',
                'gender' => \App\Enums\ParticipantGender::Male,
                'provinsi' => 'Jawa Barat',
                'institusi' => 'Dojo Nusantara',
            ]),
            'lockedFields' => [],
            'canDelete' => true,
        ]))->name('edit');
        Route::put('/{id}', fn ($id) => redirect()->route('participants.index'))->name('update');
        Route::delete('/{id}', fn ($id) => redirect()->route('participants.index'))->name('destroy');
        Route::get('/{id}', fn ($id) => view('participants.show', [
            'participant' => \App\Models\Participant::factory()->make([
                'type' => \App\Enums\ParticipantType::Athlete,
                'name' => 'John Doe',
                'nik' => '1234567890123456',
                'birth_date' => '2000-01-15',
                'gender' => \App\Enums\ParticipantGender::Male,
                'provinsi' => 'Jawa Barat',
                'institusi' => 'Dojo Nusantara',
            ]),
        ]))->where('id', '[0-9]+')->name('show');
    });

require __DIR__ . '/auth.php';
