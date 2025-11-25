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
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with('doctor');

        if ($request->has('doctor_slug')) {
            $query->whereHas('doctor', function ($q) use ($request) {
                $q->where('slug', $request->doctor_slug);
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')->get();

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
        ]);
    }

    public function create(Request $request)
    {
        $doctor = null;

        if ($request->has('doctor')) {
            $doctor = Doctor::where('slug', $request->doctor)
                ->with('schedules') // ← IMPORTANTE
                ->first();
        }

        return Inertia::render('Appointments/Create', [
            'doctors' => Doctor::where('is_active', true)
                ->with('schedules') // ← IMPORTANTE
                ->get(),

            'selectedDoctor' => $doctor,
            'appointmentDate' => $request->start,
        ]);
    }


    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();

        // Validar que no haya conflicto de horario
        $appointmentDate = Carbon::parse($validated['appointment_date']);
        $duration = config('app.appointment_duration', 30);

        $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->whereIn('status', ['pendiente', 'confirmada'])
            ->where(function ($query) use ($appointmentDate, $duration) {
                $query->whereBetween('appointment_date', [
                    $appointmentDate,
                    $appointmentDate->copy()->addMinutes($duration)
                ])
                    ->orWhere(function ($q) use ($appointmentDate) {
                        $q->where('appointment_date', '<=', $appointmentDate)
                            ->whereRaw('DATE_ADD(appointment_date, INTERVAL duration_minutes MINUTE) > ?', [$appointmentDate]);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'appointment_date' => 'Este horario ya está ocupado. Por favor selecciona otro.'
            ]);
        }

        $validated['duration_minutes'] = $duration;
        $validated['status'] = 'pendiente';

        $appointment = Appointment::create($validated);
        $appointment->load('doctor');

        // Enviar correo
        Mail::to($appointment->patient_email)
            ->send(new AppointmentCreated($appointment));

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

        Mail::to($appointment->patient_email)
            ->send(new AppointmentConfirmed($appointment));

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

        Mail::to($appointment->patient_email)
            ->send(new AppointmentRejected($appointment));

        return back()->with('success', 'Cita rechazada y correo enviado al paciente');
    }
}
