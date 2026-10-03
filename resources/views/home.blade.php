@extends('layouts.public')

@section('title', 'Klinik Gigi Sehat — Booking tanpa antre')

@section('content')
    {{-- Hero: panel navy dengan kartu contoh booking mengambang --}}
    <section class="relative mt-6 overflow-hidden rounded-3xl bg-navy px-6 py-14 text-on-navy sm:px-10 sm:py-20 lg:px-16">
        <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-accent/30 blur-3xl" aria-hidden="true"></div>
        <div class="relative grid items-center gap-12 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <h1 class="font-display text-4xl font-bold leading-[1.05] tracking-tight sm:text-5xl lg:text-6xl">
                    Booking dokter gigi,
                    <span class="text-accent">tanpa antre.</span>
                </h1>
                <p class="mt-5 max-w-lg text-base leading-relaxed text-on-navy/75 sm:text-lg">
                    Pilih layanan, dokter, dan jam praktik yang cocok — slot real-time,
                    konfirmasi lewat WhatsApp, datang tepat jadwal.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('booking') }}"
                       class="whitespace-nowrap rounded-full bg-accent px-7 py-3.5 font-medium text-white transition-colors hover:bg-accent-deep">
                        Booking Sekarang
                    </a>
                    <a href="{{ route('check-status') }}"
                       class="whitespace-nowrap rounded-full border border-on-navy/30 px-7 py-3.5 font-medium text-on-navy transition-colors hover:border-on-navy/60">
                        Cek Status Booking
                    </a>
                </div>
            </div>

            {{-- Foto klinik + kartu contoh mengambang di atasnya --}}
            <div class="lg:col-span-5">
                <img src="{{ asset(config('clinic.images.hero')) }}"
                     alt="Dokter gigi memeriksa pasien di klinik"
                     fetchpriority="high"
                     class="h-72 w-full rounded-3xl object-cover sm:h-80 lg:h-[24rem]">
                <div class="relative z-10 mx-4 -mt-14 max-w-sm rounded-2xl bg-paper p-5 text-ink shadow-lift sm:mx-6 lg:ml-auto">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium uppercase tracking-widest text-ink-soft">Contoh booking</p>
                        <span class="rounded-full bg-accent/10 px-3 py-1 text-xs font-medium text-accent-deep">Dikonfirmasi</span>
                    </div>
                    <p class="mt-3 font-display text-lg font-bold tracking-tight">Scaling (Pembersihan Karang Gigi)</p>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-4 border-b border-rule pb-2">
                            <dt class="text-ink-soft">Dokter</dt>
                            <dd class="font-medium">drg. Sari Wulandari</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-soft">Kode</dt>
                            <dd class="font-mono text-sm">KLG-261002-A84B</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </section>

    {{-- Bento statistik — semua angka nyata dari sistem --}}
    <section class="mt-14">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-12">
            <div class="lift rounded-2xl border border-rule bg-paper p-6 lg:col-span-4">
                <p class="font-mono text-4xl font-medium text-accent tabular">{{ str_pad($doctors->count(), 2, '0', STR_PAD_LEFT) }}</p>
                <p class="mt-2 font-display font-bold text-ink">Dokter gigi aktif</p>
                <p class="mt-1 text-sm text-ink-soft">Praktik rutin setiap minggu</p>
            </div>
            <div class="lift rounded-2xl border border-rule bg-paper p-6 lg:col-span-3">
                <p class="font-mono text-4xl font-medium text-accent tabular">{{ str_pad($services->count(), 2, '0', STR_PAD_LEFT) }}</p>
                <p class="mt-2 font-display font-bold text-ink">Layanan tersedia</p>
                <p class="mt-1 text-sm text-ink-soft">Dari pemeriksaan hingga bleaching</p>
            </div>
            <div class="lift rounded-2xl border border-rule bg-paper p-6 lg:col-span-2">
                <p class="font-mono text-4xl font-medium text-accent tabular">{{ $practiceDays }}</p>
                <p class="mt-2 font-display font-bold text-ink">Hari praktik</p>
                <p class="mt-1 text-sm text-ink-soft">Per minggu</p>
            </div>
            <div class="lift rounded-2xl bg-accent p-6 text-white lg:col-span-3">
                <p class="font-mono text-4xl font-medium tabular">{{ $slotInterval }}<span class="text-lg">min</span></p>
                <p class="mt-2 font-display font-bold">Per slot waktu</p>
                <p class="mt-1 text-sm text-white/80">Interval slot yang rapi dan real-time</p>
            </div>
        </div>
    </section>

    {{-- Marquee layanan — nama layanan asli, loop pelan --}}
    <section class="marquee-strip mt-14 overflow-hidden rounded-2xl border-y border-rule bg-mist px-4 py-4 sm:px-6" aria-label="Daftar layanan">
        <div class="marquee-track gap-8">
            @foreach (array_merge($services->all(), $services->all()) as $layanan)
                <span class="flex items-center gap-8 whitespace-nowrap font-display text-lg font-medium text-ink">
                    {{ $layanan->name }} <span class="font-mono text-sm text-accent" aria-hidden="true">+</span>
                </span>
            @endforeach
        </div>
    </section>

    {{-- Layanan bernomor — grid asimetris --}}
    <section class="mt-20">
        <h2 class="max-w-md font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">
            Layanan perawatan yang tersedia
        </h2>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
            {{-- Span per baris selalu berjumlah 6: unggulan 4 + 2, lalu 2+2+2 --}}
            @foreach ($services as $index => $layanan)
                @php $foto = config('clinic.images.services')[$layanan->name] ?? null; @endphp
                <article class="lift overflow-hidden rounded-2xl border border-rule {{ $loop->first ? 'bg-accent text-white lg:col-span-4' : 'bg-paper lg:col-span-2' }}">
                    @if ($foto)
                        <img src="{{ asset($foto) }}" alt="Layanan {{ $layanan->name }}" loading="lazy"
                             class="h-44 w-full object-cover">
                    @endif
                    <div class="p-6">
                        <p class="font-mono text-sm {{ $loop->first ? 'text-white/70' : 'text-accent' }}">0{{ $index + 1 }}</p>
                        <h3 class="mt-4 font-display text-xl font-bold tracking-tight {{ $loop->first ? 'text-white' : 'text-ink' }}">
                            {{ $layanan->name }}
                        </h3>
                        <p class="mt-2 text-sm {{ $loop->first ? 'text-white/80' : 'text-ink-soft' }}">
                            {{ $layanan->duration_minutes }} menit ·
                            {{ $layanan->price ? 'Rp '.number_format((float) $layanan->price, 0, ',', '.') : 'Hubungi kami' }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Dokter --}}
    <section class="mt-20">
        <h2 class="max-w-md font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">
            Dokter gigi kami
        </h2>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($doctors as $dokter)
                <article class="lift overflow-hidden rounded-2xl border border-rule bg-paper">
                    @if ($dokter->photo)
                        <img src="{{ Storage::url($dokter->photo) }}" alt="{{ $dokter->name }}"
                             class="h-52 w-full object-cover">
                    @else
                        {{-- Placeholder potret dokter — tergantikan foto saat diupload via admin --}}
                        <div class="flex h-52 w-full flex-col items-center justify-center gap-2 bg-mist text-rule">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                 stroke-linecap="round" class="h-16 w-16" aria-hidden="true">
                                <circle cx="12" cy="8" r="3.5"/>
                                <path d="M4.5 20c0-3.6 3.4-6 7.5-6s7.5 2.4 7.5 6"/>
                            </svg>
                            <span class="text-xs font-medium uppercase tracking-widest">Foto dokter</span>
                        </div>
                    @endif
                    <div class="p-6">
                        <h3 class="font-display text-lg font-bold tracking-tight text-ink">{{ $dokter->name }}</h3>
                        <p class="mt-0.5 text-sm text-ink-soft">{{ $dokter->specialization }}</p>
                    </div>
                </article>
            @empty
                <p class="text-ink-soft">Belum ada dokter yang tersedia.</p>
            @endforelse
            <article class="flex flex-col justify-center rounded-2xl bg-mist p-6 sm:col-span-2 lg:col-span-3">
                <p class="font-display text-lg font-bold tracking-tight text-ink">Jadwal praktik seragam</p>
                <p class="mt-1 text-sm text-ink-soft">
                    Senin–Jumat 09.00–16.00 · Sabtu 09.00–13.00 — slot real-time mengikuti cuti dan booking yang sudah ada.
                </p>
            </article>
        </div>
    </section>

    {{-- CTA band navy dengan foto ruang tunggu --}}
    <section class="relative mt-20 overflow-hidden rounded-3xl bg-navy px-6 py-12 text-on-navy sm:px-12">
        <img src="{{ asset(config('clinic.images.cta')) }}" alt="" aria-hidden="true" loading="lazy"
             class="absolute inset-0 h-full w-full object-cover opacity-25">
        <div class="relative flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
            <h2 class="max-w-md font-display text-3xl font-bold tracking-tight">
                Siap datang tepat jadwal?
            </h2>
            <a href="{{ route('booking') }}"
               class="whitespace-nowrap rounded-full bg-white px-7 py-3.5 font-medium text-navy transition-colors hover:bg-mist">
                Mulai Booking
            </a>
        </div>
    </section>
@endsection
