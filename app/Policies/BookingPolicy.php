<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

/**
 * Saat ini semua user adalah admin klinik (tidak ada registrasi publik),
 * jadi semua kemampuan diberikan. Tambahkan role di sini jika nanti ada
 * user dengan hak berbeda.
 */
class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Booking $booking): bool
    {
        return true;
    }

    public function delete(User $user, Booking $booking): bool
    {
        return true;
    }

    public function deleteAny(User $user): bool
    {
        return true;
    }

    public function restore(User $user, Booking $booking): bool
    {
        return true;
    }

    public function forceDelete(User $user, Booking $booking): bool
    {
        return true;
    }
}
