<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'name' => 'Dr. ' . $name,
            'slug' => Str::slug('Dr. ' . $name),
            'specialty' => 'Neurología',
            'license_number' => 'MED-' . $this->faker->unique()->numberBetween(10000, 99999),
            'bio' => 'Especialista en ' . $this->faker->randomElement([
                'Epilepsia y trastornos convulsivos',
                'Enfermedades cerebrovasculares',
                'Esclerosis múltiple y enfermedades desmielinizantes',
                'Parkinson y trastornos del movimiento',
                'Alzheimer y deterioro cognitivo',
                'Cefaleas y migrañas',
                'Neuropatías periféricas'
            ]) . '. ' . $this->faker->numberBetween(10, 25) . ' años de experiencia.',
            'phone' => $this->faker->numerify('###-###-####'),
            'email' => $this->faker->unique()->safeEmail(),
            'is_active' => true,
        ];
    }
}
