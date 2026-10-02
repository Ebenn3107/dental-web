<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        return [
            Stat::make('Booking Hari Ini', Booking::whereDate('booking_date', today())->count())
                ->description('Total kunjungan hari ini')
                ->color('primary'),
            Stat::make('Menunggu Konfirmasi', Booking::where('status', BookingStatus::Pending)->count())
                ->description('Perlu ditindaklanjuti admin')
                ->color('warning'),
            Stat::make('Selesai Hari Ini', Booking::whereDate('booking_date', today())
                ->where('status', BookingStatus::Completed)
                ->count())
                ->description('Kunjungan hari ini yang sudah selesai')
                ->color('success'),
        ];
    }
}
