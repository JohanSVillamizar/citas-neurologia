<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Doctor;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/medicos/{slug}/disponibilidad', function (Request $request, $slug) {
    $doctor = Doctor::where('slug', $slug)->firstOrFail();
    $dateStr = $request->query('date'); // 'YYYY-MM-DD'

    // Determinar los días laborales del doctor
    // El componente espera que se devuelva 'working_days'
    $workingDays = $doctor->schedules->pluck('day_of_week')->toArray(); 

    // Calcular los slots del día (basado en la fecha y horarios del doctor)
    $slots = calculateAvailableSlots($doctor, $dateStr);

    return response()->json([
        'working_days' => $workingDays, // Solo necesario si se quiere cargar todo de una
        'slots' => $slots 
    ]);
})->name('doctor.availability');

function calculateAvailableSlots(Doctor $doctor, $dateStr) {
    // 1. Obtener la fecha
    $date = \Carbon\Carbon::parse($dateStr);
    $dayOfWeek = $date->dayOfWeek;

    // 2. Obtener el horario del doctor para ese día
    $schedule = $doctor->schedules->firstWhere('day_of_week', $dayOfWeek);
    
    if (!$schedule) {
        return []; // Doctor no trabaja este día
    }
    
    // 3. Generar todos los slots posibles (ej. de 30 min)
    $start = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->start_time);
    $end = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->end_time);
    $slotDuration = 30;

    $possibleSlots = [];
    $currentSlot = $start->copy();

    while ($currentSlot->lessThan($end)) {
        $possibleSlots[] = $currentSlot->format('H:i');
        $currentSlot->addMinutes($slotDuration);
    }

    // 4. Obtener las citas ya tomadas para este día
    $appointments = \App\Models\Appointment::where('doctor_id', $doctor->id)
        ->whereDate('appointment_date', $dateStr)
        ->get();

    $takenTimes = $appointments->map(fn ($app) => \Carbon\Carbon::parse($app->appointment_date)->format('H:i'))->toArray();

    // 5. Marcar los slots
    return array_map(function ($time) use ($takenTimes) {
        return [
            'time' => $time,
            'taken' => in_array($time, $takenTimes)
        ];
    }, $possibleSlots);
}