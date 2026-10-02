<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use App\Models\DoctorSchedule;
use App\Models\Service;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AvailabilityService $availability;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availability = new AvailabilityService();
    }

    protected function buatDokterDenganJadwal(int $dayOfWeek, string $start = '09:00', string $end = '17:00'): Doctor
    {
        $doctor = Doctor::factory()->create();

        DoctorSchedule::factory()->create([
            'doctor_id' => $doctor->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => $start,
            'end_time' => $end,
        ]);

        return $doctor;
    }

    protected function layanan(int $durasi = 30): Service
    {
        return Service::factory()->create(['duration_minutes' => $durasi]);
    }

    protected function tanggalSeninDepan(): CarbonImmutable
    {
        return CarbonImmutable::parse('next monday');
    }

    public function test_returns_slots_within_schedule_with_15_minute_interval(): void
    {
        $doctor = $this->buatDokterDenganJadwal(1, '09:00', '11:00');
        $service = $this->layanan(60);

        $slots = $this->availability->getAvailableSlots($doctor, $service, $this->tanggalSeninDepan());

        $this->assertSame([
            ['start' => '09:00', 'end' => '10:00'],
            ['start' => '09:15', 'end' => '10:15'],
            ['start' => '09:30', 'end' => '10:30'],
            ['start' => '09:45', 'end' => '10:45'],
            ['start' => '10:00', 'end' => '11:00'],
        ], $slots);
    }

    public function test_returns_no_slots_when_doctor_on_leave(): void
    {
        $doctor = $this->buatDokterDenganJadwal(1, '09:00', '11:00');
        $service = $this->layanan(60);
        $date = $this->tanggalSeninDepan();

        DoctorLeave::factory()->create([
            'doctor_id' => $doctor->id,
            'date' => $date->toDateString(),
        ]);

        $this->assertSame([], $this->availability->getAvailableSlots($doctor, $service, $date));
    }

    public function test_returns_no_slots_on_day_without_schedule(): void
    {
        $doctor = $this->buatDokterDenganJadwal(1, '09:00', '11:00'); // hanya Senin
        $service = $this->layanan(60);
        $minggu = CarbonImmutable::parse('next sunday');

        $this->assertSame([], $this->availability->getAvailableSlots($doctor, $service, $minggu));
    }

    public function test_rejects_past_dates(): void
    {
        $doctor = $this->buatDokterDenganJadwal(1, '09:00', '11:00');
        $service = $this->layanan(60);
        $kemarin = $this->tanggalSeninDepan()->subDays(7);

        $this->assertSame([], $this->availability->getAvailableSlots($doctor, $service, $kemarin));
    }

    public function test_today_only_returns_future_slots(): void
    {
        CarbonImmutable::setTestNow(today()->setTime(15, 0));

        $doctor = $this->buatDokterDenganJadwal(today()->dayOfWeek, '09:00', '17:00');
        $service = $this->layanan(30);

        $slots = $this->availability->getAvailableSlots($doctor, $service, CarbonImmutable::today());

        $starts = array_column($slots, 'start');

        $this->assertNotEmpty($slots);
        $this->assertSame('15:00', $starts[0]);
        $this->assertSame('16:30', $starts[count($starts) - 1]);
        $this->assertNotContains('14:45', $starts);

        CarbonImmutable::setTestNow();
    }

    public function test_slot_length_follows_service_duration(): void
    {
        $doctor = $this->buatDokterDenganJadwal(1, '09:00', '11:00');
        $ringan = $this->layanan(30);
        $berat = $this->layanan(60);

        $slotRingan = $this->availability->getAvailableSlots($doctor, $ringan, $this->tanggalSeninDepan());
        $slotBerat = $this->availability->getAvailableSlots($doctor, $berat, $this->tanggalSeninDepan());

        $this->assertCount(7, $slotRingan);   // 09:00 .. 10:30
        $this->assertCount(5, $slotBerat);    // 09:00 .. 10:00
        $this->assertSame(['start' => '09:15', 'end' => '09:45'], $slotRingan[1]);
        $this->assertSame(['start' => '09:15', 'end' => '10:15'], $slotBerat[1]);
    }

    public function test_excludes_slots_overlapping_active_booking(): void
    {
        $doctor = $this->buatDokterDenganJadwal(1, '09:00', '11:00');
        $service = $this->layanan(60);
        $date = $this->tanggalSeninDepan();

        Booking::factory()->create([
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => BookingStatus::Pending,
        ]);

        $slots = $this->availability->getAvailableSlots($doctor, $service, $date);

        $this->assertSame([['start' => '10:00', 'end' => '11:00']], $slots);
    }

    public function test_cancelled_booking_does_not_block_slot(): void
    {
        $doctor = $this->buatDokterDenganJadwal(1, '09:00', '11:00');
        $service = $this->layanan(60);
        $date = $this->tanggalSeninDepan();

        Booking::factory()->create([
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'booking_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => BookingStatus::Cancelled,
        ]);

        $slots = $this->availability->getAvailableSlots($doctor, $service, $date);

        $this->assertCount(5, $slots);
    }
}
