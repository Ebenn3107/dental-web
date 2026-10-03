<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Klinik Gigi Sehat')</title>
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@700,500&f[]=switzer@400,500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="flex min-h-screen flex-col bg-paper font-body text-ink antialiased">
    <header class="sticky top-0 z-20 border-b border-rule bg-paper/95 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
            <a href="{{ route('home') }}" class="font-display text-lg font-bold tracking-tight text-ink">
                Klinik<span class="text-accent">Gigi</span>Sehat
            </a>
            <nav class="flex items-center gap-2 text-sm font-medium sm:gap-6">
                <a href="{{ route('home') }}" wire:navigate class="hidden px-1 text-ink-soft transition-colors hover:text-ink sm:block">Beranda</a>
                <a href="{{ route('check-status') }}" wire:navigate class="hidden px-1 text-ink-soft transition-colors hover:text-ink sm:block">Cek Status</a>
                <a href="{{ route('booking') }}" wire:navigate
                   class="whitespace-nowrap rounded-full bg-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-accent-deep">
                    Booking
                </a>
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 sm:px-6">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <footer class="mt-20 bg-navy text-on-navy">
        <div class="mx-auto max-w-6xl px-4 pt-14 sm:px-6">
            <div class="flex flex-col gap-8 border-b border-on-navy/15 pb-10 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="font-display text-2xl font-bold tracking-tight">Klinik Gigi Sehat</p>
                    <p class="mt-2 max-w-sm text-sm text-on-navy/70">
                        Booking online untuk layanan perawatan gigi — pilih jam praktik dokter favorit Anda tanpa antre.
                    </p>
                </div>
                <nav class="flex flex-wrap gap-x-8 gap-y-3 text-sm font-medium">
                    <a href="{{ route('home') }}" wire:navigate class="text-on-navy/80 transition-colors hover:text-white">Beranda</a>
                    <a href="{{ route('booking') }}" wire:navigate class="text-on-navy/80 transition-colors hover:text-white">Booking</a>
                    <a href="{{ route('check-status') }}" wire:navigate class="text-on-navy/80 transition-colors hover:text-white">Cek Status</a>
                </nav>
            </div>
            <p class="select-none py-8 text-center font-display text-[18vw] font-bold leading-none tracking-tight text-on-navy/10 md:text-[9rem]"
               aria-hidden="true">
                gigisehat
            </p>
            <p class="border-t border-on-navy/15 py-6 text-center text-xs text-on-navy/60">
                &copy; {{ date('Y') }} Klinik Gigi Sehat — sistem booking &amp; jadwal praktik.
            </p>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
