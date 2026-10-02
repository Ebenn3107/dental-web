@extends('layouts.public')

@section('title', 'Booking Berhasil — Klinik Gigi Sehat')

@section('content')
    <div class="mx-auto max-w-lg py-6 text-center">
        <h1 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">
            Booking diterima — kunjungan Anda tercatat.
        </h1>
        <p class="mt-3 text-ink-soft">
            Simpan kode booking di bawah untuk cek status kapan saja.
        </p>

        <div class="lift mt-8 rounded-2xl border border-rule bg-paper p-7 text-left">
            <p class="text-xs font-medium uppercase tracking-widest text-ink-soft">Kode Booking</p>
            <p class="mt-1 font-mono text-3xl font-medium tracking-wider text-accent-deep">{{ $booking->code }}</p>

            <dl class="mt-6 space-y-2 text-sm">
                <div class="flex justify-between gap-4 border-b border-rule pb-2">
                    <dt class="text-ink-soft">Nama</dt>
                    <dd class="font-medium text-ink">{{ $booking->patient_name }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-rule pb-2">
                    <dt class="text-ink-soft">Layanan</dt>
                    <dd class="font-medium text-ink">{{ $booking->service->name }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-rule pb-2">
                    <dt class="text-ink-soft">Dokter</dt>
                    <dd class="font-medium text-ink">{{ $booking->doctor->name }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-rule pb-2">
                    <dt class="text-ink-soft">Tanggal</dt>
                    <dd class="font-medium text-ink">{{ $booking->booking_date->translatedFormat('l, d F Y') }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-rule pb-2">
                    <dt class="text-ink-soft">Jam</dt>
                    <dd class="font-medium text-ink tabular">
                        {{ \Carbon\Carbon::parse($booking->start_time)->format('H.i') }} -
                        {{ \Carbon\Carbon::parse($booking->end_time)->format('H.i') }} WIB
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-soft">Status</dt>
                    <dd>
                        <span class="rounded-full bg-accent/10 px-3 py-0.5 text-xs font-medium text-accent-deep">
                            {{ $booking->status->getLabel() }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a href="{{ $booking->waLink() }}" target="_blank" rel="noopener"
               class="w-full whitespace-nowrap rounded-full bg-wa px-7 py-3.5 font-medium text-white transition-opacity hover:opacity-90 sm:w-auto">
                Konfirmasi via WhatsApp
            </a>
            <a href="{{ route('booking') }}"
               class="w-full whitespace-nowrap rounded-full border border-rule px-7 py-3.5 font-medium text-ink transition-colors hover:border-accent sm:w-auto">
                Booking Lagi
            </a>
        </div>
    </div>
@endsection
