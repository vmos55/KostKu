<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KostController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::put('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences');

    Route::resource('kosts', KostController::class);
    Route::resource('rooms', RoomController::class)->except('show');
    Route::resource('tenants', TenantController::class);

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
    Route::patch('/bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::patch('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::patch('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/excel', [ReportController::class, 'excel'])->name('reports.excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
});
