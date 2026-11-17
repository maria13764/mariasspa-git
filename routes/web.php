<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::middleware(['auth', 'can:book-appointments'])->group(function () {
    Route::get('/client', [AppointmentController::class, 'create'])
        ->name('client.appointments.create');
    Route::post('/client/appointments', [AppointmentController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('client.appointments.store');
});

Route::middleware(['auth', 'can:manage-therapist-appointments'])->group(function () {
    Route::get('/therapist', [AppointmentController::class, 'therapist'])
        ->name('therapist.appointments.index');
    Route::patch('/therapist/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
        ->middleware('throttle:30,1')
        ->name('therapist.appointments.update-status');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
