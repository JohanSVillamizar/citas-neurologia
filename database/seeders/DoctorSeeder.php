<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        // Doctor::factory()->count(3)->create();

        // Podemos usar el seeder con datos generados por el factory como arriba,
        //o datos estáticos como el siguiente ejemplo:

        $doctors = [
            [
                'name' => 'Dr. Carlos Ramírez',
                'slug' => 'dr-carlos-ramirez',
                'specialty' => 'Neurología',
                'license_number' => 'MED-12345',
                'bio' => 'Especialista en epilepsia y trastornos convulsivos. 15 años de experiencia en diagnóstico y tratamiento de enfermedades neurológicas.',
                'phone' => '320-555-0101',
                'email' => 'carlos.ramirez@neurologia.com',
                'is_active' => true,
            ],
            [
                'name' => 'Dra. María González',
                'slug' => 'dra-maria-gonzalez',
                'specialty' => 'Neurología',
                'license_number' => 'MED-23456',
                'bio' => 'Experta en Parkinson y trastornos del movimiento. 12 años dedicados a mejorar la calidad de vida de pacientes con enfermedades neurodegenerativas.',
                'phone' => '315-555-0202',
                'email' => 'maria.gonzalez@neurologia.com',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Jorge Mendoza',
                'slug' => 'dr-jorge-mendoza',
                'specialty' => 'Neurología',
                'license_number' => 'MED-34567',
                'bio' => 'Especialista en cefaleas y migrañas. 10 años de experiencia en el manejo del dolor neurológico crónico.',
                'phone' => '318-555-0303',
                'email' => 'jorge.mendoza@neurologia.com',
                'is_active' => true,
            ],
        ];

        foreach ($doctors as $doctorData) {
            Doctor::firstOrCreate(
                ['slug' => Str::slug($doctorData['name'])],
                array_merge($doctorData, ['slug' => Str::slug($doctorData['name'])])
            );
        }
    }
}
