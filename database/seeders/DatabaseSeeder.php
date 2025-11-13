<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario admin
        User::firstOrCreate(
            ['email' => 'admin@neurologia.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        $this->call([
            //DoctorSeeder::class,
            //DoctorScheduleSeeder::class,
            AppointmentSeeder::class
        ]);
    }
}
