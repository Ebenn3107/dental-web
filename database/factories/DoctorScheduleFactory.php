<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'doctor_id' => \App\Models\Doctor::factory(),
            'day_of_week' => 1, // Senin
            'start_time' => '09:00:00',
            'end_time' => '16:00:00',
        ];
    }
}
