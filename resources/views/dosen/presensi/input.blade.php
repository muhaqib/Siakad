<x-app-layout>
    <x-slot name="header">
        Input Presensi - Pertemuan {{ $pertemuan->pertemuan_ke }}
    </x-slot>

    <!-- Breadcrumb -->
    <div class="mb-5">
        <a href="{{ route('dosen.presensi.kelas', $kelas) }}" class="inline-flex items-center gap-2 text-siakad-secondary hover:text-siakad-primary transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke {{ $kelas->mataKuliah->nama_mk }}
        </a>
    </div>

    <!-- Pertemuan Info -->
    <div class="card-saas p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-xl bg-siakad-primary text-white flex items-center justify-center font-bold text-xl flex-shrink-0">
                    {{ $pertemuan->pertemuan_ke }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-siakad-dark">Pertemuan {{ $pertemuan->pertemuan_ke }}</h1>
                    <p class="text-sm text-siakad-secondary mt-0.5">{{ $kelas->mataKuliah->nama_mk }} • Kelas {{ $kelas->nama_kelas }}</p>
                    <p class="text-xs text-siakad-secondary mt-1">{{ $pertemuan->tanggal->format('l, d F Y') }} • {{ $pertemuan->materi ?? 'Belum ada materi' }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-siakad-primary">{{ $mahasiswaList->count() }}</p>
                <p class="text-sm text-siakad-secondary">Mahasiswa</p>
            </div>
        </div>
    </div>

    <form action="{{ route('dosen.presensi.store', $pertemuan) }}" method="POST">
        @csrf
        
        <div class="card-saas overflow-hidden mb-6">
            <!-- Quick Actions -->
            <div class="p-4 bg-siakad-light/50 border-b border-siakad-light flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-siakad-secondary">Pilih status kehadiran untuk setiap mahasiswa</p>
                <div class="flex gap-2">
                    <button type="button" onclick="setAllStatus('hadir')" class="px-3 py-1.5 text-xs font-medium bg-siakad-primary/10 text-siakad-primary rounded-lg hover:bg-siakad-primary/20 transition">
                        Semua Hadir
                    </button>
                    <button type="button" onclick="setAllStatus('alpa')" class="px-3 py-1.5 text-xs font-medium bg-siakad-light text-siakad-secondary rounded-lg hover:bg-siakad-primary/10 transition">
                        Semua Alpa
                    </button>
                </div>
            </div>

            @if($mahasiswaList->isEmpty())
            <div class="p-10 text-center">
                <div class="w-16 h-16 rounded-full bg-siakad-light/50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <p class="text-siakad-secondary">Belum ada mahasiswa yang terdaftar di kelas ini</p>
            </div>
            @else
            <div class="divide-y divide-siakad-light">
                @foreach($mahasiswaList as $mahasiswa)
                @php
                    $existing = $existingPresensi[$mahasiswa->id] ?? null;
                    $currentStatus = old("presensi.{$mahasiswa->id}", $existing?->status ?? 'hadir');
                @endphp
                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-siakad-light/30 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-siakad-primary text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($mahasiswa->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-siakad-dark">{{ $mahasiswa->user->name }}</p>
                            <p class="text-sm text-siakad-secondary">{{ $mahasiswa->nim }}</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 flex-wrap sm:flex-nowrap">
                        <label class="cursor-pointer">
                            <input type="radio" name="presensi[{{ $mahasiswa->id }}]" value="hadir" class="peer hidden" {{ $currentStatus === 'hadir' ? 'checked' : '' }}>
                            <span class="px-3 py-2 rounded-lg text-sm font-medium border transition peer-checked:bg-siakad-primary peer-checked:text-white peer-checked:border-siakad-primary bg-white text-siakad-secondary border-siakad-light hover:border-siakad-primary/50">
                                Hadir
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="presensi[{{ $mahasiswa->id }}]" value="sakit" class="peer hidden" {{ $currentStatus === 'sakit' ? 'checked' : '' }}>
                            <span class="px-3 py-2 rounded-lg text-sm font-medium border transition peer-checked:bg-siakad-primary/70 peer-checked:text-white peer-checked:border-siakad-primary/70 bg-white text-siakad-secondary border-siakad-light hover:border-siakad-primary/50">
                                Sakit
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="presensi[{{ $mahasiswa->id }}]" value="izin" class="peer hidden" {{ $currentStatus === 'izin' ? 'checked' : '' }}>
                            <span class="px-3 py-2 rounded-lg text-sm font-medium border transition peer-checked:bg-siakad-primary/50 peer-checked:text-white peer-checked:border-siakad-primary/50 bg-white text-siakad-secondary border-siakad-light hover:border-siakad-primary/50">
                                Izin
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="presensi[{{ $mahasiswa->id }}]" value="alpa" class="peer hidden" {{ $currentStatus === 'alpa' ? 'checked' : '' }}>
                            <span class="px-3 py-2 rounded-lg text-sm font-medium border transition peer-checked:bg-siakad-secondary peer-checked:text-white peer-checked:border-siakad-secondary bg-white text-siakad-secondary border-siakad-light hover:border-siakad-secondary/50">
                                Alpa
                            </span>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        @if($mahasiswaList->isNotEmpty())
        <div class="flex gap-3">
            <button type="submit" class="px-6 py-3 bg-siakad-primary text-white rounded-lg font-medium hover:bg-siakad-primary/90 transition flex items-center gap-2 min-h-[44px]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Simpan Presensi
            </button>
            <a href="{{ route('dosen.presensi.kelas', $kelas) }}" class="px-6 py-3 border border-siakad-light text-siakad-secondary rounded-lg font-medium hover:bg-siakad-light/50 transition">
                Batal
            </a>
        </div>
        @endif
    </form>

    <script>
        function setAllStatus(status) {
            document.querySelectorAll(`input[value="${status}"]`).forEach(input => {
                input.checked = true;
            });
        }
    </script>
</x-app-layout>
