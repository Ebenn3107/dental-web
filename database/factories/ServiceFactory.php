<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Pemeriksaan & Konsultasi',
            'duration_minutes' => 30,
            'price' => 50000,
            'is_active' => true,
        ];
    }
}
