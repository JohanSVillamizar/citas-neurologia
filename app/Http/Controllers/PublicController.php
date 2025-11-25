<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // Importar Route para usar Route::has()
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Appointment;


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

    public function doctorProfile($slug)
    {
        $doctor = Doctor::where('slug', $slug)
            ->where('is_active', true)
            ->with('schedules')
            ->firstOrFail();

        return Inertia::render('Public/DoctorProfile', [
            'doctor' => $doctor,
        ]);
    }

    public function doctorAvailability(Request $request, $slug)
    {
        try {
            $date = $request->input('date');
            if (!$date) {
                return response()->json(['error' => 'Falta la fecha'], 400);
            }

            $doctor = Doctor::where('slug', $slug)->with('schedules')->firstOrFail();

            $dayOfWeek = Carbon::parse($date)->dayOfWeek;
            $schedules = $doctor->schedules->where('day_of_week', $dayOfWeek);
            $duration = (int) config('app.appointment_duration', 30);

            $slots = [];
            foreach ($schedules as $schedule) {
                if (!$schedule->start_time || !$schedule->end_time) continue;

                $start = Carbon::parse($date . ' ' . $schedule->start_time);
                $end = Carbon::parse($date . ' ' . $schedule->end_time)->subMinutes($duration);

                for ($time = $start->copy(); $time->lte($end); $time->addMinutes($duration)) {
                    $slots[] = $time->format('H:i');
                }
            }

            $taken = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $date)
                ->whereIn('status', ['pendiente', 'confirmada'])
                ->pluck('appointment_date')
                ->map(fn($dt) => Carbon::parse($dt)->format('H:i'))
                ->toArray();

            $response = collect($slots)->map(fn($hour) => [
                'time' => $hour,
                'taken' => in_array($hour, $taken)
            ])->values();

            return response()->json(['slots' => $response]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}
