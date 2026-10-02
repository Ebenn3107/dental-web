<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Doctor;
use App\Models\Service;
use Carbon\CarbonImmutable;

class AvailabilityService
{
    public const SLOT_INTERVAL_MINUTES = 15;

    /**
     * Slot kosong dokter pada suatu tanggal untuk durasi service tertentu.
     *
     * @return list<array{start: string, end: string}> format 'H:i', terurut naik
     */
    public function getAvailableSlots(Doctor $doctor, Service $service, CarbonImmutable $date): array
    {
        if ($date->startOfDay()->isBefore(today()->startOfDay())) {
            return [];
        }

        if ($doctor->leaves()->whereDate('date', $date->toDateString())->exists()) {
            return [];
        }

        $schedules = $doctor->schedules()
            ->where('day_of_week', $date->dayOfWeek)
            ->orderBy('start_time')
            ->get();

        if ($schedules->isEmpty()) {
            return [];
        }

        $busy = $doctor->bookings()
            ->whereDate('booking_date', $date->toDateString())
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->get(['start_time', 'end_time'])
            ->map(fn ($booking) => [
                self::toMinutes((string) $booking->start_time),
                self::toMinutes((string) $booking->end_time),
            ])
            ->all();

        $duration = $service->duration_minutes;
        $isToday = $date->isToday();
        $nowMinutes = (int) now()->format('H') * 60 + (int) now()->format('i');

        $slots = [];

        foreach ($schedules as $schedule) {
            $open = self::toMinutes((string) $schedule->start_time);
            $close = self::toMinutes((string) $schedule->end_time);

            for ($start = $open; $start + $duration <= $close; $start += self::SLOT_INTERVAL_MINUTES) {
                $end = $start + $duration;

                if ($isToday && $start < $nowMinutes) {
                    continue;
                }

                foreach ($busy as [$busyStart, $busyEnd]) {
                    if ($start < $busyEnd && $end > $busyStart) {
                        continue 2;
                    }
                }

                $slots[] = [
                    'start' => self::toClock($start),
                    'end' => self::toClock($end),
                ];
            }
        }

        return $slots;
    }

    public static function toMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);

        return ((int) $hours) * 60 + ((int) $minutes);
    }

    public static function toClock(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
