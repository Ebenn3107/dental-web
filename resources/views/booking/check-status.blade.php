@extends('layouts.public')

@section('title', 'Cek Status Booking — Klinik Gigi Sehat')

@section('content')
    <div class="mx-auto max-w-lg py-6">
        <h1 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">
            Di mana booking saya?
        </h1>
        <p class="mt-3 text-ink-soft">
            Masukkan kode booking dan nomor HP Anda. Lupa kode? Biarkan kosong —
            kami carikan dari nomor HP.
        </p>

        <form method="GET" action="{{ route('check-status') }}"
              class="mt-8 rounded-2xl border border-rule bg-paper p-6 sm:p-7">
            <div class="grid gap-4">
                <div>
                    <label for="code" class="mb-1 block text-sm font-medium text-ink">
                        Kode Booking <span class="font-normal text-ink-soft">(opsional)</span>
                    </label>
                    <input type="text" id="code" name="code" value="{{ old('code', request('code')) }}"
                           placeholder="KLG-260101-AB12"
                           class="w-full rounded-xl border-rule font-mono uppercase text-ink placeholder:text-ink-soft focus:border-accent">
                    @error('code') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-ink">No. HP / WhatsApp</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', request('phone')) }}"
                           placeholder="08xxxxxxxxxx" required
                           class="w-full rounded-xl border-rule text-ink placeholder:text-ink-soft focus:border-accent">
                    @error('phone') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                        class="whitespace-nowrap rounded-full bg-accent px-8 py-3.5 font-medium text-white transition-colors hover:bg-accent-deep">
                    Cek Status
                </button>
            </div>
        </form>

        @if (isset($searched) && $searched)
            @php
                $warnaStatus = [
                    'pending' => 'bg-accent/10 text-accent-deep',
                    'confirmed' => 'bg-accent/10 text-accent-deep',
                    'completed' => 'bg-wa/10 text-wa',
                    'cancelled' => 'bg-danger-soft text-danger',
                    'no_show' => 'bg-mist text-ink-soft',
                ];
            @endphp

            @if ($booking)
                <div class="lift mt-6 rounded-2xl border border-rule bg-paper p-6 sm:p-7">
                    <div class="flex items-center justify-between gap-4">
                        <p class="font-mono text-xl font-medium tracking-wider text-accent-deep">{{ $booking->code }}</p>
                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $warnaStatus[$booking->status->value] }}">
                            {{ $booking->status->getLabel() }}
                        </span>
                    </div>

                    <dl class="mt-4 space-y-2 text-sm">
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
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-soft">Jam</dt>
                            <dd class="font-medium text-ink tabular">
                                {{ \Carbon\Carbon::parse($booking->start_time)->format('H.i') }} -
                                {{ \Carbon\Carbon::parse($booking->end_time)->format('H.i') }} WIB
                            </dd>
                        </div>
                    </dl>

                    <a href="{{ $booking->waLink() }}" target="_blank" rel="noopener"
                       class="mt-5 flex w-full items-center justify-center whitespace-nowrap rounded-full bg-wa px-6 py-3 font-medium text-white transition-opacity hover:opacity-90">
                        Konfirmasi via WhatsApp
                    </a>
                </div>
            @elseif ($bookings !== null && $bookings->isNotEmpty())
                <p class="mt-6 font-display text-lg font-bold tracking-tight text-ink">
                    Ditemukan {{ $bookings->count() }} booking atas nomor ini:
                </p>
                @foreach ($bookings as $item)
                    <article class="lift mt-3 rounded-2xl border border-rule bg-paper p-5">
                        <div class="flex items-center justify-between gap-4">
                            <p class="font-mono text-lg font-medium tracking-wider text-accent-deep">{{ $item->code }}</p>
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $warnaStatus[$item->status->value] }}">
                                {{ $item->status->getLabel() }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-ink-soft">
                            {{ $item->maskedName() }} · {{ $item->service->name }}
                        </p>
                        <p class="mt-1 text-sm text-ink tabular">
                            {{ $item->booking_date->translatedFormat('l, d F Y') }} ·
                            {{ \Carbon\Carbon::parse($item->start_time)->format('H.i') }} WIB
                        </p>
                    </article>
                @endforeach
                <p class="mt-3 text-sm text-ink-soft">
                    Salin salah satu kode di atas, lalu cari bersama nomor HP untuk melihat detail lengkap.
                </p>
            @else
                <div class="mt-6 rounded-2xl border border-rule bg-mist p-6 text-center">
                    <p class="font-display font-bold text-ink">Booking tidak ditemukan.</p>
                    <p class="mt-1 text-sm text-ink-soft">
                        Periksa kembali nomor HP — pastikan sama dengan yang Anda gunakan saat booking.
                    </p>
                </div>
            @endif
        @endif
    </div>
@endsection
