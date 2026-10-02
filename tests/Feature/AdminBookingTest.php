<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource\Pages\ListBookings;
use App\Filament\Widgets\BookingStatsWidget;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_bookings_resource(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/bookings')
            ->assertOk();
    }

    public function test_admin_can_confirm_pending_booking(): void
    {
        $this->actingAs(User::factory()->create());
        $booking = Booking::factory()->create(['status' => BookingStatus::Pending]);

        Livewire::test(ListBookings::class)
            ->callTableAction('confirm', $booking);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);
    }

    public function test_admin_can_complete_confirmed_booking(): void
    {
        $this->actingAs(User::factory()->create());
        $booking = Booking::factory()->create(['status' => BookingStatus::Confirmed]);

        Livewire::test(ListBookings::class)
            ->callTableAction('selesai', $booking);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'completed']);
    }

    public function test_admin_can_cancel_pending_booking(): void
    {
        $this->actingAs(User::factory()->create());
        $booking = Booking::factory()->create(['status' => BookingStatus::Pending]);

        Livewire::test(ListBookings::class)
            ->callTableAction('batalkan', $booking);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_admin_can_mark_confirmed_booking_as_no_show(): void
    {
        $this->actingAs(User::factory()->create());
        $booking = Booking::factory()->create(['status' => BookingStatus::Confirmed]);

        Livewire::test(ListBookings::class)
            ->callTableAction('tidak_hadir', $booking);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'no_show']);
    }

    public function test_stats_widget_counts_bookings_today(): void
    {
        Booking::factory()->create(['booking_date' => today()]);            // pending, hari ini
        Booking::factory()->create(['booking_date' => today()]);            // pending, hari ini
        Booking::factory()->create(['booking_date' => today()->addDay()]);  // besok — tidak dihitung

        Livewire::test(BookingStatsWidget::class)
            ->assertSuccessful()
            ->assertSeeText('Booking Hari Ini')
            ->assertSeeText('Menunggu Konfirmasi')
            ->assertSeeText('2');
    }

    public function test_policy_allows_admin_users_to_manage_bookings(): void
    {
        $policy = new \App\Policies\BookingPolicy();

        $this->assertTrue($policy->viewAny(User::factory()->create()));
        $this->assertTrue($policy->update(User::factory()->create(), Booking::factory()->create()));
    }
}
