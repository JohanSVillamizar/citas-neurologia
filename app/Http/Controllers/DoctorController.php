<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with('schedules')
            ->where('is_active', true)
            ->get();

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

        return redirect()->route('doctors.index')
            ->with('success', 'Médico creado exitosamente');
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
        return Inertia::render('Doctors/Edit', [
            'doctor' => $doctor,
        ]);
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        $doctor->update($request->validated());

        return redirect()->route('doctors.index')
            ->with('success', 'Médico actualizado exitosamente');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return redirect()->route('doctors.index')
            ->with('success', 'Médico eliminado exitosamente');
    }
}
