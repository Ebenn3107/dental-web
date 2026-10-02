<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\Service;
use App\Support\Phone;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class BookingService
{
    public function __construct(private AvailabilityService $availability) {}

    /**
     * Buat booking dengan re-check ketersediaan di dalam transaction,
     * sehingga dua submit paralel tidak bisa merebut slot yang sama.
     *
     * @param array{doctor_id: int, service_id: int, booking_date: string, start_time: string, patient_name: string, patient_phone: string, notes?: ?string} $data
     */
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $doctor = Doctor::findOrFail($data['doctor_id']);
            $service = Service::findOrFail($data['service_id']);
            $date = CarbonImmutable::parse($data['booking_date']);

            // Kunci baris booking dokter ini pada tanggal ini (lockForUpdate
            // efektif di MySQL; SQLite mengunci seluruh database).
            $doctor->bookings()
                ->whereDate('booking_date', $date->toDateString())
                ->where('status', '!=', BookingStatus::Cancelled->value)
                ->lockForUpdate()
                ->get();

            $wanted = CarbonImmutable::parse($data['start_time'])->format('H:i');

            $slot = collect($this->availability->getAvailableSlots($doctor, $service, $date))
                ->first(fn (array $slot) => $slot['start'] === $wanted);

            if ($slot === null) {
                throw new SlotUnavailableException($wanted, $date->toDateString());
            }

            return Booking::create([
                'code' => $this->generateCode($date),
                'patient_name' => $data['patient_name'],
                'patient_phone' => Phone::normalize($data['patient_phone']),
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'booking_date' => $date->toDateString(),
                'start_time' => $slot['start'].':00',
                'end_time' => $slot['end'].':00',
                'status' => BookingStatus::Pending,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    private function generateCode(CarbonImmutable $date): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $code = 'KLG-'.$date->format('ymd').'-'.Str::upper(Str::random(4));

            if (!Booking::where('code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Gagal membuat kode booking unik, coba lagi.');
    }
}
