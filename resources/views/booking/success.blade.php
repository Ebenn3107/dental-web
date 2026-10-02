@extends('layouts.public')

@section('title', 'Booking Berhasil — Klinik Gigi Sehat')

@section('content')
    <div class="mx-auto max-w-xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-teal-100 text-3xl">
            ✅
        </div>
        <h1 class="mt-4 text-2xl font-bold text-slate-900 sm:text-3xl">Booking Berhasil Dibuat!</h1>
        <p class="mt-2 text-slate-500">
            Simpan kode booking di bawah ini untuk cek status kunjungan Anda.
        </p>

        <div class="mt-8 rounded-2xl border border-teal-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Kode Booking</p>
            <p class="mt-1 text-3xl font-mono font-bold tracking-wider text-teal-700">{{ $booking->code }}</p>

            <dl class="mt-6 space-y-2 text-left text-sm">
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Nama</dt>
                    <dd class="font-semibold text-slate-900">{{ $booking->patient_name }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Layanan</dt>
                    <dd class="font-semibold text-slate-900">{{ $booking->service->name }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Dokter</dt>
                    <dd class="font-semibold text-slate-900">{{ $booking->doctor->name }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Tanggal</dt>
                    <dd class="font-semibold text-slate-900">
                        {{ $booking->booking_date->translatedFormat('l, d F Y') }}
                    </dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Jam</dt>
                    <dd class="font-semibold text-slate-900">
                        {{ \Carbon\Carbon::parse($booking->start_time)->format('H.i') }} -
                        {{ \Carbon\Carbon::parse($booking->end_time)->format('H.i') }} WIB
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Status</dt>
                    <dd>
                        <span class="rounded-full bg-amber-100 px-3 py-0.5 text-xs font-semibold text-amber-700">
                            {{ $booking->status->getLabel() }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a href="{{ $booking->waLink() }}" target="_blank" rel="noopener"
               class="w-full rounded-full bg-green-600 px-6 py-3 font-semibold text-white shadow-md transition hover:bg-green-700 sm:w-auto">
                Konfirmasi via WhatsApp
            </a>
            <a href="{{ route('booking') }}"
               class="w-full rounded-full bg-teal-600 px-6 py-3 font-semibold text-white shadow-md transition hover:bg-teal-700 sm:w-auto">
                Booking Lagi
            </a>
            <a href="{{ route('check-status') }}"
               class="w-full rounded-full border border-teal-600 px-6 py-3 font-semibold text-teal-700 transition hover:bg-teal-50 sm:w-auto">
                Cek Status Booking
            </a>
        </div>
    </div>
@endsection
