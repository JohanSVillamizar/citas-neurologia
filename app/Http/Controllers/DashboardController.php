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

        // Citas pendientes
        $pendingAppointments = Appointment::where('status', 'pendiente')
            ->with('doctor')
            ->select('id', 'slug', 'doctor_id', 'patient_name', 'appointment_date')
            ->orderBy('appointment_date', 'asc')
            ->get();

        // Citas confirmadas
        $confirmedAppointments = Appointment::where('status', 'confirmada')
            ->with('doctor')
            ->select('id', 'slug', 'doctor_id', 'patient_name', 'appointment_date')
            ->orderBy('appointment_date', 'asc')
            ->get();

        // Citas rechazadas
        $rejectedAppointments = Appointment::where('status', 'rechazada')
            ->with('doctor')
            ->select('id', 'slug', 'doctor_id', 'patient_name', 'appointment_date')
            ->orderBy('appointment_date', 'asc')
            ->get();

        // Citas completadas
        $completedAppointments = Appointment::where('status', 'completada')
            ->with('doctor')
            ->select('id', 'slug', 'doctor_id', 'patient_name', 'appointment_date')
            ->orderBy('appointment_date', 'asc')
            ->get();

        // TODAS las citas con el status traducido a inglés para el calendario
        $allAppointments = Appointment::with('doctor')
            ->whereIn('status', ['pendiente', 'confirmada', 'rechazada', 'completada'])
            ->select('id', 'slug', 'doctor_id', 'patient_name', 'appointment_date', 'status')
            ->orderBy('appointment_date', 'asc')
            ->get()
            ->map(function ($appointment) {
                // Traducir el status de español a inglés
                $statusMap = [
                    'pendiente' => 'pending',
                    'confirmada' => 'confirmed',
                    'rechazada' => 'rejected',
                    'completada' => 'completed',
                ];
                
                $appointment->status = $statusMap[$appointment->status] ?? $appointment->status;
                return $appointment;
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'doctors' => $doctors,
            'pendingAppointments' => $pendingAppointments,
            'confirmedAppointments' => $confirmedAppointments,
            'rejectedAppointments' => $rejectedAppointments,
            'completedAppointments' => $completedAppointments,
            'allAppointments' => $allAppointments, // <- IMPORTANTE: Agregar esto
        ]);
    }
}