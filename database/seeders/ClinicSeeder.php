<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        $services = collect([
            ['name' => 'Pemeriksaan & Konsultasi', 'duration_minutes' => 30, 'price' => 50000],
            ['name' => 'Scaling (Pembersihan Karang Gigi)', 'duration_minutes' => 45, 'price' => 150000],
            ['name' => 'Tambal Gigi', 'duration_minutes' => 60, 'price' => 250000],
            ['name' => 'Cabut Gigi', 'duration_minutes' => 60, 'price' => 300000],
            ['name' => 'Bleaching Gigi', 'duration_minutes' => 60, 'price' => 500000],
        ])->map(fn (array $s) => Service::create($s + ['is_active' => true]));

        $doctors = collect([
            ['name' => 'dr. Andi Pratama', 'specialization' => 'Ortodonti'],
            ['name' => 'drg. Sari Wulandari', 'specialization' => 'Perawatan Gigi'],
            ['name' => 'drg. Budi Santoso', 'specialization' => 'Bedah Mulut'],
        ])->map(fn (array $d) => Doctor::create($d + ['is_active' => true]));

        // Senin-Jumat 09:00-16:00, Sabtu 09:00-13:00
        foreach ($doctors as $doctor) {
            for ($day = 1; $day <= 5; $day++) {
                $doctor->schedules()->create([
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '16:00:00',
                ]);
            }
            $doctor->schedules()->create([
                'day_of_week' => 6,
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
            ]);

            $doctor->services()->attach($services->pluck('id'));
        }
    }
}
