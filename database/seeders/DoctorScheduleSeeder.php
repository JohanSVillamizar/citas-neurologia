<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\DoctorSchedule;

class DoctorScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //DoctorSchedule::factory()->count(15)->create();
        //Podemos usar el seeder con datos generados por el factory como arriba,
        //o datos estáticos como el siguiente ejemplo:

        $doctors = Doctor::all();

        foreach ($doctors as $doctor) {
            for ($day = 1; $day <= 5; $day++) {
                $morningExists = DoctorSchedule::where('doctor_id', $doctor->id)
                    ->where('day_of_week', $day)
                    ->where('start_time', '08:00:00')
                    ->exists();

                if (!$morningExists) {
                    DoctorSchedule::create([
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                        'start_time' => '08:00:00',
                        'end_time' => '12:00:00',
                        'is_active' => true,
                    ]);
                }

                $afternoonExists = DoctorSchedule::where('doctor_id', $doctor->id)
                    ->where('day_of_week', $day)
                    ->where('start_time', '14:00:00')
                    ->exists();

                if (!$afternoonExists) {
                    DoctorSchedule::create([
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                        'start_time' => '14:00:00',
                        'end_time' => '18:00:00',
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
