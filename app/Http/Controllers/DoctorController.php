<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class DoctorController extends Controller
{

    public function index(Request $request)
    {
        $doctors = Doctor::orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Doctors/Index', [
            'doctors' => $doctors,
        ]);
    }

    public function create()
    {
        return Inertia::render('Doctors/Create');
    }

    public function store(StoreDoctorRequest $request)
    {
        Doctor::create($request->validated());

        return redirect()
            ->route('doctors.index')
            ->with('success', 'Médico creado exitosamente.');
    }

    public function show(Doctor $doctor)
    {
        $doctor->load('schedules');

        return Inertia::render('Doctors/Show', [
            'doctor' => $doctor,
        ]);
    }

    public function edit(Doctor $doctor)
    {
        $doctor->load('schedules');

        return Inertia::render('Doctors/Edit', [
            'doctor' => $doctor,
        ]);
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        $doctor->update($request->validated());

        $schedules = $request->input('schedules', []);

        foreach ($schedules as $data) {
            // Normalizar datos
            $payload = [
                'day_of_week' => $data['day_of_week'],
                'start_time'  => $data['start_time'] ?: null,
                'end_time'    => $data['end_time'] ?: null,
                'is_active'   => !empty($data['is_active']),
            ];

            if (!empty($data['id'])) {
                // Si viene ID, actualiza ese registro
                $doctor->schedules()
                    ->where('id', $data['id'])
                    ->update($payload);
            } else {
                // Si no tiene ID, crea uno nuevo (por si en algún momento agregas nuevos días/turnos)
                $doctor->schedules()->create($payload);
            }
        }

        return redirect()
            ->route('doctors.index')
            ->with('success', 'Médico y horarios actualizado exitosamente.');
    }

    // ACTIVAR / DESACTIVAR - Reemplaza eliminación de médico y solo cambia su estado
    public function toggle(Doctor $doctor)
    {
        $doctor->is_active = !$doctor->is_active;
        $doctor->save();

        return redirect()
            ->route('doctors.index')
            ->with('success', 'Estado del médico actualizado.');
    }

    // ELIMINAR — Desactivado para mantener trazabilidad
    public function destroy(Doctor $doctor)
    {
        return back()->with(
            'error',
            'La eliminación está deshabilitada. Usa la opción de desactivar.'
        );
    }
}
