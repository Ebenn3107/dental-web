@extends('layouts.public')

@section('title', 'Beranda — Klinik Gigi Sehat')

@section('content')
    <section class="rounded-3xl bg-gradient-to-br from-teal-600 to-teal-800 px-6 py-14 text-center text-white shadow-lg sm:px-12">
        <h1 class="mx-auto max-w-2xl text-3xl font-bold leading-tight sm:text-4xl">
            Senyum Sehat Dimulai dari Slot yang Tepat
        </h1>
        <p class="mx-auto mt-4 max-w-xl text-teal-100">
            Pilih layanan, dokter, dan jam praktik favoritmu. Booking online tanpa antre —
            cukup beberapa klik.
        </p>
        <a href="{{ route('booking') }}"
           class="mt-8 inline-block rounded-full bg-white px-8 py-3 text-base font-semibold text-teal-700 shadow-md transition hover:bg-teal-50">
            Booking Sekarang
        </a>
    </section>

    <section class="mt-12">
        <div class="flex items-end justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Layanan Kami</h2>
            <span class="text-sm text-slate-500">{{ $services->count() }} layanan aktif</span>
        </div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($services as $service)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-teal-300 hover:shadow-md">
                    <h3 class="font-semibold text-slate-900">{{ $service->name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">⏱ {{ $service->duration_minutes }} menit</p>
                    <p class="mt-3 font-bold text-teal-700">
                        {{ $service->price ? 'Rp '.number_format((float) $service->price, 0, ',', '.') : 'Hubungi kami' }}
                    </p>
                </div>
            @empty
                <p class="text-slate-500">Belum ada layanan yang tersedia.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-12">
        <div class="flex items-end justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Dokter Gigi Kami</h2>
            <span class="text-sm text-slate-500">{{ $doctors->count() }} dokter aktif</span>
        </div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($doctors as $doctor)
                <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-teal-300 hover:shadow-md">
                    @if ($doctor->photo)
                        <img src="{{ Storage::url($doctor->photo) }}" alt="{{ $doctor->name }}"
                             class="h-14 w-14 rounded-full object-cover">
                    @else
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-teal-100 text-lg font-bold text-teal-700">
                            {{ \Illuminate\Support\Str::of($doctor->name)->replace(['drg. ', 'dr. '], '')->explode(' ')->map(fn ($w) => \Illuminate\Support\Str::charAt($w, 0))->implode('') }}
                        </div>
                    @endif
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ $doctor->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $doctor->specialization }}</p>
                    </div>
                </div>
            @empty
                <p class="text-slate-500">Belum ada dokter yang tersedia.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-12 rounded-2xl border border-teal-200 bg-teal-50 p-8 text-center">
        <h2 class="text-xl font-bold text-slate-900">Sudah punya kode booking?</h2>
        <p class="mt-1 text-slate-600">Cek status kunjunganmu kapan saja.</p>
        <a href="{{ route('check-status') }}"
           class="mt-4 inline-block rounded-full border border-teal-600 px-6 py-2 font-semibold text-teal-700 transition hover:bg-teal-600 hover:text-white">
            Cek Status Booking
        </a>
    </section>
@endsection
