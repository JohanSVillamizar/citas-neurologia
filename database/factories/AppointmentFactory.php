<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => Str::random(10),
            'doctor_id' => Doctor::factory(),
            'patient_name' => $this->faker->name(),
            'patient_email' => $this->faker->safeEmail(),
            'patient_phone' => $this->faker->numerify('###-###-####'),
            'patient_id_number' => $this->faker->numerify('##########'),
            'patient_birth_date' => $this->faker->date('Y-m-d', '-20 years'),
            'reason' => $this->faker->randomElement([
                'Dolor de cabeza constante',
                'Mareos frecuentes',
                'Pérdida de memoria',
                'Temblores en las manos',
                'Dificultad para concentrarse',
                'Problemas de sueño',
                'Hormigueo en extremidades'
            ]),
            'appointment_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'duration_minutes' => config('app.appointment_duration', 30),
            'status' => $this->faker->randomElement(['pendiente', 'confirmada']),
        ];
    }
}
