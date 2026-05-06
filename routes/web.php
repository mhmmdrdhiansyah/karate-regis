<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KontingenManagementController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Modules\AuthManagement\Controllers\UserController;
use App\Modules\AuthManagement\Controllers\RoleController;
use App\Modules\AuthManagement\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\SubCategoryController;

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

    // Event Management — Protected by permission middleware (checked at controller level)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware(['permission:view events|create events|edit events|delete events'])->group(function () {
            Route::resource('events', EventController::class);
            Route::patch('events/{event}/transition', [EventController::class, 'transition'])->name('events.transition');
        });

        Route::middleware(['permission:manage event categories'])->group(function () {
            Route::post('events/{event}/categories', [EventCategoryController::class, 'store'])->name('events.categories.store');
            Route::get('event-categories/{eventCategory}', [EventCategoryController::class, 'show'])->name('event-categories.show');
            Route::get('event-categories/{eventCategory}/edit', [EventCategoryController::class, 'edit'])->name('event-categories.edit');
            Route::put('event-categories/{eventCategory}', [EventCategoryController::class, 'update'])->name('event-categories.update');
            Route::delete('event-categories/{eventCategory}', [EventCategoryController::class, 'destroy'])->name('event-categories.destroy');
        });

        Route::middleware(['permission:manage sub-categories'])->group(function () {
            Route::post('event-categories/{eventCategory}/sub-categories', [SubCategoryController::class, 'store'])->name('event-categories.sub-categories.store');
            Route::get('sub-categories/{subCategory}/edit', [SubCategoryController::class, 'edit'])->name('sub-categories.edit');
            Route::put('sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
            Route::delete('sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.destroy');
        });

        Route::middleware(['permission:verify payments'])->group(function () {
            Route::get('payments', \App\Livewire\Admin\PaymentManagement::class)->name('payments.index');
        });
    });

    // Laporan (Reports) - simple index page
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // Pendaftaran Event (User/Kontingen)
    Route::middleware(['permission:create registrations', 'role:super-admin|panitia|kontingen'])->group(function () {
        Route::get('registration', function () {
            return view('registration.index');
        })->name('registration.index');

        // Placeholder for the next module
        Route::get('registration/create/{event}/{category}/{sub_category}', \App\Livewire\AthleteSelectionForm::class)
            ->name('registration.create');

        Route::get('registration/coaches', \App\Livewire\CoachSelectionForm::class)
            ->name('registration.coaches');

        Route::get('registration/invoice/{event}', \App\Livewire\EventRegistrationInvoice::class)
            ->name('registration.invoice');

        Route::get('payments', \App\Livewire\PaymentList::class)
            ->name('payments.index');
    });
});

require __DIR__ . '/auth.php';
