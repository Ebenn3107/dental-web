<?php

namespace App\Http\Controllers;

use App\Models\Booking;

class BookingSuccessController extends Controller
{
    public function __invoke(string $code)
    {
        $booking = Booking::where('code', $code)->firstOrFail();

        return view('booking.success', compact('booking'));
    }
}
