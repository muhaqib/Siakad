<x-app-layout>
    <x-slot name="header">
        Buat Pertemuan Baru
    </x-slot>

    <!-- Breadcrumb -->
    <div class="mb-5">
        <a href="{{ route('dosen.presensi.kelas', $kelas) }}" class="inline-flex items-center gap-2 text-siakad-secondary hover:text-siakad-primary transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke {{ $kelas->mataKuliah->nama_mk }}
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <div class="card-saas overflow-hidden">
                <!-- Header -->
                <div class="p-5 bg-siakad-primary text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold">{{ $kelas->mataKuliah->nama_mk }}</h2>
                            <p class="text-white/70 text-sm">{{ $kelas->mataKuliah->kode_mk }} • Kelas {{ $kelas->nama_kelas }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('dosen.presensi.pertemuan.store', $kelas) }}" method="POST" class="p-6 space-y-5">
                    @csrf

                    <!-- Jadwal Selection -->
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark mb-2">Jadwal Kuliah</label>
                        @if($jadwalList->isEmpty())
                        <div class="p-4 bg-siakad-light text-siakad-secondary rounded-lg text-sm">
                            Tidak ada jadwal untuk kelas ini. Hubungi admin untuk menambahkan jadwal.
                        </div>
                        @else
                        <div class="space-y-2">
                            @foreach($jadwalList as $jadwal)
                            <label class="flex items-center gap-3 p-4 border border-siakad-light rounded-lg cursor-pointer hover:border-siakad-primary/50 transition has-[:checked]:border-siakad-primary has-[:checked]:bg-siakad-primary/5">
                                <input type="radio" name="jadwal_kuliah_id" value="{{ $jadwal->id }}" class="text-siakad-primary focus:ring-siakad-primary" {{ old('jadwal_kuliah_id') == $jadwal->id ? 'checked' : ($loop->first && !old('jadwal_kuliah_id') ? 'checked' : '') }} required>
                                <div>
                                    <p class="font-medium text-siakad-dark">{{ $jadwal->hari }}</p>
                                    <p class="text-sm text-siakad-secondary">{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }} • {{ $jadwal->ruangan ?? 'Ruangan TBA' }}</p>
                                    <p class="text-xs text-siakad-primary mt-1 font-medium">Pertemuan selanjutnya: ke-{{ $nextPertemuanKe[$jadwal->id] ?? 1 }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @endif
                        @error('jadwal_kuliah_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pertemuan Ke -->
                    <div>
                        <label for="pertemuan_ke" class="block text-sm font-medium text-siakad-dark mb-2">Pertemuan Ke</label>
                        <input type="number" name="pertemuan_ke" id="pertemuan_ke" min="1" max="16" 
                            value="{{ old('pertemuan_ke', $nextPertemuanKe[$jadwalList->first()?->id] ?? 1) }}"
                            class="w-full px-4 py-3 rounded-lg border border-siakad-light bg-white text-siakad-dark focus:border-siakad-primary focus:ring-2 focus:ring-siakad-primary/20 transition" required>
                        @error('pertemuan_ke')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal -->
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-siakad-dark mb-2">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" 
                            value="{{ old('tanggal', date('Y-m-d')) }}"
                            class="w-full px-4 py-3 rounded-lg border border-siakad-light bg-white text-siakad-dark focus:border-siakad-primary focus:ring-2 focus:ring-siakad-primary/20 transition" required>
                        @error('tanggal')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Materi -->
                    <div>
                        <label for="materi" class="block text-sm font-medium text-siakad-dark mb-2">Materi (Opsional)</label>
                        <input type="text" name="materi" id="materi" 
                            value="{{ old('materi') }}"
                            placeholder="Topik atau materi yang dibahas"
                            class="w-full px-4 py-3 rounded-lg border border-siakad-light bg-white text-siakad-dark placeholder:text-siakad-secondary focus:border-siakad-primary focus:ring-2 focus:ring-siakad-primary/20 transition">
                        @error('materi')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 px-6 py-3 bg-siakad-primary text-white rounded-lg font-medium hover:bg-siakad-primary/90 transition min-h-[44px]">
                            Buat & Input Presensi
                        </button>
                        <a href="{{ route('dosen.presensi.kelas', $kelas) }}" class="px-6 py-3 border border-siakad-light text-siakad-secondary rounded-lg font-medium hover:bg-siakad-light/50 transition">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Info Kelas -->
            <div class="card-saas p-5">
                <h3 class="font-semibold text-siakad-dark mb-4 text-sm">Info Kelas</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-siakad-secondary">Mahasiswa</span>
                        <span class="text-sm font-semibold text-siakad-dark">{{ $kelas->krsDetail->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-siakad-secondary">SKS</span>
                        <span class="text-sm font-semibold text-siakad-dark">{{ $kelas->mataKuliah->sks }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-siakad-secondary">Kapasitas</span>
                        <span class="text-sm font-semibold text-siakad-dark">{{ $kelas->krsDetail->count() }}/{{ $kelas->kapasitas }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-siakad-secondary">Semester</span>
                        <span class="text-sm font-semibold text-siakad-dark">{{ $kelas->mataKuliah->semester }}</span>
                    </div>
                </div>
            </div>

            <!-- Riwayat Pertemuan -->
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark text-sm">Riwayat Pertemuan</h3>
                </div>
                @php
                    $pertemuanTerakhir = \App\Models\Pertemuan::byKelas($kelas->id)
                        ->orderBy('pertemuan_ke', 'desc')
                        ->take(5)
                        ->get();
                @endphp
                @if($pertemuanTerakhir->isEmpty())
                <div class="p-5 text-center">
                    <div class="w-12 h-12 rounded-full bg-siakad-light/50 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="text-siakad-secondary text-sm">Belum ada pertemuan</p>
                    <p class="text-xs text-siakad-secondary mt-1">Ini akan menjadi pertemuan pertama</p>
                </div>
                @else
                <div class="divide-y divide-siakad-light max-h-64 overflow-y-auto">
                    @foreach($pertemuanTerakhir as $pertemuan)
                    <div class="p-4 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-siakad-primary/10 text-siakad-primary flex items-center justify-center font-bold text-sm flex-shrink-0">
                            {{ $pertemuan->pertemuan_ke }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-siakad-dark truncate">{{ $pertemuan->materi ?? 'Pertemuan ' . $pertemuan->pertemuan_ke }}</p>
                            <p class="text-xs text-siakad-secondary">{{ $pertemuan->tanggal->format('d M Y') }}</p>
                        </div>
                        <span class="text-xs bg-siakad-primary/10 text-siakad-primary px-2 py-1 rounded-full">
                            {{ $pertemuan->presensi->count() }} hadir
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
