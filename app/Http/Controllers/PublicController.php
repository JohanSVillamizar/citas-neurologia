<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // Importar Route para usar Route::has()
use Inertia\Inertia;

class PublicController extends Controller
{
    public function welcome()
    {
        $doctors = Doctor::with('schedules')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Welcome', [
            'doctors' => $doctors,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function calendar(Request $request)
    {
        $doctor = null;

        if ($request->has('doctor')) {
            $doctor = Doctor::where('slug', $request->doctor)
                ->with('schedules')
                ->first();
        }

        return Inertia::render('Calendar', [
            'selectedDoctor' => $doctor,
            'doctors' => Doctor::where('is_active', true)->get(),
        ]);
    }
}
