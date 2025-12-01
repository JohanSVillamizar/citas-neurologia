<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $path = Storage::disk('s3')->put('doctors', $photo);
            $validated['photo'] = Storage::disk('s3')->url($path);
        }

        Doctor::create($validated);

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
        $validated = $request->validated();

        if (!$request->hasFile('photo')) {
            unset($validated['photo']);
        }

        if ($request->hasFile('photo')) {
            if ($doctor->photo) {
                $baseUrl = rtrim(Storage::disk('s3')->url(''), '/');
                $relativePath = str_replace($baseUrl . '/', '', $doctor->photo);
                Storage::disk('s3')->delete($relativePath);
            }

            $path = Storage::disk('s3')->put('doctors', $request->file('photo'));
            $validated['photo'] = Storage::disk('s3')->url($path);
        }

        $doctor->update($validated);

        // ✅ Normalizar schedules que viene plano
        $rawSchedules = $request->input('schedules', []);
        $schedules = $this->normalizeSchedules($rawSchedules);

        // Aplicar cambios
        foreach ($schedules as $data) {
            if (!isset($data['day_of_week'])) {
                continue;
            }

            $payload = [
                'day_of_week' => $data['day_of_week'],
                'start_time'  => $data['start_time'] ?: null,
                'end_time'    => $data['end_time'] ?: null,
                'is_active'   => !empty($data['is_active']),
            ];

            if (!empty($data['id'])) {
                $doctor->schedules()
                    ->where('id', $data['id'])
                    ->update($payload);
            } else {
                $doctor->schedules()->create($payload);
            }
        }

        return redirect()
            ->route('doctors.index')
            ->with('success', 'Médico y horarios actualizado exitosamente.');
    }

    /**
     * Normaliza schedules que vienen planos desde FormData
     * Convierte: [{"id":"31"},{"day_of_week":"0"},{"start_time":"08:00"}, ...]
     * En: [{"id":31,"day_of_week":0,"start_time":"08:00","end_time":"12:00","is_active":1}, ...]
     */
    private function normalizeSchedules(array $rawSchedules): array
    {
        $normalized = [];
        $current = [
            'id'          => null,
            'day_of_week' => null,
            'start_time'  => null,
            'end_time'    => null,
            'is_active'   => false,
        ];

        foreach ($rawSchedules as $entry) {
            // Si encontramos un 'id' y ya tenemos un día_de_semana, guardamos y empezamos uno nuevo
            if (isset($entry['id']) && $current['day_of_week'] !== null) {
                $normalized[] = $current;
                $current = [
                    'id'          => null,
                    'day_of_week' => null,
                    'start_time'  => null,
                    'end_time'    => null,
                    'is_active'   => false,
                ];
            }

            // Poblar el item actual
            if (isset($entry['id'])) {
                $current['id'] = $entry['id'];
            }
            if (isset($entry['day_of_week'])) {
                $current['day_of_week'] = (int) $entry['day_of_week'];
            }
            if (isset($entry['start_time'])) {
                $current['start_time'] = $entry['start_time'];
            }
            if (isset($entry['end_time'])) {
                $current['end_time'] = $entry['end_time'];
            }
            if (isset($entry['is_active'])) {
                $current['is_active'] = (bool) $entry['is_active'];
            }
        }

        // Guardar el último item si tiene un día
        if ($current['day_of_week'] !== null) {
            $normalized[] = $current;
        }

        return $normalized;
    }

    public function toggle(Doctor $doctor)
    {
        $doctor->is_active = !$doctor->is_active;
        $doctor->save();

        return redirect()
            ->route('doctors.index')
            ->with('success', 'Estado del médico actualizado.');
    }

    public function destroy(Doctor $doctor)
    {
        return back()->with(
            'error',
            'La eliminación está deshabilitada. Usa la opción de desactivar.'
        );
    }
}
