<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_booking_with_matching_code_and_phone(): void
    {
        $booking = Booking::factory()->create();

        $this->get(route('check-status', ['code' => $booking->code, 'phone' => '081234567890']))
            ->assertOk()
            ->assertSee($booking->code)
            ->assertSee($booking->patient_name);
    }

    public function test_matches_phone_in_different_format(): void
    {
        // Tersimpan kanonik 62..., user isi +62 dengan tanda hubung
        $booking = Booking::factory()->create(['patient_phone' => '6281234567890']);

        $this->get(route('check-status', ['code' => $booking->code, 'phone' => '+62 812-3456-7890']))
            ->assertOk()
            ->assertSee($booking->code);
    }

    public function test_hides_booking_with_wrong_phone(): void
    {
        $booking = Booking::factory()->create();

        $this->get(route('check-status', ['code' => $booking->code, 'phone' => '081999999999']))
            ->assertOk()
            ->assertSee('Booking tidak ditemukan')
            ->assertDontSee($booking->patient_name);
    }

    public function test_rejects_invalid_phone_format(): void
    {
        $booking = Booking::factory()->create();

        $this->get(route('check-status', ['code' => $booking->code, 'phone' => '12345']))
            ->assertOk()
            ->assertSee('format Indonesia');
    }

    public function test_phone_without_code_lists_bookings_with_masked_name(): void
    {
        // Skenario: user lupa kode — cukup nomor HP untuk menemukan kodenya lagi
        $booking = Booking::factory()->create([
            'patient_name' => 'Budi Pasien',
            'patient_phone' => '6281234567890',
        ]);

        $this->get(route('check-status', ['phone' => '081234567890']))
            ->assertOk()
            ->assertSee($booking->code)
            ->assertSee('B*** P*****')
            ->assertDontSee('Budi Pasien');
    }

    public function test_phone_without_code_unknown_number_shows_not_found(): void
    {
        $this->get(route('check-status', ['phone' => '081999999999']))
            ->assertOk()
            ->assertSee('Booking tidak ditemukan');
    }

    public function test_wa_link_builds_message_with_booking_details(): void
    {
        $booking = Booking::factory()->create(['patient_name' => 'Budi Pasien']);

        $link = $booking->waLink();

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $link);
        $this->assertStringContainsString(rawurlencode($booking->code), $link);
        $this->assertStringContainsString(rawurlencode('Budi Pasien'), $link);
    }
}
