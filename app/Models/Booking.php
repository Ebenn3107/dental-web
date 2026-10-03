<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'patient_name',
        'patient_phone',
        'doctor_id',
        'service_id',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date:Y-m-d',
            'status' => BookingStatus::class,
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', BookingStatus::Cancelled->value);
    }

    /**
     * Nama dengan sebagian karakter disamarkan — untuk daftar hasil pencarian
     * yang hanya memakai nomor HP (kode tidak perlu disamarkan: ia justru
     * yang dicari pemiliknya).
     */
    public function maskedName(): string
    {
        return collect(explode(' ', $this->patient_name))
            ->map(fn ($word) => \Illuminate\Support\Str::charAt($word, 0)
                .str_repeat('*', max(mb_strlen($word) - 1, 1)))
            ->implode(' ');
    }

    /**
     * Link WhatsApp untuk mengonfirmasi booking ke admin klinik.
     */
    public function waLink(): string
    {
        $message = sprintf(
            'Halo %s, saya %s. Saya sudah booking %s dengan %s pada %s pukul %s-%s WIB. Kode booking: %s. Terima kasih.',
            config('clinic.name'),
            $this->patient_name,
            $this->service->name,
            $this->doctor->name,
            $this->booking_date->translatedFormat('d/m/Y'),
            \Carbon\Carbon::parse($this->start_time)->format('H.i'),
            \Carbon\Carbon::parse($this->end_time)->format('H.i'),
            $this->code,
        );

        return 'https://wa.me/'.config('clinic.whatsapp_number').'?text='.rawurlencode($message);
    }
}
