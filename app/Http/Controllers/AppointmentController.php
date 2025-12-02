<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentCreated;
use App\Mail\AppointmentRejected;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with('doctor');

        // Filtrar por status si viene en el request
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query
            ->orderBy('appointment_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
        ]);
    }

    public function create(Request $request)
    {
        $doctor = null;

        if ($request->has('doctor')) {
            $doctor = Doctor::where('slug', $request->doctor)
                ->with('schedules')
                ->first();
        }

        return Inertia::render('Appointments/Create', [
            'doctors' => Doctor::where('is_active', true)
                ->with('schedules')
                ->get(),
            'selectedDoctor' => $doctor,
            'appointmentDate' => $request->start,
        ]);

        $doctor = $request->has('doctor')
            ? Doctor::where('slug', $request->doctor)->with('schedules')->first()
            : null;

        return Inertia::render('Appointments/Create', [
            'selectedDoctor' => $doctor,
            'appointmentDate' => $request->start,
            'doctors' => Doctor::where('is_active', true)->get(),
            'defaultDurationMinutes' => (int) config('app.maintenance.appointment_duration', 30), 
        ]);
    }

    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();

        // 1. OBTENER LA DURACIÓN
        $duration = (int) config('app.maintenance.appointment_duration', 30); 

        // Validar que no haya conflicto de horario
        $appointmentDate = Carbon::parse($validated['appointment_date']);
        
        // Calcular el momento en que terminaría la cita
        $appointmentEnd = $appointmentDate->copy()->addMinutes($duration);

        $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->whereIn('status', ['pendiente', 'confirmada'])
            ->where(function ($query) use ($appointmentDate, $appointmentEnd) {
                // Caso 1: Una cita existente comienza durante la cita nueva
                $query->whereBetween('appointment_date', [$appointmentDate, $appointmentEnd])
                    // Caso 2: Una cita existente termina durante la cita nueva (usando duration_minutes)
                    ->orWhere(function ($q) use ($appointmentDate, $appointmentEnd) {
                        $q->whereRaw("appointment_date + (duration_minutes || ' minutes')::interval > ?", [$appointmentDate])
                        ->whereRaw("appointment_date + (duration_minutes || ' minutes')::interval <= ?", [$appointmentEnd]);
                    })
                    // Caso 3: La cita existente envuelve completamente a la cita nueva
                    ->orWhere(function ($q) use ($appointmentDate, $appointmentEnd) {
                        $q->where('appointment_date', '<', $appointmentDate)
                        ->whereRaw("appointment_date + (duration_minutes || ' minutes')::interval > ?", [$appointmentEnd]);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'appointment_date' => 'Este horario ya está ocupado. Por favor selecciona otro.'
            ]);
        }

        // 2. ASIGNAR LA DURACIÓN FIJA AL ARRAY DE DATOS A GUARDAR
        $validated['duration_minutes'] = $duration; 
        $validated['status'] = 'pendiente';
        
        // Crear la cita
        $appointment = Appointment::create($validated);
        $appointment->load('doctor');

        // Enviar correo con manejo de errores
        try {
            Mail::to($appointment->patient_email)->send(new AppointmentCreated($appointment));
            Log::info('Correo enviado exitosamente a: ' . $appointment->patient_email);
        } catch (\Exception $e) {
            Log::error('Error enviando correo de cita: ' . $e->getMessage());
        }

        return redirect()->route('welcome')
            ->with('success', 'Cita solicitada exitosamente. Recibirás un correo de confirmación.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load('doctor');

        return Inertia::render('Appointments/Show', [
            'appointment' => $appointment,
        ]);
    }

    public function edit(Appointment $appointment)
    {
        $appointment->load('doctor');

        return Inertia::render('Appointments/Edit', [
            'appointment' => $appointment,
            'doctors' => Doctor::where('is_active', true)->get(),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment->update($request->validated());

        return redirect()->route('appointments.index')
            ->with('success', 'Cita actualizada exitosamente');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Cita eliminada exitosamente');
    }

    public function accept(Appointment $appointment)
    {
        if ($appointment->status !== 'pendiente') {
            return back()->withErrors(['error' => 'Solo se pueden aceptar citas pendientes']);
        }

        $appointment->update(['status' => 'confirmada']);
        $appointment->load('doctor');

        try {
            Mail::to($appointment->patient_email)
                ->send(new AppointmentConfirmed($appointment));
            Log::info('Correo de confirmación enviado a: ' . $appointment->patient_email);
        } catch (\Exception $e) {
            Log::error('Error enviando correo de confirmación: ' . $e->getMessage());
        }

        return back()->with('success', 'Cita confirmada y correo enviado al paciente');
    }

    public function reject(Request $request, Appointment $appointment)
    {
        if ($appointment->status !== 'pendiente') {
            return back()->withErrors(['error' => 'Solo se pueden rechazar citas pendientes']);
        }

        $appointment->update([
            'status' => 'rechazada',
            'admin_notes' => $request->admin_notes,
        ]);
        $appointment->load('doctor');

        try {
            Mail::to($appointment->patient_email)
                ->send(new AppointmentRejected($appointment));
            Log::info('Correo de rechazo enviado a: ' . $appointment->patient_email);
        } catch (\Exception $e) {
            Log::error('Error enviando correo de rechazo: ' . $e->getMessage());
        }

        return back()->with('success', 'Cita rechazada y correo enviado al paciente');
    }

    public function complete(Appointment $appointment)
    {
        if ($appointment->status !== 'confirmada') {
            return back()->withErrors(['error' => 'Solo se pueden completar citas confirmadas']);
        }

        $appointment->update(['status' => 'completada']);

        return back()->with('success', 'Cita marcada como completada');
    }
}
