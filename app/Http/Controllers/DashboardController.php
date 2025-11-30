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
        $query = Appointment::query();

        if ($request->has('doctor_slug')) {
            $query->whereHas('doctor', function ($q) use ($request) {
                $q->where('slug', $request->doctor_slug);
            });
        }

        $stats = [
            'pending'   => (clone $query)->where('status', 'pendiente')->count(),
            'confirmed' => (clone $query)->where('status', 'confirmada')->count(),
            'completed' => (clone $query)->where('status', 'completada')->count(),
            'rejected'  => (clone $query)->where('status', 'rechazada')->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'doctors' => Doctor::where('is_active', true)->get(),
        ]);
    }
}
