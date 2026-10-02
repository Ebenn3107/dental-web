<?php

namespace Tests\Feature;

use App\Livewire\BookingForm;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_active_services_and_doctors(): void
    {
        Service::factory()->create(['name' => 'Tambal Gigi']);
        Service::factory()->create(['name' => 'Radiologi Gigi', 'is_active' => false]);
        Doctor::factory()->create(['name' => 'drg. Sari Wulandari']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Tambal Gigi')
            ->assertSee('drg. Sari Wulandari')
            ->assertDontSee('Radiologi Gigi');
    }

    public function test_booking_flow_creates_booking(): void
    {
        $doctor = Doctor::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);
        DoctorSchedule::factory()->create([
            'doctor_id' => $doctor->id,
            'day_of_week' => CarbonImmutable::parse('next monday')->dayOfWeek,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
        ]);
        $doctor->services()->attach($service);

        Livewire::test(BookingForm::class)
            ->set('serviceId', $service->id)
            ->set('doctorId', $doctor->id)
            ->set('date', CarbonImmutable::parse('next monday')->toDateString())
            ->set('slot', '09:00')
            ->set('name', 'Budi Pasien')
            ->set('phone', '081234567890')
            ->call('submit')
            ->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'patient_name' => 'Budi Pasien',
            'patient_phone' => '6281234567890',
            'status' => 'pending',
        ]);
    }

    public function test_phone_must_be_indonesian_format(): void
    {
        $doctor = Doctor::factory()->create();
        $service = Service::factory()->create();
        DoctorSchedule::factory()->create([
            'doctor_id' => $doctor->id,
            'day_of_week' => CarbonImmutable::parse('next monday')->dayOfWeek,
        ]);
        $doctor->services()->attach($service);

        Livewire::test(BookingForm::class)
            ->set('serviceId', $service->id)
            ->set('doctorId', $doctor->id)
            ->set('date', CarbonImmutable::parse('next monday')->toDateString())
            ->set('slot', '09:00')
            ->set('name', 'Budi Pasien')
            ->set('phone', '12345')
            ->call('submit')
            ->assertHasErrors(['phone']);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_taken_slot_is_rejected_friendly(): void
    {
        $doctor = Doctor::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);
        DoctorSchedule::factory()->create([
            'doctor_id' => $doctor->id,
            'day_of_week' => CarbonImmutable::parse('next monday')->dayOfWeek,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
        ]);
        $doctor->services()->attach($service);
        Booking::factory()->create([
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'booking_date' => CarbonImmutable::parse('next monday')->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);

        Livewire::test(BookingForm::class)
            ->set('serviceId', $service->id)
            ->set('doctorId', $doctor->id)
            ->set('date', CarbonImmutable::parse('next monday')->toDateString())
            ->set('slot', '09:00')
            ->set('name', 'Budi Pasien')
            ->set('phone', '081234567890')
            ->call('submit')
            ->assertHasErrors(['slot']);

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_success_page_shows_booking_code(): void
    {
        $booking = Booking::factory()->create();

        $this->get("/booking/success/{$booking->code}")
            ->assertOk()
            ->assertSee($booking->code);
    }

    public function test_success_page_unknown_code_is_404(): void
    {
        $this->get('/booking/success/KLG-260101-ZZZZ')->assertNotFound();
    }
}
