<?php

namespace Tests\Unit;

use App\Models\Doctor;
use App\Models\DoctorSchedule;

class AvailabilityOverlapTest extends AvailabilityServiceTest
{
    public function test_overlapping_schedule_rows_produce_merged_unique_sorted_slots(): void
    {
        $doctor = Doctor::factory()->create();

        // Dua baris jadwal yang saling tumpang tindih — admin boleh memasukkan ini
        DoctorSchedule::factory()->create([
            'doctor_id' => $doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
        ]);
        DoctorSchedule::factory()->create([
            'doctor_id' => $doctor->id,
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
        ]);

        $service = $this->layanan(60);
        $slots = $this->availability->getAvailableSlots($doctor, $service, $this->tanggalSeninDepan());
        $starts = array_column($slots, 'start');

        // Tanpa duplikat dan terurut naik (kontrak terdokumentasi)
        $this->assertSame($starts, array_values(array_unique($starts)));
        $this->assertSame(collect($starts)->sort()->values()->all(), $starts);

        // Jendela efektif 09:00-14:00 dengan slot 60 menit: (300-60)/15 + 1 = 17
        $this->assertCount(17, $slots);
    }
}
