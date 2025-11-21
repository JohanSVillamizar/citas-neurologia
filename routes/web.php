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

// Ruta pública para el calendario de citas por doctor
Route::get('/calendar', [PublicController::class, 'calendar'])->name('calendar');

// Ver perfil de doctor (público)
Route::get('/doctors/{doctor}', [DoctorController::class, 'show'])->name('doctors.show');

// Rutas públicas para solicitar cita
Route::get('/appointments/new', [AppointmentController::class, 'create'])->name('appointments.new');
Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');

// Rutas protegidas por autenticación Jetstream
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard de usuario autenticado
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRUD para doctores (excepto show)
    Route::resource('doctors', DoctorController::class)->except(['show']);

    // CRUD para citas (excepto creación pública)
    Route::resource('appointments', AppointmentController::class)->except(['create', 'store']);

    // Rutas para aceptar o rechazar citas
    Route::post('/appointments/{appointment}/accept', [AppointmentController::class, 'accept'])->name('appointments.accept');
    Route::post('/appointments/{appointment}/reject', [AppointmentController::class, 'reject'])->name('appointments.reject');
});