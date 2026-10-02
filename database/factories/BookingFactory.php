<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'KLG-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(2))),
            'patient_name' => 'Budi Pasien',
            'patient_phone' => '6281234567890',
            'doctor_id' => Doctor::factory(),
            'service_id' => Service::factory(),
            'booking_date' => today()->addDay(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => BookingStatus::Pending,
            'notes' => null,
        ];
    }
}
