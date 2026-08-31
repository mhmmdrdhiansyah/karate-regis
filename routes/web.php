<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KontingenManagementController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\EventFileController;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/events/{event}', [LandingController::class, 'show'])->name('events.show');
Route::get('/events/{event}/klasemen', [LandingController::class, 'klasemen'])->name('events.klasemen');
Route::post('/check-status', [LandingController::class, 'checkStatus'])->name('check-status');

// Sertifikat publik — lookup via NIK/Nama, PDF on-the-fly
Route::get('/sertifikat', [CertificateController::class, 'index'])->name('certificates.public.index');
Route::post('/sertifikat', [CertificateController::class, 'lookup'])->middleware('throttle:6,1')->name('certificates.public.lookup');
Route::post('/sertifikat/nama', [CertificateController::class, 'lookupByName'])
    ->middleware('throttle:6,1')
    ->name('certificates.public.lookupByName');
Route::get('/sertifikat/peserta/{participant}', [CertificateController::class, 'show'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('certificates.public.show');
Route::get('/sertifikat/{registration}/pdf', [CertificateController::class, 'pdf'])
    ->middleware('throttle:10,1')
    ->name('certificates.public.pdf');

Route::get('/api/wilayah/provinces', [WilayahController::class, 'provinces']);
Route::get('/api/wilayah/regencies/{provinceCode}', [WilayahController::class, 'regencies']);
Route::get('/api/sports/{sport}/perguruan', function (\App\Models\Sport $sport) {
    return response()->json([
        'data' => $sport->perguruan()->where('is_active', true)->orderBy('name')->get()
    ]);
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
        // Users - Hanya super-admin
        Route::middleware(['role:super-admin'])->group(function () {
            Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
            Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('users.forceDelete');
            Route::resource('users', UserController::class);
        });

        // Roles & Permissions - Hanya super-admin
        Route::middleware(['role:super-admin'])->group(function () {
            Route::resource('roles', RoleController::class);
            Route::resource('permissions', PermissionController::class);
        });
    });

    // Kontingen Management
    Route::middleware(['permission:view kontingen|create kontingen|edit kontingen'])->group(function () {
        Route::resource('kontingen', KontingenManagementController::class);
    });
    Route::middleware(['permission:delete kontingen'])->group(function () {
        Route::post('kontingen/{kontingen}/restore', [KontingenManagementController::class, 'restore'])->name('kontingen.restore');
        Route::delete('kontingen/{kontingen}/force-delete', [KontingenManagementController::class, 'forceDelete'])->name('kontingen.forceDelete');
    });

    // Peserta / Bank Peserta (Role Kontingen)
    Route::middleware(['permission:view participants|create participants|edit participants'])->group(function () {
        Route::resource('participants', ParticipantController::class)->except(['destroy']);
    });
    Route::middleware(['permission:delete participants'])->group(function () {
        Route::delete('participants/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');
        Route::get('participants/{participant}/delete-preview', [ParticipantController::class, 'deletePreview'])->name('participants.delete-preview');
    });

    Route::get('/api/check-nik', [ParticipantController::class, 'checkNik']);

    // Event Management — Protected by permission middleware (checked at controller level)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware(['permission:view events|create events|edit events|delete events'])->group(function () {
            Route::resource('events', EventController::class);
        });

        Route::middleware(['permission:transition events'])->group(function () {
            Route::patch('events/{event}/transition', [EventController::class, 'transition'])->name('events.transition');
            Route::put('events/{event}/panitia', [EventController::class, 'assignPanitia'])->name('events.panitia.assign');
        });

        Route::middleware(['permission:manage event files'])->group(function () {
            Route::post('events/{event}/files', [EventFileController::class, 'store'])->name('events.files.store');
            Route::delete('events/{event}/files/{eventFile}', [EventFileController::class, 'destroy'])->name('events.files.destroy');
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

        Route::middleware(['permission:verify documents'])->group(function () {
            Route::get('documents', [\App\Http\Controllers\Admin\DocumentVerificationController::class, 'index'])->name('documents.index');
            Route::post('documents/{participant}/approve', [\App\Http\Controllers\Admin\DocumentVerificationController::class, 'approve'])->name('documents.approve');
            Route::post('documents/{participant}/reject', [\App\Http\Controllers\Admin\DocumentVerificationController::class, 'reject'])->name('documents.reject');
            Route::post('documents/{participant}/revoke', [\App\Http\Controllers\Admin\DocumentVerificationController::class, 'revoke'])->name('documents.revoke');
        });

        Route::middleware(['permission:manage participants'])->group(function () {
            Route::get('participants', \App\Livewire\Admin\ParticipantManagement::class)->name('participants.index');
        });

        Route::get('results', [\App\Http\Controllers\Admin\ResultController::class, 'index'])
            ->name('results.index')
            ->middleware('permission:manage results');

        Route::get('events/{event}/results', \App\Livewire\Admin\ResultEntry::class)
            ->name('events.results.entry')
            ->middleware('permission:manage results');

        Route::get('events/{event}/certificate-templates', \App\Livewire\Admin\CertificateTemplateManagement::class)
            ->name('events.certificate-templates.index')
            ->middleware('permission:manage certificate templates');
    });

    Route::middleware(['permission:manage master data'])->group(function () {
        Route::get('master-data', \App\Livewire\Admin\MasterDataManagement::class)->name('master-data.index');
    });

    // Laporan Keuangan (Financial Reports)
    Route::get('reports', [ReportController::class, 'index'])
        ->middleware(['permission:view reports'])
        ->name('reports.index');
    Route::get('reports/financial/export', [ReportController::class, 'financialExport'])
        ->middleware(['permission:export reports'])
        ->name('reports.financial.export');
    Route::get('reports/financial/pdf', [ReportController::class, 'financialPdf'])
        ->middleware(['permission:export reports'])
        ->name('reports.financial.pdf');

    // Laporan Peserta
    Route::get('reports/participants', [ReportController::class, 'participants'])
        ->middleware(['permission:view reports'])
        ->name('reports.participants');
    Route::get('reports/participants/export', [ReportController::class, 'participantsExport'])
        ->middleware(['permission:export reports'])
        ->name('reports.participants.export');
    Route::get('reports/participants/pdf', [ReportController::class, 'participantsPdf'])
        ->middleware(['permission:export reports'])
        ->name('reports.participants.pdf');

    // Pendaftaran Event (User/Kontingen)
    Route::middleware(['permission:create registrations'])->group(function () {
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

        Route::get('registration/invoice/{event}/pdf', [\App\Http\Controllers\InvoiceController::class, 'pdf'])
            ->name('invoice.pdf');

        Route::get('payments/{payment}/invoice', [\App\Http\Controllers\InvoiceController::class, 'downloadByPayment'])
            ->name('payments.invoice');

        Route::get('payments', \App\Livewire\PaymentList::class)
            ->name('payments.index');
    });
});

require __DIR__ . '/auth.php';
