# Klinik Gigi Booking System — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Web booking klinik dokter gigi — pasien booking slot online, admin kelola jadwal via Filament.

**Architecture:** Availability logic terpusat di `AvailabilityService` (dipakai form publik & re-check saat submit via `BookingService` dalam DB transaction + lock). Admin pakai Filament 3 resources; halaman publik Blade + satu Livewire full-page component untuk form booking dinamis.

**Tech Stack:** Laravel 11, Filament 3 (Livewire 3), SQLite (dev; MySQL via .env), Tailwind (Vite), Blade, PHP 8.4. Timezone Asia/Jakarta, locale `id`.

**Spec:** `docs/superpowers/plans/` — spec inline pada percakapan user (booking & jadwal ONLY; tanpa rekam medis).

## Global Constraints

- Scope: booking & jadwal ONLY. Tidak ada fitur rekam medis.
- UI bahasa Indonesia. Timezone `Asia/Jakarta` (.env `APP_TIMEZONE`).
- SQLite untuk dev (`database/database.sqlite`); MySQL hanya ganti .env.
- `day_of_week` 0–6, 0=Minggu (sama dengan `Carbon::dayOfWeek`).
- Slot interval 15 menit, panjang = `duration_minutes` service.
- Booking status: `pending|confirmed|completed|cancelled|no_show` (string, bukan DB enum).
- Kode booking format `KLG-YYMMDD-XXXX` (4 char uppercase alnum acak), unique index.
- No. HP disimpan kanonik `62…` (dinormalisasi dari `08…`/`+62…`).
- Tidak simpan data kesehatan selain `notes` (tindakan yang diminta).
- Logic berat di Service/FormRequest/Policy, bukan controller.
- Tidak ada package di luar stack tanpa alasan di README.
- Composer 2.10 di mesin ini memblokir paket dengan advisory (`policy.advisories`); project ini set `composer config policy.advisories.block false` (didokumentasikan di README) karena spec mewajibkan Laravel 11.

## Review Focus

Kondisi yang paling mungkin menggigit user, masing-masing dipasang test di task pemiliknya:

1. **Double booking** — dua orang slot sama harus gagal salah satunya. Test: `BookingServiceTest::test_rejects_double_booking_same_slot` (Task 4).
2. **Slot tepat bersinggungan (adjacent)** — 09:00–10:00 lalu 10:00–11:00 harus BOLEH (overlap hanya jika `start < otherEnd && end > otherStart`). Test: `test_allows_adjacent_bookings` (Task 4).
3. **Phone berbeda format** — booking pakai `0812…`, cek status pakai `62812…` harus cocok (normalisasi). Test: `CheckStatusTest::test_matches_phone_in_different_format` (Task 7).
4. **Slot dokter lain** — booking dokter A jangan memblokir slot dokter B di jam sama. Test: `test_other_doctor_slot_not_blocked` (Task 4).
5. **Slot hari ini yang sudah lewat** — jam sekarang misal 15:00, slot 09:00 tidak boleh muncul tapi 16:00 tetap ada. Test: `test_today_only_returns_future_slots` (Task 4).

---

### Task 1: Setup Filament + auth admin + seeder

**Files:** Modify `composer.json`, `app/Models/User.php`, `database/seeders/*`; Create `app/Providers/Filament/AdminPanelProvider.php`, `database/seeders/AdminUserSeeder.php`, `database/seeders/ClinicSeeder.php`.

**Interfaces:**
- Produces: admin panel di `/admin`; user admin `admin@klinik.test` / `password`; seeder terpisah `ClinicSeeder` (isi diisi di Task 2 setelah migration; dipanggil dari `DatabaseSeeder`).

- [ ] Step 1: `composer config policy.advisories.block false` lalu `composer require filament/filament:"^3.3" --no-interaction`
- [ ] Step 2: `php artisan filament:install --panels --no-interaction`; panel id `admin`, path `admin`, font default, dark mode on
- [ ] Step 3: `User` implements `FilamentUser` — `canAccessPanel()` return `true` (tidak ada registrasi publik)
- [ ] Step 4: `AdminUserSeeder` + daftarkan di `DatabaseSeeder` (dipanggil sebelum ClinicSeeder)
- [ ] Step 5: Buat `ClinicSeeder` stub kosong (data diisi Task 2)
- [ ] Step 6: Verify `php artisan migrate:fresh --seed` sukses dan `php artisan test` hijau (default ExampleTest disesuaikan agar `/` return 200 setelah route home ada di Task 5 — untuk sekarang hapus ExampleTest)
- [ ] Step 7: Commit `feat: install Filament admin panel with admin user seeder`

### Task 2: Migrations + models + relasi

**Files:** Create 5 migrations, `app/Models/{Service,Doctor,DoctorSchedule,DoctorLeave,Booking}.php`, `app/Enums/BookingStatus.php`, factories di `database/factories/`.

