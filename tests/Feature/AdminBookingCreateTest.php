<?php

namespace Tests\Feature;

use App\Filament\Resources\BookingResource\Pages\CreateBooking;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminBookingCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_created_booking_stores_canonical_phone(): void
    {
        $this->actingAs(User::factory()->create());
        $doctor = Doctor::factory()->create();
        $service = Service::factory()->create();

        Livewire::test(CreateBooking::class)
            ->fillForm([
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'patient_name' => 'Walk-in Pasien',
                'patient_phone' => '081234567890',
                'booking_date' => today()->addWeek()->toDateString(),
                'start_time' => '09:00',
                'end_time' => '09:30',
                'status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Aturan spec: nomor HP selalu tersimpan kanonik 62...
        $this->assertDatabaseHas('bookings', [
            'patient_name' => 'Walk-in Pasien',
            'patient_phone' => '6281234567890',
        ]);
    }
}
