<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Services\BookingService;
use App\Support\Phone;
use Carbon\CarbonImmutable;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Aturan spec: nomor HP selalu tersimpan kanonik 62...
        $data['patient_phone'] = Phone::normalize(preg_replace('/[^\d+]/', '', (string) $data['patient_phone']));

        // Form admin tidak memuat kolom kode — generate di sini.
        $data['code'] = BookingService::generateCode(CarbonImmutable::parse($data['booking_date']));

        return $data;
    }
}
