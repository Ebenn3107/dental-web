# Klinik Gigi Sehat — Sistem Booking & Jadwal

Aplikasi web booking klinik dokter gigi: pasien booking slot online, admin mengelola layanan, dokter, jadwal, cuti, dan booking lewat panel Filament.

**Scope: booking & jadwal saja.** Tidak ada rekam medis (ditunda ke fase 2).

## Fitur

- **Halaman publik** (bahasa Indonesia, responsif):
  - Beranda: hero + daftar layanan (durasi, harga) + daftar dokter
  - Form booking dinamis (Livewire): layanan → dokter (sesuai layanan) → tanggal → slot tersedia → data diri
  - Slot dihitung real-time: interval 15 menit, panjang sesuai durasi layanan, menghindari booking aktif, cuti dokter, dan jam yang sudah lewat
  - Halaman sukses: kode booking unik (`KLG-YYMMDD-XXXX`) + tombol **Konfirmasi via WhatsApp** (pesan terisi otomatis)
  - Cek status booking dengan kode + no. HP (no. HP dinormalisasi, jadi `0812…` dan `62812…` sama saja)
- **Admin panel** (Filament 3, di `/admin`):
  - CRUD Layanan & Dokter (jadwal praktik + cuti via relation manager di halaman dokter, pivot layanan yang dikuasai)
  - Resource Booking: filter status/tanggal/dokter, aksi ubah status (Konfirmasi → Selesai / Batalkan / Tidak Hadir) sesuai transisi yang sah
  - Widget dashboard: booking hari ini, menunggu konfirmasi, selesai hari ini
- **Perlindungan double booking**: re-check ketersediaan di dalam `DB::transaction` + `lockForUpdate` (`BookingService`); dua submit paralel tidak bisa merebut slot yang sama. Test wajibnya ada.

## Stack

- Laravel 11 (PHP 8.4), Filament 3 + Livewire 3
- Blade + Tailwind CSS (Vite) untuk halaman publik
- SQLite (default dev) / MySQL (produksi) — timezone `Asia/Jakarta`

Tidak ada package di luar stack di atas.

## Cara Menjalankan

Prasyarat: PHP >= 8.2 dengan ekstensi `pdo_sqlite`, Composer, Node.js >= 20.

```bash
# 1. Dependensi PHP
composer install

# 2. Konfigurasi
cp .env.example .env
php artisan key:generate

# 3. Database (SQLite — siap pakai)
touch database/database.sqlite
php artisan migrate --seed

#    Untuk MySQL: ubah DB_CONNECTION=mysql + kredensial di .env,
#    lalu php artisan migrate --seed (tanpa touch database.sqlite)

# 4. Dependensi + build frontend
npm install
npm run build

# 5. Jalankan
php artisan serve
```

- Publik: <http://127.0.0.1:8000>
- Admin: <http://127.0.0.1:8000/admin> — login `admin@klinik.test` / `password` (**segera ganti** untuk lingkungan nyata)
- Nomor WhatsApp klinik: set `CLINIC_WHATSAPP_NUMBER=62xxxxxxxxxx` di `.env` (default dummy `6281234567890`)

Seeder mengisi 3 dokter, 5 layanan, dan jadwal Senin–Jumat 09:00–16:00 serta Sabtu 09:00–13:00.

## Menjalankan Test

```bash
php artisan test
```

Menggunakan SQLite in-memory (tidak mengganggu database dev). Cakupan antara lain:

- `AvailabilityServiceTest` — slot sesuai jadwal, cuti, hari tanpa jadwal, tanggal lampau, slot hari ini hanya mendatang, durasi layanan berbeda → slot berbeda, overlap booking aktif vs cancelled
- `BookingServiceTest` — kode/end_time/status benar, **double booking ditolak**, slot berdempetan (09:00–10:00 lalu 10:00–11:00) diizinkan, slot dokter lain tidak terblokir, slot lampau ditolak
- `PublicPagesTest` / `CheckStatusTest` / `AdminPanelTest` / `AdminBookingTest` — alur booking publik, validasi no. HP Indonesia, cek status (termasuk format HP berbeda), panel admin & aksi status

## Catatan Teknis

- **Composer security advisories**: Composer 2.10 memblokir instalasi paket dengan advisory (`policy.advisories.block`). Project ini mewajibkan Laravel 11 (spec), yang berstatus advisory (terutama di debug mode), jadi repositori ini set `composer config policy.advisories.block false` secara lokal. Jalankan `composer audit` untuk melihat detailnya, dan pertimbangkan upgrade ke Laravel 12 saat memungkinkan.
- **Logika slot** terpusat di `App\Services\AvailabilityService`; pembuatan booking di `App\Services\BookingService` (transaction + lock). Controller/view tidak memuat logika berat.
- **No. HP** disimpan kanonik `62…` (`App\Support\Phone`); semua pencocokan (cek status) memakai bentuk kanonik.
- Validasi memakai aturan di Livewire component / FormRequest; pesan validasi bahasa Indonesia di `lang/id/validation.php`.

## Out of Scope (Fase 2)

Reminder WhatsApp otomatis (n8n), pembayaran/DP, rekam medis, multi-cabang.
