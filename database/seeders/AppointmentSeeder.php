<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $duration = config('app.appointment_duration');

        $doctors = Doctor::all();

        foreach ($doctors as $doctor) {
            for ($i = 0; $i < 5; $i++) { // 5 citas por doctor

                // Intentar hasta 10 veces encontrar un horario válido
                for ($attempt = 0; $attempt < 10; $attempt++) {
                    $appointmentDate = fake()->dateTimeBetween('now', '+30 days');

                    // Normalizar a intervalos de 30 minutos para simplificar
                    $hour = (int)$appointmentDate->format('H');
                    $minute = (int)$appointmentDate->format('i');

                    // Rechazar horarios no disponibles
                    if (
                        $hour < 8 || // antes de 8
                        ($hour >= 12 && $hour < 14) || // entre 12 y 14
                        $hour >= 18 // después de 18
                    ) {
                        continue; // saltar esta iteración y generar otro horario
                    }

                    // Ajustar minutos a 0 o 30
                    $appointmentDate->setTime($hour, $minute < 30 ? 0 : 30, 0);

                    $patientIdNumber = fake()->numerify('##########');
                    $patientName = fake()->name();
                    $patientEmail = fake()->safeEmail();
                    $patientPhone = fake()->numerify('###-###-####');

                    // Verificar conflictos

                    // 1. Chequear si doctor tiene cita en misma fecha y hora
                    $conflictDoctor = Appointment::where('doctor_id', $doctor->id)
                        ->where('appointment_date', $appointmentDate)
                        ->exists();

                    // 2. Chequear si paciente tiene cita a misma fecha y hora con cualquier doctor
                    $conflictPatient = Appointment::where('patient_id_number', $patientIdNumber)
                        ->where('appointment_date', $appointmentDate)
                        ->exists();

                    if (!$conflictDoctor && !$conflictPatient) {
                        // Guardar en BD
                        Appointment::create([
                            'slug' => Str::random(10),
                            'doctor_id' => $doctor->id,
                            'patient_name' => $patientName,
                            'patient_email' => $patientEmail,
                            'patient_phone' => $patientPhone,
                            'patient_id_number' => $patientIdNumber,
                            'patient_birth_date' => fake()->date('Y-m-d', '-20 years'),
                            'reason' => fake()->randomElement([
                                'Dolor de cabeza constante',
                                'Mareos frecuentes',
                                'Pérdida de memoria',
                                'Temblores en las manos',
                                'Dificultad para concentrarse',
                                'Problemas de sueño',
                                'Hormigueo en extremidades',
                            ]),
                            'appointment_date' => $appointmentDate,
                            'duration_minutes' => $duration,
                            'status' => 'pendiente',
                        ]);

                        break; // salir del intento
                    }
                }
            }
        }
    }
}