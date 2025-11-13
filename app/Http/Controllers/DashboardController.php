<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with('doctor');

        if ($request->has('doctor_slug')) {
            $query->whereHas('doctor', function ($q) use ($request) {
                $q->where('slug', $request->doctor_slug);
            });
        }

        $pending = (clone $query)->where('status', 'pendiente')
            ->orderBy('appointment_date', 'asc')
            ->get();

        $confirmed = (clone $query)->where('status', 'confirmada')
            ->where('appointment_date', '>=', now())
            ->orderBy('appointment_date', 'asc')
            ->get();

        $doctors = Doctor::where('is_active', true)->get();

        return Inertia::render('Dashboard', [
            'pendingAppointments' => $pending,
            'confirmedAppointments' => $confirmed,
            'doctors' => $doctors,
        ]);
    }
}