**Interfaces:**
- Produces:
  - Relasi: `Service::doctors()` belongsToMany `doctor_service`; `Doctor::services()`, `Doctor::schedules()` hasMany `DoctorSchedule`, `Doctor::leaves()` hasMany `DoctorLeave`, `Doctor::bookings()`;
  - `Booking` belongsTo `doctor`, `service`; casts `booking_date: date`, `status: BookingStatus`;
  - `BookingStatus` enum string: `pending/confirmed/completed/cancelled/no_show` + `label(): string` Indonesia + `color(): string` (untuk Filament badge).
- Skema: `services(name, duration_minutes int, price decimal(10,2) nullable, is_active bool default 1)`; `doctors(name, specialization, photo nullable, is_active bool default 1)`; `doctor_schedules(doctor_id FK cascade, day_of_week tinyint, start_time time, end_time time)`; `doctor_leaves(doctor_id FK cascade, date date, reason nullable)`; `bookings(code unique, patient_name, patient_phone, doctor_id FK, service_id FK, booking_date date, start_time time, end_time time, status string default pending index, notes nullable, timestamps)` + index komposit `(doctor_id, booking_date)`; pivot `doctor_service(doctor_id, service_id)` PK komposit.

- [ ] Step 1: Tulis factories (Service, Doctor, DoctorSchedule, DoctorLeave, Booking)
- [ ] Step 2: Tulis migrations sesuai skema di atas
- [ ] Step 3: Tulis models + `BookingStatus` enum
- [ ] Step 4: Isi `ClinicSeeder`: 3 dokter (dr. Andi Pratama/Ortodonti, drg. Sari Wulandari/Perawatan Gigi, drg. Budi Santoso/Bedah Mulut), 5 layanan (Pemeriksaan & Konsultasi 30', Scaling 45', Tambal Gigi 60', Cabut Gigi 60', Bleaching 60' — harga Rp50k/150k/250k/300k/500k), jadwal Senin–Jumat 09:00–16:00, Sabtu 09:00–13:00, semua dokter bisa semua layanan
- [ ] Step 5: Verify `php artisan migrate:fresh --seed` + tinker check relasi
- [ ] Step 6: Commit `feat: add domain models, migrations, and clinic seeder`

### Task 3: Filament resources Services, Doctors (+schedules, leaves)

**Files:** Create `app/Filament/Resources/{ServiceResource,DoctorResource}.php` + pages; `app/Filament/Resources/DoctorResource/RelationManagers/{SchedulesRelationManager,LeavesRelationManager}.php`; navigation group "Klinik", label Indonesia.

**Interfaces:**
- Consumes: models Task 2, `BookingStatus` tidak dipakai di sini.
- Produces: admin CRUD lengkap untuk layanan/dokter; jadwal & cuti dikelola via relation manager di DoctorResource.

- [ ] Step 1: `ServiceResource` — form: name (required), duration_minutes (numeric, min 15, step 15, required), price (nullable, prefix "Rp"), is_active (toggle default true); table filter is_active
- [ ] Step 2: `DoctorResource` — form: name, specialization, photo (FileUpload disk public, image, nullable), is_active toggle; `ServicesCheckboxList`/Select multiple untuk pivot; table kolom nama+spesialisasi+is_active
- [ ] Step 3: `SchedulesRelationManager` — day_of_week Select (0=Minggu … 6=Sabtu), TimePicker start/end, validasi end > start
- [ ] Step 4: `LeavesRelationManager` — DatePicker date (required), reason nullable
- [ ] Step 5: Verify manual via `php artisan test` (panel smoke test: login admin → GET /admin/services 200) — buat `tests/Feature/AdminPanelTest`
- [ ] Step 6: Commit `feat: Filament resources for services and doctors with schedules and leaves`

### Task 4: AvailabilityService + BookingService + unit test

**Files:** Create `app/Services/AvailabilityService.php`, `app/Services/BookingService.php`, `app/Exceptions/SlotUnavailableException.php`, `app/Support/Phone.php`; Test `tests/Unit/AvailabilityServiceTest.php`, `tests/Unit/BookingServiceTest.php`.

**Interfaces:**
- Consumes: models Task 2.
- Produces:
  - `AvailabilityService::getAvailableSlots(Doctor $doctor, Service $service, Carbon\CarbonImmutable $date): array` — array of `['start' => 'H:i', 'end' => 'H:i']` ascending; `[]` jika: tanggal lampau, hari tanpa jadwal, dokter cuti pada tanggal itu, atau semua slot lewat (hari ini).
  - Algoritma: ambil semua `doctor_schedules` hari itu (`day_of_week == $date->dayOfWeek`); per baris jadwal, grid mulai `start_time` melangkah 15 menit, slot valid jika `slotEnd <= end_time`; buang slot `start < now` bila hari ini; buang slot overlap booking `status != cancelled` (kondisi: `start < existingEnd && end > existingStart`); dokter cuti (baris `doctor_leaves.date == tanggal`) → `[]`.
  - `BookingService::create(array $data): Booking` — `DB::transaction`: re-generate slot via AvailabilityService lalu cek `start_time` masih ada DI DALAM transaction + `lockForUpdate` pada bookings dokter itu di tanggal itu; hitung `end_time = start + duration`; `patient_phone` via `Phone::normalize`; kode unik `KLG-YYMMDD-XXXX` (retry max 5); status `pending`; throw `SlotUnavailableException` jika slot hilang.
  - `Phone::normalize(string $raw): string` — buang non-digit; `0` → `62`; `8…` → `62`+; validasi regex raw: `/^(\+62|62|0)8\d{7,11}$/` (dipakai Livewire/FormRequest, throw `InvalidArgumentException` bila tak valid).

- [ ] Step 1: Tulis `AvailabilityServiceTest` (7 test: slot dalam jadwal; cuti→kosong; hari tanpa jadwal→kosong; tanggal lampau→kosong; hari ini hanya slot mendatang — pakai `Carbon::setTestNow`; durasi 30' vs 60' → jumlah slot beda; slot overlap booking aktif ter-exclude, booking cancelled tidak)
- [ ] Step 2: Run, verify fail (class belum ada)
- [ ] Step 3: Implement `AvailabilityService` + `Phone`
- [ ] Step 4: Run, verify pass
- [ ] Step 5: Tulis `BookingServiceTest` (sukses → end_time/kode/status benar; double booking sama slot → `SlotUnavailableException`; adjacent 09:00–10:00 + 10:00–11:00 → sukses; dokter lain jam sama → sukses)
- [ ] Step 6: Run, verify fail → implement `BookingService` + exception → pass
- [ ] Step 7: `php artisan test` penuh hijau
- [ ] Step 8: Commit `feat: availability and booking services with double-booking protection`

### Task 5: Public booking flow

**Files:** Create `app/Http/Controllers/HomeController.php`, `app/Livewire/BookingForm.php`, `resources/views/layouts/public.blade.php`, `resources/views/home.blade.php`, `resources/views/livewire/booking-form.blade.php`, `resources/views/booking/success.blade.php`; Modify `routes/web.php`; Test `tests/Feature/PublicPagesTest.php`.

**Interfaces:**
- Consumes: `AvailabilityService::getAvailableSlots`, `BookingService::create` (Task 4), models Task 2.
- Produces: route `/` (home), `/booking` (Livewire full-page), `/booking/success/{code}`; layout Blade `x-public-layout`-style via `layouts.public`.

- [ ] Step 1: Layout publik (Tailwind via `@vite`, header nav: Beranda/Booking/Cek Status, footer klinik) — bahasa Indonesia
- [ ] Step 2: `HomeController@index` → home: hero + CTA booking, grid layanan aktif (nama, durasi menit, harga Rp), grid dokter aktif (nama, spesialisasi)
- [ ] Step 3: `BookingForm` Livewire: props publik `$serviceId, $doctorId, $date, $slot, $name, $phone, $notes`; `updated()` reset langkah bawah; computed `doctors()` (Doctor aktif yang punya service terpilih via pivot), `slots()` (AvailabilityService untuk dokter+tanggal terpilih); rules: serviceId exists services is_active, doctorId exists + pivot, date `after_or_equal:today`, slot in_array computed slots, name required max 100, phone regex `/^(\+62|62|0)8\d{7,11}$/`, notes nullable max 500; `submit()` → BookingService::create → redirect `/booking/success/{code}`; error "Maaf, slot sudah diambil" bila `SlotUnavailableException` (hook `onSlotUnavailable` di UI: tampilkan pesan + refresh slot)
- [ ] Step 4: View booking-form: langkah 1 layanan (radio cards) → 2 dokter → 3 tanggal (input date min=today) → 4 slot (tombol `H:i – H:i`, tampil "Tidak ada slot" kosong) → 5 data diri; responsif mobile; loading state `wire:loading`
- [ ] Step 5: Success page `/booking/success/{code}`: tampilkan kode besar, ringkasan (layanan, dokter, tanggal, jam, nama, status pending), CTA booking lagi (placeholder tombol WA di Task 7)
- [ ] Step 6: `PublicPagesTest`: home menampilkan layanan & dokter seeded; `Livewire::test(BookingForm)` set semua field → `call('submit')` → `assertRedirect` → assert DB ada booking; phone `12345` → `hasError('phone')`
- [ ] Step 7: Verify `php artisan test` hijau + `npm install && npm run build`
- [ ] Step 8: Commit `feat: public home page and dynamic booking flow`

### Task 6: BookingResource + status actions + dashboard widget

**Files:** Create `app/Filament/Resources/BookingResource.php` (+Pages), `app/Filament/Widgets/BookingStatsWidget.php`, `app/Policies/BookingPolicy.php`; Modify `app/Providers/Filament/AdminPanelProvider.php` (discover widget), `app/Models/Booking.php` (HasPolicy).

**Interfaces:**
- Consumes: models + `BookingStatus` (Task 2), admin panel (Task 1).
- Produces: admin kelola booking; `BookingPolicy` (viewAny/view/update → semua admin user true — tidak ada role lain di scope ini).

- [ ] Step 1: `BookingResource` read-mostly: form hanya patient_name/phone/notes/doctor/service/date/time editable saat create; table kolom: code (copyable), patient_name, doctor.name, service.name, booking_date, jam (start–end), status badge (warna dari enum); filter: SelectFilter status, SelectFilter dokter, Filter "Hari Ini" (toggle whereDate = today); sort default booking_date desc
- [ ] Step 2: Action ubah status per row (visible bila transisi valid): Konfirmasi (pending→confirmed), Tandai Selesai (confirmed→completed), Batalkan (pending|confirmed→cancelled), Tidak Hadir (confirmed→no_show); `requiresConfirmation()`
- [ ] Step 3: `BookingStatsWidget` (StatsOverview): "Booking Hari Ini" (count whereDate booking_date = today), "Menunggu Konfirmasi" (status pending), "Selesai Hari Ini"
- [ ] Step 4: `BookingPolicy` + register di `AppServiceProvider` (auto-discover)
- [ ] Step 5: Commit `feat: Filament booking resource with status actions and dashboard stats`

### Task 7: WA confirmation link + cek status

**Files:** Create `config/clinic.php`, `app/Http/Controllers/{BookingSuccessController,CheckStatusController}.php`, `app/Http/Requests/CheckStatusRequest.php`, `resources/views/booking/check-status.blade.php`; Modify `app/Models/Booking.php` (`waLink()`), `resources/views/booking/success.blade.php`, `routes/web.php`; Test `tests/Feature/CheckStatusTest.php`.

**Interfaces:**
- Consumes: Booking model; layout Task 5.
- Produces: `Booking::waLink(): string` — `https://wa.me/{CLINIC_WHATSAPP_NUMBER}?text=...` pesan: "Halo Klinik Gigi Sehat, saya {nama}. Saya sudah booking {service} dengan {doctor} pada {tanggal d/m/Y} pukul {start}–{end} WIB. Kode booking: {code}. Terima kasih."; nomor klinik dari env `CLINIC_WHATSAPP_NUMBER` (default `6281234567890`).

- [ ] Step 1: `config/clinic.php` (name, whatsapp_number) + tombol WA di success page (target _blank)
- [ ] Step 2: `CheckStatusRequest` (code required string, phone required + regex, `prepareForValidation` → `Phone::normalize`); controller cari Booking code+phone → tampil hasil (status badge, jadwal) atau error "Booking tidak ditemukan / no. HP tidak cocok"; GET form + GET submit (query) — stateless
- [ ] Step 3: View cek status di layout publik, link di nav
- [ ] Step 4: `CheckStatusTest`: cocok code+phone → tampil; phone beda format (`0812…` vs tersimpan `62812…`) → tetap cocok; phone salah → pesan tidak ditemukan
- [ ] Step 5: Commit `feat: WhatsApp confirmation link and booking status check page`

### Task 8: Validasi, error handling, polish UI

**Files:** Modify view publik & Livewire sesuai temuan; `resources/views/errors/minimal` opsional; pastikan 404 success page untuk kode tak dikenal (abort 404 → halaman error default Laravel dalam bahasa Indonesia).

**Checklist:**
- [ ] Step 1: Semua form tervalidasi server-side (Livewire rules = server-side; CheckStatusRequest; Filament form otomatis)
- [ ] Step 2: Success page kode salah → 404; slot persis diambil orang lain saat submit → pesan ramah + slot refresh
- [ ] Step 3: Responsif: grid mobile-first, tombol slot wrap, table admin default Filament
- [ ] Step 4: `php artisan test` hijau penuh
- [ ] Step 5: Commit `feat: validation hardening and responsive polish`

### Task 9: README

**Files:** Modify `README.md` (ganti default Laravel).

- [ ] Step 1: README bahasa Indonesia: fitur, stack, setup (composer install, copy .env, touch sqlite / konfig MySQL, migrate --seed, npm install + build, artisan serve), akun admin (admin@klinik.test / password + catatan ganti), jalankan test, catatan composer policy advisories + timezone, alur booking, out of scope fase 2
- [ ] Step 2: `php artisan test` final hijau
- [ ] Step 3: Commit `docs: project README with setup and testing guide`
