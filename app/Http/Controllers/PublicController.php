<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // Importar Route para usar Route::has()
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Appointment;
use Illuminate\Support\Facades\Log;


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

            Log::info('=== DEBUG DISPONIBILIDAD ===');
            Log::info('Fecha: ' . $date);
            Log::info('Doctor: ' . $doctor->name . ' (ID: ' . $doctor->id . ')');

            if ($doctor->schedules->isEmpty()) {
                Log::warning("Doctor {$slug} no tiene horarios configurados");
                return response()->json(['slots' => []], 200);
            }

            $carbonDayOfWeek = Carbon::parse($date)->dayOfWeek;
            $dayOfWeek = $carbonDayOfWeek === 0 ? 7 : $carbonDayOfWeek;
            
            Log::info('Día Carbon: ' . $carbonDayOfWeek . ' -> Día BD: ' . $dayOfWeek . ' (' . Carbon::parse($date)->locale('es')->dayName . ')');
            
            // FIX: Usar json_encode para arrays
            Log::info('Todos los horarios del doctor: ' . json_encode($doctor->schedules->pluck('day_of_week')->toArray()));

            $schedules = $doctor->schedules->where('day_of_week', $dayOfWeek);

            Log::info('Horarios encontrados para día ' . $dayOfWeek . ': ' . $schedules->count());

            if ($schedules->isEmpty()) {
                Log::warning("No hay horarios para el día {$dayOfWeek}");
                return response()->json(['slots' => []], 200);
            }

            $duration = (int) config('app.appointment_duration', 30);

            // Generar todos los slots posibles
            $slots = [];
            foreach ($schedules as $schedule) {
                if (!$schedule->start_time || !$schedule->end_time) continue;

                $start = Carbon::parse($date . ' ' . $schedule->start_time);
                $end = Carbon::parse($date . ' ' . $schedule->end_time)->subMinutes($duration);

                Log::info('Generando slots de ' . $schedule->start_time . ' a ' . $schedule->end_time);

                for ($time = $start->copy(); $time->lte($end); $time->addMinutes($duration)) {
                    $slots[] = $time->format('H:i');
                }
            }

            Log::info('Total slots generados: ' . count($slots));
            Log::info('Slots: ' . json_encode($slots)); // FIX: json_encode

            // Horarios ocupados en BD
            $appointments = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $date)
                ->whereIn('status', ['pendiente', 'confirmada'])
                ->get();

            Log::info('Citas en BD para esta fecha: ' . $appointments->count());
            
            if ($appointments->count() > 0) {
                // FIX: json_encode para el array
                Log::info('Citas encontradas: ' . json_encode($appointments->map(function($apt) {
                    return [
                        'id' => $apt->id,
                        'fecha' => $apt->appointment_date,
                        'hora' => Carbon::parse($apt->appointment_date)->format('H:i'),
                        'status' => $apt->status
                    ];
                })->toArray()));
            }

            $taken = $appointments->map(fn($apt) => Carbon::parse($apt->appointment_date)->format('H:i'))->toArray();
            
            Log::info('Horarios marcados como ocupados: ' . json_encode($taken)); // FIX: json_encode

            // Transformar a formato del frontend
            $responseSlots = collect($slots)->map(fn($slot) => [
                'time' => $slot,
                'taken' => in_array($slot, $taken)
            ])->values();

            $takenCount = $responseSlots->filter(fn($s) => $s['taken'])->count();
            Log::info('Respuesta: ' . $responseSlots->count() . ' slots totales, ' . $takenCount . ' ocupados');
            Log::info('=== FIN DEBUG ===');

            return response()->json(['slots' => $responseSlots], 200);

        } catch (\Exception $e) {
            Log::error('ERROR en doctorAvailability: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }
}
