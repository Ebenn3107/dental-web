<div class="grid gap-10 py-4 lg:grid-cols-12">
    {{-- Kolom kiri: judul & penjelasan (desktop) --}}
    <div class="lg:col-span-4">
        <h1 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">
            Booking kunjungan Anda.
        </h1>
        <p class="mt-4 text-ink-soft">
            Lima langkah singkat — slot dihitung real-time dari jadwal dokter,
            jadi yang Anda lihat pasti tersedia saat dikirim.
        </p>
        <ul class="mt-8 space-y-4 text-sm">
            <li class="flex gap-3">
                <span class="font-mono text-accent">01</span>
                <span class="text-ink-soft">Slot tampil sesuai durasi layanan yang dipilih</span>
            </li>
            <li class="flex gap-3">
                <span class="font-mono text-accent">02</span>
                <span class="text-ink-soft">Dokter cuti atau slot terisi langsung hilang dari daftar</span>
            </li>
            <li class="flex gap-3">
                <span class="font-mono text-accent">03</span>
                <span class="text-ink-soft">Setelah submit, konfirmasi lewat WhatsApp</span>
            </li>
        </ul>
    </div>

    {{-- Kolom kanan: form langkah demi langkah --}}
    <form wire:submit="submit" class="space-y-6 lg:col-span-8">

        {{-- Langkah 1: Layanan --}}
        <fieldset class="rounded-2xl border border-rule bg-paper p-5 sm:p-6">
            <legend class="px-2 font-mono text-xs uppercase tracking-widest text-accent">1 · Layanan</legend>
            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                @foreach (\App\Models\Service::active()->orderBy('name')->get() as $layanan)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-colors
                        {{ $serviceId === $layanan->id ? 'border-accent bg-accent/5' : 'border-rule hover:border-accent/50' }}">
                        <input type="radio" value="{{ $layanan->id }}" wire:model.live="serviceId"
                               class="mt-1 accent-accent">
                        <span>
                            <span class="block font-medium text-ink">{{ $layanan->name }}</span>
                            <span class="block text-sm text-ink-soft tabular">
                                {{ $layanan->duration_minutes }} menit ·
                                {{ $layanan->price ? 'Rp '.number_format((float) $layanan->price, 0, ',', '.') : 'Hubungi kami' }}
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('serviceId') <p class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
        </fieldset>

        {{-- Langkah 2: Dokter --}}
        <fieldset class="rounded-2xl border border-rule bg-paper p-5 sm:p-6 @unless($serviceId) hidden @endunless">
            <legend class="px-2 font-mono text-xs uppercase tracking-widest text-accent">2 · Dokter</legend>
            @if (! $serviceId)
                <p class="text-sm text-ink-soft">Pilih layanan terlebih dahulu.</p>
            @elseif ($this->doctors->isEmpty())
                <p class="text-sm text-ink-soft">Belum ada dokter untuk layanan ini.</p>
            @else
                <div class="mt-2 grid gap-3 sm:grid-cols-2">
                    @foreach ($this->doctors as $dokter)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-4 transition-colors
                            {{ $doctorId === $dokter->id ? 'border-accent bg-accent/5' : 'border-rule hover:border-accent/50' }}">
                            <input type="radio" value="{{ $dokter->id }}" wire:model.live="doctorId"
                                   class="accent-accent">
                            <span>
                                <span class="block font-medium text-ink">{{ $dokter->name }}</span>
                                <span class="block text-sm text-ink-soft">{{ $dokter->specialization }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
            @error('doctorId') <p class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
        </fieldset>

        {{-- Langkah 3: Tanggal --}}
        <fieldset class="rounded-2xl border border-rule bg-paper p-5 sm:p-6 @unless($doctorId) hidden @endunless">
            <legend class="px-2 font-mono text-xs uppercase tracking-widest text-accent">3 · Tanggal</legend>
            <input type="date" wire:model.live="date" min="{{ today()->toDateString() }}"
                   class="mt-2 w-full rounded-xl border-rule text-ink focus:border-accent sm:w-64">
            @error('date') <p class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
        </fieldset>

        {{-- Langkah 4: Jam --}}
        <fieldset class="rounded-2xl border border-rule bg-paper p-5 sm:p-6 @unless($date) hidden @endunless">
            <legend class="px-2 font-mono text-xs uppercase tracking-widest text-accent">4 · Jam</legend>

            @if ($date)
                <div wire:loading wire:target="date, doctorId, serviceId" class="text-sm text-ink-soft">
                    Memuat slot…
                </div>
                <div wire:loading.remove wire:target="date, doctorId, serviceId">
                    @if ($this->slots->isEmpty())
                        <p class="text-sm text-ink-soft">
                            Tidak ada slot pada tanggal ini — dokter mungkin libur atau slot sudah penuh.
                        </p>
                    @else
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($this->slots as $pilihan)
                                <button type="button"
                                        wire:click="$set('slot', '{{ $pilihan['start'] }}')"
                                        class="rounded-full border px-4 py-2 text-sm font-medium tabular transition-colors
                                        {{ $slot === $pilihan['start']
                                            ? 'border-accent bg-accent text-white'
                                            : 'border-rule bg-paper text-ink hover:border-accent hover:text-accent-deep' }}">
                                    {{ $pilihan['start'] }} – {{ $pilihan['end'] }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <p class="text-sm text-ink-soft">Pilih tanggal untuk melihat slot tersedia.</p>
            @endif
            @error('slot') <p class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
        </fieldset>

        {{-- Langkah 5: Data diri --}}
        <fieldset class="rounded-2xl border border-rule bg-paper p-5 sm:p-6">
            <legend class="px-2 font-mono text-xs uppercase tracking-widest text-accent">5 · Data diri</legend>
            <div class="mt-2 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-ink">Nama Lengkap</label>
                    <input type="text" id="name" wire:model="name" placeholder="Nama Anda"
                           class="w-full rounded-xl border-rule text-ink placeholder:text-ink-soft focus:border-accent">
                    @error('name') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-ink">No. HP / WhatsApp</label>
                    <input type="tel" id="phone" wire:model="phone" placeholder="08xxxxxxxxxx"
                           class="w-full rounded-xl border-rule text-ink placeholder:text-ink-soft focus:border-accent">
                    @error('phone') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="notes" class="mb-1 block text-sm font-medium text-ink">Catatan (opsional)</label>
                    <textarea id="notes" wire:model="notes" rows="3" placeholder="Keluhan atau permintaan khusus"
                              class="w-full rounded-xl border-rule text-ink placeholder:text-ink-soft focus:border-accent"></textarea>
                    @error('notes') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
            </div>
        </fieldset>

        <div class="flex items-center justify-end gap-4">
            <div wire:loading wire:target="submit" class="text-sm text-ink-soft">Menyimpan…</div>
            <button type="submit"
                    class="whitespace-nowrap rounded-full bg-accent px-8 py-3.5 font-medium text-white transition-colors hover:bg-accent-deep">
                Konfirmasi Booking
            </button>
        </div>
    </form>
</div>
