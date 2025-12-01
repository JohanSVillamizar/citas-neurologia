<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PublicController;
use Illuminate\Foundation\Application;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
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

    
    Route::get('/calendar', function () {
        // 1. Obtener el slug del doctor de los parámetros de la URL
        $slug = request('doctor');
        $selectedDoctor = null;
        $doctors = Doctor::all(); // O solo los necesarios

        if ($slug) {
            // 2. Buscar el doctor por slug
            $selectedDoctor = Doctor::where('slug', $slug)->first();
        }

        // 3. Renderizar el componente Vue/Inertia
        return Inertia::render('Path/To/Your/AppointmentSchedulerComponent', [
            'selectedDoctor' => $selectedDoctor ? [
                'id' => $selectedDoctor->id,
                'slug' => $selectedDoctor->slug,
                'name' => $selectedDoctor->name,
            ] : null,
            'doctors' => $doctors->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'slug' => $doctor->slug,
                    'name' => $doctor->name,
                ];
            })->toArray(),
        ]);
    })->name('appointment.calendar');
});
Route::post('/appointments', function (Request $request) {
    // 1. Validar los datos
    $validated = $request->validate([
        'doctor_id' => 'required|exists:doctors,id',
        'patient_name' => 'required|string|max:255',
        'patient_email' => 'required|email|max:255',
        'patient_phone' => 'required|string|max:20',
        'patient_id_number' => 'required|string|max:50|unique:appointments,patient_id_number',
        'patient_birth_date' => 'required|date|before:today',
        'reason' => 'nullable|string|max:1000',
        'appointment_date' => 'required|date_format:Y-m-d H:i:s|unique:appointments,appointment_date', // Validar que la hora no esté ya tomada
    ]);

    // 2. Crear la cita
    Appointment::create($validated);

    // 3. Redireccionar o responder con éxito
    return redirect()->route('home')->with('success', 'Cita agendada con éxito.');
})->name('appointments.store');
