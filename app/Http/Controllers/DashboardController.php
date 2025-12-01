<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas generales
        $stats = [
            'pending' => Appointment::where('status', 'pendiente')->count(),
            'confirmed' => Appointment::where('status', 'confirmada')->count(),
            'completed' => Appointment::where('status', 'completada')->count(),
            'rejected' => Appointment::where('status', 'rechazada')->count(),
        ];

        // Doctores activos
        $doctors = Doctor::where('is_active', true)->get();

        // Citas pendientes (próximas)
        $pendingAppointments = Appointment::where('status', 'pendiente')
            ->with('doctor')
            ->select('id', 'slug', 'doctor_id', 'patient_name', 'appointment_date')
            ->orderBy('appointment_date', 'asc')
            ->get();

        // Citas confirmadas (próximas)
        $confirmedAppointments = Appointment::where('status', 'confirmada')
            ->with('doctor')
            ->select('id', 'slug', 'doctor_id', 'patient_name', 'appointment_date')
            ->orderBy('appointment_date', 'asc')
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'doctors' => $doctors,
            'pendingAppointments' => $pendingAppointments,
            'confirmedAppointments' => $confirmedAppointments,
        ]);
    }
}
