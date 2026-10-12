<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\IncidentTypeController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportAttachmentController;
use App\Http\Controllers\ReportCategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportPdfController;
use App\Http\Controllers\ReportVerificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TemporaryUploadController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VillageController;
use App\Models\ReportCategory;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationsController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationsController::class, 'markAsRead'])->name('notifications.read');

    Route::get('settings', [SettingsController::class, 'edit'])->middleware('permission:manage master data')->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->middleware('permission:manage master data')->name('settings.update');

    Route::post('uploads/temporary', [TemporaryUploadController::class, 'store'])->middleware('throttle:30,1')->name('uploads.temporary.store');
    Route::delete('uploads/temporary/{temporaryUpload}', [TemporaryUploadController::class, 'destroy'])->name('uploads.temporary.destroy');

    Route::resource('users', UserManagementController::class)
        ->except('show')
        ->middleware('permission:manage users');
    Route::resource('roles', RoleController::class)
        ->except('show')
        ->middleware('permission:manage roles');
    Route::resource('permissions', PermissionController::class)
        ->except('show')
        ->middleware('permission:manage permissions');
    Route::resource('report-categories', ReportCategoryController::class)
        ->except('show')
        ->middleware('permission:manage master data');
    Route::resource('incident-types', IncidentTypeController::class)
        ->except('show')
        ->middleware('permission:manage master data');
    Route::resource('districts', DistrictController::class)->except('show')->middleware('permission:manage master data');
    Route::resource('villages', VillageController::class)->except('show')->middleware('permission:manage master data');

    Route::resource('reports', ReportController::class)->only(['create', 'store', 'edit', 'update'])->middleware('permission:create reports');
    Route::resource('reports', ReportController::class)->only(['index', 'show']);
    Route::get('reports/{report}/attachments/{attachment}', [ReportAttachmentController::class, 'show'])->name('reports.attachments.show');
    Route::get('reports/{report}/pdf/bupati', [ReportPdfController::class, 'bupati'])->middleware('permission:export reports')->name('reports.pdf.bupati');
    Route::get('reports/{report}/pdf/notice', [ReportPdfController::class, 'notice'])->name('reports.pdf.notice');
    Route::get('verifikasi', [ReportVerificationController::class, 'index'])->middleware('permission:verify reports')->name('reports.verify.index');
    Route::get('reports/{report}/verifikasi', [ReportVerificationController::class, 'show'])->middleware('permission:verify reports')->name('reports.verify.show');
    Route::get('report-categories/{reportCategory}/incident-types', fn (ReportCategory $reportCategory) => $reportCategory->incidentTypes()->where('is_active', true)->orderBy('name')->get(['id', 'name']))->name('report-categories.incident-types');
    Route::post('reports/{report}/verify', [ReportController::class, 'verify'])->middleware('permission:verify reports')->name('reports.verify');
    Route::post('reports/{report}/clarify', [ReportController::class, 'clarify'])->middleware('permission:verify reports')->name('reports.clarify');
    Route::post('reports/{report}/reject', [ReportController::class, 'reject'])->middleware('permission:verify reports')->name('reports.reject');
    Route::post('reports/{report}/complete', [ReportController::class, 'complete'])->middleware('permission:verify reports')->name('reports.complete');
});
