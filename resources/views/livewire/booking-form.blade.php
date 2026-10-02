<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Booking Kunjungan</h1>
    <p class="mt-1 text-slate-500">Pilih layanan, dokter, tanggal, lalu jam praktik yang tersedia.</p>

    <form wire:submit="submit" class="mt-8 space-y-8">

        {{-- Langkah 1: Layanan --}}
        <fieldset class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <legend class="px-2 text-sm font-semibold text-teal-700">1. Pilih Layanan</legend>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach (\App\Models\Service::active()->orderBy('name')->get() as $layanan)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition
                        {{ $serviceId === $layanan->id ? 'border-teal-500 bg-teal-50 ring-1 ring-teal-500' : 'border-slate-200 hover:border-teal-300' }}">
                        <input type="radio" value="{{ $layanan->id }}" wire:model.live="serviceId"
                               class="mt-1 text-teal-600 focus:ring-teal-500">
                        <span>
                            <span class="block font-semibold text-slate-900">{{ $layanan->name }}</span>
                            <span class="block text-sm text-slate-500">
                                {{ $layanan->duration_minutes }} menit ·
                                {{ $layanan->price ? 'Rp '.number_format((float) $layanan->price, 0, ',', '.') : 'Hubungi kami' }}
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('serviceId') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </fieldset>

        {{-- Langkah 2: Dokter --}}
        <fieldset class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm @unless($serviceId) opacity-50 @endunless">
            <legend class="px-2 text-sm font-semibold text-teal-700">2. Pilih Dokter</legend>
            @if (! $serviceId)
                <p class="text-sm text-slate-500">Pilih layanan terlebih dahulu.</p>
            @elseif ($this->doctors->isEmpty())
                <p class="text-sm text-slate-500">Belum ada dokter untuk layanan ini.</p>
            @else
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($this->doctors as $dokter)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-4 transition
                            {{ $doctorId === $dokter->id ? 'border-teal-500 bg-teal-50 ring-1 ring-teal-500' : 'border-slate-200 hover:border-teal-300' }}">
                            <input type="radio" value="{{ $dokter->id }}" wire:model.live="doctorId"
                                   class="text-teal-600 focus:ring-teal-500">
                            <span>
                                <span class="block font-semibold text-slate-900">{{ $dokter->name }}</span>
                                <span class="block text-sm text-slate-500">{{ $dokter->specialization }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
            @error('doctorId') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </fieldset>

        {{-- Langkah 3: Tanggal --}}
        <fieldset class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm @unless($doctorId) opacity-50 @endunless">
            <legend class="px-2 text-sm font-semibold text-teal-700">3. Pilih Tanggal</legend>
            <input type="date" wire:model.live="date" min="{{ today()->toDateString() }}"
                   class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500 sm:w-64">
            @error('date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </fieldset>

        {{-- Langkah 4: Jam --}}
        <fieldset class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm @unless($date) opacity-50 @endunless">
            <legend class="px-2 text-sm font-semibold text-teal-700">4. Pilih Jam</legend>

            @if ($date)
                <div wire:loading wire:target="date, doctorId, serviceId" class="text-sm text-slate-500">
                    Memuat slot…
                </div>
                <div wire:loading.remove wire:target="date, doctorId, serviceId">
                    @if ($this->slots->isEmpty())
                        <p class="text-sm text-slate-500">
                            Tidak ada slot pada tanggal ini. Dokter mungkin libur atau slot sudah penuh.
                        </p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($this->slots as $pilihan)
                                <button type="button"
                                        wire:click="$set('slot', '{{ $pilihan['start'] }}')"
                                        class="rounded-lg border px-3 py-2 text-sm font-medium transition
                                        {{ $slot === $pilihan['start']
                                            ? 'border-teal-600 bg-teal-600 text-white'
                                            : 'border-slate-200 bg-white text-slate-700 hover:border-teal-400 hover:text-teal-700' }}">
                                    {{ $pilihan['start'] }} – {{ $pilihan['end'] }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <p class="text-sm text-slate-500">Pilih tanggal untuk melihat slot tersedia.</p>
            @endif
            @error('slot') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </fieldset>

        {{-- Langkah 5: Data diri --}}
        <fieldset class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <legend class="px-2 text-sm font-semibold text-teal-700">5. Data Diri</legend>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nama Lengkap</label>
                    <input type="text" id="name" wire:model="name" placeholder="Nama Anda"
                           class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">No. HP / WhatsApp</label>
                    <input type="tel" id="phone" wire:model="phone" placeholder="08xxxxxxxxxx"
                           class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="notes" class="mb-1 block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                    <textarea id="notes" wire:model="notes" rows="3" placeholder="Keluhan atau permintaan khusus"
                              class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500"></textarea>
                    @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </fieldset>

        <div class="flex items-center justify-end gap-4">
            <div wire:loading wire:target="submit" class="text-sm text-slate-500">Menyimpan…</div>
            <button type="submit"
                    class="rounded-full bg-teal-600 px-8 py-3 font-semibold text-white shadow-md transition hover:bg-teal-700 disabled:opacity-50">
                Konfirmasi Booking
            </button>
        </div>
    </form>
</div>
