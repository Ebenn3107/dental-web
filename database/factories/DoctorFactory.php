<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'drg. Sari Wulandari',
            'specialization' => 'Perawatan Gigi',
            'photo' => null,
            'is_active' => true,
        ];
    }
}
