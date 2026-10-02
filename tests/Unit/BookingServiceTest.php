<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Service;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BookingService::class);
    }

    private function dokterBerjadwalSenin(string $start = '09:00', string $end = '17:00'): Doctor
    {
        $doctor = Doctor::factory()->create();

        DoctorSchedule::factory()->create([
            'doctor_id' => $doctor->id,
            'day_of_week' => 1,
            'start_time' => $start,
            'end_time' => $end,
        ]);

        return $doctor;
    }

    private function dataBooking(Doctor $doctor, Service $service, string $start, ?string $tanggal = null): array
    {
        return [
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'booking_date' => $tanggal ?? CarbonImmutable::parse('next monday')->toDateString(),
            'start_time' => $start,
            'patient_name' => 'Budi Pasien',
            'patient_phone' => '081234567890',
            'notes' => null,
        ];
    }

    public function test_creates_booking_with_end_time_code_and_pending_status(): void
    {
        $doctor = $this->dokterBerjadwalSenin();
        $service = Service::factory()->create(['duration_minutes' => 45]);

        $booking = $this->service->create($this->dataBooking($doctor, $service, '09:00'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'booking_date' => CarbonImmutable::parse('next monday')->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:45:00',
            'patient_phone' => '6281234567890',
            'status' => BookingStatus::Pending->value,
        ]);
        $this->assertMatchesRegularExpression('/^KLG-\d{6}-[A-Z0-9]{4}$/', $booking->code);
        $this->assertSame('Menunggu', $booking->status->getLabel());
    }

    public function test_rejects_double_booking_same_slot(): void
    {
        $doctor = $this->dokterBerjadwalSenin('09:00', '11:00');
        $service = Service::factory()->create(['duration_minutes' => 60]);

        $this->service->create($this->dataBooking($doctor, $service, '09:00'));

        $this->expectException(SlotUnavailableException::class);
        $this->service->create($this->dataBooking($doctor, $service, '09:15'));
    }

    public function test_double_booking_rejected_leaves_no_partial_data(): void
    {
        $doctor = $this->dokterBerjadwalSenin('09:00', '11:00');
        $service = Service::factory()->create(['duration_minutes' => 60]);

        $this->service->create($this->dataBooking($doctor, $service, '09:00'));

        try {
            $this->service->create($this->dataBooking($doctor, $service, '09:15'));
        } catch (SlotUnavailableException) {
            // diharapkan
        }

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_allows_adjacent_bookings(): void
    {
        $doctor = $this->dokterBerjadwalSenin('09:00', '11:00');
        $service = Service::factory()->create(['duration_minutes' => 60]);

        $pertama = $this->service->create($this->dataBooking($doctor, $service, '09:00'));
        $kedua = $this->service->create($this->dataBooking($doctor, $service, '10:00'));

        $this->assertDatabaseCount('bookings', 2);
        $this->assertSame('10:00:00', $kedua->start_time);
        $this->assertNotSame($pertama->id, $kedua->id);
    }

    public function test_other_doctor_slot_not_blocked(): void
    {
        $dokterA = $this->dokterBerjadwalSenin('09:00', '11:00');
        $dokterB = $this->dokterBerjadwalSenin('09:00', '11:00');
        $service = Service::factory()->create(['duration_minutes' => 60]);

        $this->service->create($this->dataBooking($dokterA, $service, '09:00'));
        $bookingB = $this->service->create($this->dataBooking($dokterB, $service, '09:00'));

        $this->assertDatabaseCount('bookings', 2);
        $this->assertSame($dokterB->id, $bookingB->doctor_id);
    }

    public function test_rejects_past_slot(): void
    {
        $doctor = $this->dokterBerjadwalSenin();
        $service = Service::factory()->create(['duration_minutes' => 30]);
        $seninLalu = CarbonImmutable::parse('next monday')->subDays(7)->toDateString();

        $this->expectException(SlotUnavailableException::class);
        $this->service->create($this->dataBooking($doctor, $service, '09:00', $seninLalu));
    }
}
