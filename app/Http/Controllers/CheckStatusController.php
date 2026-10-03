<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckStatusRequest;
use App\Models\Booking;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckStatusController extends Controller
{
    public function __invoke(Request $request)
    {
        // Halaman pencarian GET bersifat stateless: error validasi
        // ditampilkan inline (bukan redirect), rules tetap di FormRequest.
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
            'phone' => preg_replace('/[^\d+]/', '', (string) $request->input('phone')),
        ]);

        $searched = $request->filled('code') || $request->filled('phone');
        $booking = null;
        $bookings = null;

        if ($request->filled('phone')) {
            $form = new CheckStatusRequest();

            $validator = Validator::make($request->all(), $form->rules(), $form->messages());

            if ($validator->fails()) {
                return view('booking.check-status', ['booking' => null, 'bookings' => null, 'searched' => true])
                    ->withErrors($validator);
            }

            $phone = Phone::normalize($request->input('phone'));

            if ($request->filled('code')) {
                // Pencarian presisi: kode + nomor HP → detail lengkap
                $booking = Booking::where('code', $request->input('code'))
                    ->where('patient_phone', $phone)
                    ->first();
            } else {
                // User lupa kode: daftar semua booking atas nomor ini,
                // nama disamarkan, kode bisa dipakai untuk detail lengkap
                $bookings = Booking::where('patient_phone', $phone)
                    ->orderByDesc('booking_date')
                    ->get();
            }
        }

        return view('booking.check-status', compact('booking', 'bookings', 'searched'));
    }
}
