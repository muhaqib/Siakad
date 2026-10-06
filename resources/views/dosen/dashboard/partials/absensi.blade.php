@php
    $statusHarian = \App\Models\KehadiranDosen::getAbsenHarianStatusList();
    $sudahAbsenMasuk = (bool) $absenHariIni;
    $sedangHadir = $sudahAbsenMasuk && $absenHariIni->isHadir();
    $sudahAbsenKeluar = $sedangHadir && $absenHariIni->jam_keluar;
@endphp

<div id="absensi-dosen" class="card-saas overflow-hidden"
     x-data="{ open: {{ $errors->hasAny(['status', 'keterangan', 'bukti_file']) ? 'true' : 'false' }}, status: '{{ old('status', 'hadir') }}' }">

    {{-- Header --}}
    <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <h3 class="font-semibold text-siakad-dark dark:text-white">Absensi Hari Ini</h3>
            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
        </div>
        @if($sudahAbsenKeluar)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">✓ Selesai</span>
        @elseif($sedangHadir)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Hadir
            </span>
        @elseif($sudahAbsenMasuk)
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-{{ $absenHariIni->status_color }}-100 text-{{ $absenHariIni->status_color }}-700">{{ $absenHariIni->status_label }}</span>
        @else
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Belum Absen</span>
        @endif
    </div>

    {{-- STEP 1: Belum absen masuk --}}
    @if(! $sudahAbsenMasuk)
        <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <p class="font-medium text-siakad-dark dark:text-white">Anda belum absen masuk hari ini</p>
                <p class="text-sm text-siakad-secondary dark:text-gray-400">
                    {{ $jadwalHariIni->count() > 0 ? 'Ada '.$jadwalHariIni->count().' kelas yang harus Anda ajar hari ini.' : 'Tidak ada jadwal mengajar hari ini.' }}
                </p>
            </div>
            <button type="button" id="btn-absen-masuk" @click="open = true"
                    class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-3 bg-siakad-primary text-white font-semibold rounded-xl shadow hover:bg-siakad-primary/90 transition w-full md:w-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                Absen Masuk
            </button>
        </div>

    {{-- Sakit / Izin / Tugas Luar --}}
    @elseif(! $sedangHadir)
        <div class="p-6">
            <p class="font-medium text-siakad-dark dark:text-white">Anda tercatat <strong>{{ $absenHariIni->status_label }}</strong> hari ini.</p>
            @if($absenHariIni->keterangan)
                <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-1">Alasan: {{ $absenHariIni->keterangan }}</p>
            @endif
            @if($absenHariIni->bukti_file)
                <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">📎 Bukti telah diunggah</p>
            @endif
            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-3">Kelas hari ini tidak perlu diisi presensi. Semoga lekas kembali beraktivitas.</p>
        </div>

    {{-- STEP 2 & 3: Hadir → Kelas hari ini → Absen keluar --}}
    @else
        <div class="px-6 py-4 bg-siakad-light/30 dark:bg-gray-700/30 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm">
            <span class="text-siakad-secondary dark:text-gray-400">Masuk: <strong class="text-siakad-dark dark:text-white">{{ substr($absenHariIni->jam_masuk, 0, 5) }}</strong></span>
            <span class="text-siakad-secondary dark:text-gray-400">Keluar: <strong class="text-siakad-dark dark:text-white">{{ $absenHariIni->jam_keluar ? substr($absenHariIni->jam_keluar, 0, 5) : '-' }}</strong></span>
        </div>

        <div class="px-6 pt-4 pb-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-siakad-secondary dark:text-gray-400">Kelas Hari Ini ({{ $jadwalHariIni->count() }})</p>
        </div>

        @if($jadwalHariIni->isEmpty())
            <p class="px-6 pb-4 text-sm text-siakad-secondary dark:text-gray-400">Tidak ada kelas hari ini.</p>
        @else
            <div class="divide-y divide-siakad-light dark:divide-gray-700">
                @foreach($jadwalHariIni as $jadwal)
                    @php
                        $pertemuanHariIni = $jadwal->pertemuan->first();
                        $presensiTerisi = $jadwal->pertemuan->sum('presensi_count') > 0;
                        $linkPresensi = $pertemuanHariIni
                            ? route('dosen.presensi.input', $pertemuanHariIni)
                            : route('dosen.presensi.pertemuan.create', $jadwal->kelas);
                    @endphp
                    <div id="kelas-hari-ini-{{ $jadwal->id }}" class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex flex-col items-center justify-center bg-siakad-light/40 dark:bg-gray-700 rounded-lg w-16 h-14 flex-shrink-0">
                            <p class="text-sm font-bold text-siakad-primary">{{ $jadwal->jam_mulai->format('H:i') }}</p>
                            <p class="text-[10px] text-siakad-secondary dark:text-gray-400">{{ $jadwal->jam_selesai->format('H:i') }}</p>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-siakad-dark dark:text-white truncate">{{ $jadwal->kelas->mataKuliah->nama_mk ?? '-' }}</p>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">Kelas {{ $jadwal->kelas->nama_kelas }} • {{ $jadwal->kelas->krs_detail_count }} Mhs • Ruang {{ $jadwal->ruangan ?? '-' }}</p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            @if($presensiTerisi)
                                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">✓ Presensi terisi</span>
                            @else
                                <span class="text-xs font-medium text-amber-600 dark:text-amber-400">Belum presensi</span>
                            @endif
                            <a href="{{ $linkPresensi }}"
                               class="px-4 py-2 rounded-lg text-xs font-semibold transition {{ $presensiTerisi ? 'bg-siakad-light text-siakad-primary hover:bg-siakad-primary/10 dark:bg-gray-700 dark:text-gray-200' : 'bg-siakad-primary text-white hover:bg-siakad-primary/90' }}">
                                {{ $presensiTerisi ? 'Lihat' : 'Presensi Mahasiswa' }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700">
            @if($sudahAbsenKeluar)
                <p class="text-sm text-emerald-700 dark:text-emerald-400 font-medium">Anda sudah absen keluar pukul {{ substr($absenHariIni->jam_keluar, 0, 5) }}. Terima kasih!</p>
            @else
                @if($kelasBelumPresensi > 0)
                    <div class="mb-3 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-300">
                        ⚠️ Masih ada {{ $kelasBelumPresensi }} kelas yang presensi mahasiswanya belum diisi.
                    </div>
                @endif
                <form method="POST" action="{{ route('dosen.kehadiran.checkout', $absenHariIni) }}"
                      @if($kelasBelumPresensi > 0) onsubmit="return confirm('Masih ada {{ $kelasBelumPresensi }} kelas yang belum diisi presensi. Tetap absen keluar?')" @endif>
                    @csrf
                    <button type="submit" id="btn-absen-keluar"
                            class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-3 bg-siakad-dark text-white font-semibold rounded-xl hover:bg-siakad-dark/90 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        Absen Keluar
                    </button>
                </form>
            @endif
        </div>
    @endif

    {{-- Modal Absen Masuk --}}
    @if(! $sudahAbsenMasuk)
        <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @keydown.escape.window="open = false">
            <div @click.outside="open = false" class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold text-siakad-dark dark:text-white mb-1">Absen Masuk</h3>
                <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-5">Pilih status kehadiran Anda hari ini.</p>

                <form method="POST" action="{{ route('dosen.kehadiran.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-2">
                        @foreach($statusHarian as $value => $label)
                            <label class="flex items-center justify-center gap-2 px-3 py-3 border rounded-lg cursor-pointer text-sm font-medium transition"
                                   :class="status === '{{ $value }}' ? 'border-siakad-primary bg-siakad-primary/10 text-siakad-primary' : 'border-siakad-light text-siakad-dark dark:border-gray-700 dark:text-gray-300'">
                                <input type="radio" name="status" value="{{ $value }}" x-model="status" class="sr-only">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('status') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                    <div x-show="status !== 'hadir'" x-cloak class="space-y-4">
                        <div>
                            <label for="keterangan" class="block text-xs font-semibold text-siakad-dark dark:text-gray-300 mb-1">Alasan <span class="text-red-500">*</span></label>
                            <textarea id="keterangan" name="keterangan" rows="3" :disabled="status === 'hadir'"
                                      class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                      placeholder="Contoh: Demam, sudah periksa ke dokter">{{ old('keterangan') }}</textarea>
                            @error('keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="bukti_file" class="block text-xs font-semibold text-siakad-dark dark:text-gray-300 mb-1">Bukti (opsional)</label>
                            <input id="bukti_file" type="file" name="bukti_file" accept=".jpg,.jpeg,.png,.pdf" :disabled="status === 'hadir'"
                                   class="block w-full text-sm text-siakad-secondary file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-siakad-light file:text-siakad-primary dark:text-gray-400">
                            <p class="text-[11px] text-siakad-secondary dark:text-gray-500 mt-1">Surat dokter / surat tugas. JPG, PNG, atau PDF maks 2 MB.</p>
                            @error('bukti_file') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2 border border-siakad-light dark:border-gray-700 text-siakad-dark dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-siakad-light dark:hover:bg-gray-700 transition">Batal</button>
                        <button type="submit" id="btn-simpan-absen" class="px-5 py-2 bg-siakad-primary text-white rounded-lg text-sm font-semibold hover:bg-siakad-primary/90 transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
