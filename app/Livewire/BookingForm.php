<?php

namespace App\Livewire;

use App\Exceptions\SlotUnavailableException;
use App\Models\Doctor;
use App\Models\Service;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Support\Phone;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Booking — Klinik Gigi Sehat')]
class BookingForm extends Component
{
    public ?int $serviceId = null;

    public ?int $doctorId = null;

    public ?string $date = null;

    public ?string $slot = null;

    public string $name = '';

    public string $phone = '';

    public ?string $notes = null;

    protected function rules(): array
    {
        return [
            'serviceId' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
            'doctorId' => ['required', Rule::exists('doctors', 'id')->where('is_active', true)],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'slot' => ['required', 'date_format:H:i'],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:'.Phone::VALIDATION_PATTERN],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'serviceId' => 'layanan',
            'doctorId' => 'dokter',
            'date' => 'tanggal',
            'slot' => 'jam',
            'name' => 'nama',
            'phone' => 'no. HP',
            'notes' => 'catatan',
        ];
    }

    public function updatedServiceId(): void
    {
        $this->reset('doctorId', 'date', 'slot');
    }

    public function updatedDoctorId(): void
    {
        $this->reset('date', 'slot');
    }

    public function updatedDate(): void
    {
        $this->reset('slot');
    }

    public function getDoctorsProperty(): Collection
    {
        if (! $this->serviceId) {
            return collect();
        }

        return Doctor::active()
            ->whereHas('services', fn ($query) => $query->where('services.id', $this->serviceId))
            ->orderBy('name')
            ->get();
    }

    public function getSlotsProperty(): Collection
    {
        if (! $this->serviceId || ! $this->doctorId || ! $this->date) {
            return collect();
        }

        $doctor = Doctor::find($this->doctorId);
        $service = Service::find($this->serviceId);

        if (! $doctor || ! $service || ! $doctor->services()->whereKey($this->serviceId)->exists()) {
            return collect();
        }

        try {
            $date = CarbonImmutable::parse($this->date);
        } catch (\Exception) {
            return collect();
        }

        return collect(app(AvailabilityService::class)->getAvailableSlots($doctor, $service, $date));
    }

    public function submit()
    {
        $this->validate();

        if (! $this->slots->contains(fn (array $s) => $s['start'] === $this->slot)) {
            $this->addError('slot', 'Maaf, jam yang dipilih sudah tidak tersedia. Silakan pilih jam lain.');
            $this->reset('slot');

            return;
        }

        try {
            $booking = app(BookingService::class)->create([
                'doctor_id' => $this->doctorId,
                'service_id' => $this->serviceId,
                'booking_date' => $this->date,
                'start_time' => $this->slot,
                'patient_name' => $this->name,
                'patient_phone' => $this->phone,
                'notes' => $this->notes,
            ]);
        } catch (SlotUnavailableException) {
            $this->addError('slot', 'Maaf, slot sudah diambil orang lain. Silakan pilih jam lain.');
            $this->reset('slot');

            return;
        }

        return $this->redirect(route('booking.success', ['code' => $booking->code]), navigate: true);
    }

    public function render()
    {
        return view('livewire.booking-form');
    }
}
