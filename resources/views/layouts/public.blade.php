<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Klinik Gigi Sehat')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-800 antialiased">
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold text-teal-700">
                <span aria-hidden="true">🦷</span> Klinik Gigi Sehat
            </a>
            <nav class="flex items-center gap-3 text-sm font-medium sm:gap-5">
                <a href="{{ route('home') }}" class="hover:text-teal-700">Beranda</a>
                <a href="{{ route('booking') }}" class="hover:text-teal-700">Booking</a>
                <a href="{{ route('check-status') }}" class="hover:text-teal-700">Cek Status</a>
                <a href="{{ route('booking') }}"
                   class="hidden rounded-full bg-teal-600 px-4 py-1.5 text-white shadow-sm transition hover:bg-teal-700 sm:inline-block">
                    Booking Cepat
                </a>
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-8">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-5xl px-4 py-6 text-center text-sm text-slate-500">
            &copy; {{ date('Y') }} Klinik Gigi Sehat — Booking &amp; jadwal praktik dokter gigi.
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
