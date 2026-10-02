@extends('layouts.public')

@section('title', 'Cek Status Booking — Klinik Gigi Sehat')

@section('content')
    <div class="mx-auto max-w-xl">
        <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Cek Status Booking</h1>
        <p class="mt-1 text-slate-500">Masukkan kode booking dan nomor HP yang Anda gunakan saat booking.</p>

        <form method="GET" action="{{ route('check-status') }}"
              class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4">
                <div>
                    <label for="code" class="mb-1 block text-sm font-medium text-slate-700">Kode Booking</label>
                    <input type="text" id="code" name="code" value="{{ old('code', request('code')) }}"
                           placeholder="KLG-260101-AB12" required
                           class="w-full rounded-lg border-slate-300 font-mono uppercase focus:border-teal-500 focus:ring-teal-500">
                    @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">No. HP / WhatsApp</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', request('phone')) }}"
                           placeholder="08xxxxxxxxxx" required
                           class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                        class="rounded-full bg-teal-600 px-8 py-3 font-semibold text-white shadow-md transition hover:bg-teal-700">
                    Cek Status
                </button>
            </div>
        </form>

        @if (isset($searched) && $searched)
            @if ($booking)
                <div class="mt-6 rounded-2xl border border-teal-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <p class="font-mono text-xl font-bold tracking-wider text-teal-700">{{ $booking->code }}</p>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold
                            {{ $booking->status->value === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $booking->status->value === 'confirmed' ? 'bg-sky-100 text-sky-700' : '' }}
                            {{ $booking->status->value === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $booking->status->value === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $booking->status->value === 'no_show' ? 'bg-slate-200 text-slate-600' : '' }}">
                            {{ $booking->status->getLabel() }}
                        </span>
                    </div>

                    <dl class="mt-4 space-y-2 text-sm">
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
                            <dd class="font-semibold text-slate-900">{{ $booking->booking_date->translatedFormat('l, d F Y') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Jam</dt>
                            <dd class="font-semibold text-slate-900">
                                {{ \Carbon\Carbon::parse($booking->start_time)->format('H.i') }} -
                                {{ \Carbon\Carbon::parse($booking->end_time)->format('H.i') }} WIB
                            </dd>
                        </div>
                    </dl>

                    <a href="{{ $booking->waLink() }}" target="_blank" rel="noopener"
                       class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-green-600 px-6 py-3 font-semibold text-white shadow-md transition hover:bg-green-700">
                        Konfirmasi via WhatsApp
                    </a>
                </div>
            @else
                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-6 text-center">
                    <p class="font-semibold text-red-700">Booking tidak ditemukan.</p>
                    <p class="mt-1 text-sm text-red-600">
                        Periksa kembali kode booking dan pastikan nomor HP sama dengan yang Anda gunakan saat booking.
                    </p>
                </div>
            @endif
        @endif
    </div>
@endsection
