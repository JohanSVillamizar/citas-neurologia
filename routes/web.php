<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PublicController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Ruta pública para la página de bienvenida con doctores y flags de login/register
Route::get('/', [PublicController::class, 'welcome'])->name('welcome');

Route::get('/medicos/{slug}', [PublicController::class, 'doctorProfile'])->name('public.doctors.show');

Route::get('/medicos/{slug}/disponibilidad', [PublicController::class, 'doctorAvailability']);

// Rutas públicas para solicitar cita
Route::get('/appointments/new', [AppointmentController::class, 'create'])->name('appointments.new');
Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');

// Rutas protegidas por autenticación Jetstream
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Panel y gestión interna
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('doctors', DoctorController::class);
    
        // Activar / desactivar médicos (reemplaza eliminación)
    Route::post('/doctors/{doctor}/toggle', [DoctorController::class, 'toggle'])
        ->name('doctors.toggle');

    Route::resource('appointments', AppointmentController::class)->except(['create', 'store']);
    Route::post('/appointments/{appointment}/accept', [AppointmentController::class, 'accept'])->name('appointments.accept');
    Route::post('/appointments/{appointment}/reject', [AppointmentController::class, 'reject'])->name('appointments.reject');
    Route::post('/appointments/{appointment}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');
});
