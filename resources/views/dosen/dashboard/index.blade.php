<x-app-layout>
    <x-slot name="header">
        Dashboard Dosen
    </x-slot>

    <!-- Greeting -->
    <div class="mb-8">
        @php
            $hour = now()->hour;
            if ($hour < 11) $greeting = 'Selamat Pagi';
            elseif ($hour < 15) $greeting = 'Selamat Siang';
            elseif ($hour < 18) $greeting = 'Selamat Sore';
            else $greeting = 'Selamat Malam';
            
            if ($hour < 11) { $emoji = '🌅'; }
            elseif ($hour < 15) { $emoji = '☀️'; }
            elseif ($hour < 18) { $emoji = '🌤️'; }
            else { $emoji = '🌙'; }
        @endphp
        <h1 class="text-2xl font-semibold text-siakad-dark hidden md:block">{{ $greeting }}, {{ explode(' ', $dosen->user->name)[0] }}! {{ $emoji }}</h1>
        <p class="text-siakad-secondary text-sm mt-1 hidden md:block">Berikut ringkasan aktivitas mengajar Anda</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card-saas p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-siakad-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark">{{ $totalKelas }}</p>
                    <p class="text-xs text-siakad-secondary">Kelas Diampu</p>
                </div>
            </div>
        </div>
        <div class="card-saas p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-siakad-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark">{{ $totalMahasiswa }}</p>
                    <p class="text-xs text-siakad-secondary">Total Mahasiswa</p>
                </div>
            </div>
        </div>
        <div class="card-saas p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-siakad-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark">{{ $totalPertemuan }}</p>
                    <p class="text-xs text-siakad-secondary">Pertemuan</p>
                </div>
            </div>
        </div>
        <div class="card-saas p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-siakad-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark">{{ $mahasiswaBimbingan->count() }}</p>
                    <p class="text-xs text-siakad-secondary">Mahasiswa PA</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Jadwal Hari Ini (Context Aware Hero Card) -->
            @if($kelasHariIni->isNotEmpty())
                @php 
                    // Get the very first class of the day (assuming sorted by time)
                    $upcomingClass = $kelasHariIni->first();
                    $jadwal = $upcomingClass->jadwal->firstWhere('hari', $hariIni);
                @endphp
                <div class="card-saas p-6 border-l-4 border-l-siakad-primary bg-gradient-to-r from-white to-siakad-light/30 dark:from-gray-800 dark:to-gray-800/50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-1 rounded bg-siakad-primary text-white text-xs font-bold uppercase tracking-wide">Kelas Hari Ini</span>
                                <span class="text-sm font-semibold text-siakad-primary">{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</span>
                            </div>
                            <h2 class="text-2xl font-bold text-siakad-dark dark:text-white mb-1">{{ $upcomingClass->mataKuliah->nama_mk }}</h2>
                            <p class="text-siakad-secondary dark:text-gray-300">Kelas {{ $upcomingClass->nama_kelas }} • {{ $upcomingClass->krsDetail->count() }} Mahasiswa • Ruang {{ $jadwal->ruangan ?? '-' }}</p>
                        </div>
                        <a href="{{ route('dosen.presensi.kelas', $upcomingClass) }}" class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-4 bg-siakad-primary text-white text-lg font-bold rounded-xl shadow-lg hover:bg-siakad-primary/90 hover:scale-105 transition transform w-full md:w-auto">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            Buka Presensi
                        </a>
                    </div>
                </div>

                <!-- List of other classes if any -->
                @if($kelasHariIni->count() > 1)
                <div class="card-saas overflow-hidden mt-6">
                    <div class="px-6 py-4 border-b border-siakad-light flex items-center justify-between">
                         <h3 class="font-semibold text-siakad-dark">Jadwal Lainnya Hari Ini</h3>
                    </div>
                    <div class="divide-y divide-siakad-light">
                        @foreach($kelasHariIni->skip(1) as $kelas)
                        @php $jadwal = $kelas->jadwal->firstWhere('hari', $hariIni); @endphp
                        <div class="p-4 flex flex-row items-center gap-4 hover:bg-siakad-light/30 transition">
                            <div class="flex flex-col items-center justify-center bg-siakad-light/30 p-2 rounded w-16 h-16 flex-shrink-0">
                                <p class="text-sm font-bold text-siakad-primary mb-0.5">{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}</p>
                                <p class="text-[10px] text-siakad-secondary leading-none">{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-siakad-dark truncate">{{ $kelas->mataKuliah->nama_mk }}</h4>
                                <div class="flex items-center gap-2 text-xs text-siakad-secondary mt-0.5">
                                    <span class="font-medium bg-siakad-light px-1.5 py-0.5 rounded text-siakad-dark">{{ $kelas->nama_kelas }}</span>
                                    <span>•</span>
                                    <span>{{ $kelas->krsDetail->count() }} Mhs</span>
                                </div>
                            </div>
                            <a href="{{ route('dosen.presensi.kelas', $kelas) }}" class="p-2 bg-siakad-light/50 text-siakad-primary hover:bg-siakad-primary/10 rounded-lg group" title="Buka Presensi">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @else
            <!-- Empty State for Today -->
            <div class="card-saas p-8 text-center flex flex-col items-center justify-center min-h-[200px]">
                <div class="w-16 h-16 rounded-full bg-siakad-light/50 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-lg font-semibold text-siakad-dark mb-1">Tidak Ada Jadwal Hari Ini</h3>
                <p class="text-siakad-secondary text-sm">Nikmati hari Anda, Pak/Bu Dosen! ☕</p>
                <a href="{{ route('dosen.presensi.index') }}" class="mt-4 text-siakad-primary hover:underline text-sm font-medium">Lihat Semua Jadwal</a>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Pending KRS -->
            @if($pendingKrs > 0)
            <div class="card-saas p-6 border-l-4 border-l-amber-500">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-siakad-dark">KRS Menunggu</p>
                        <p class="text-sm text-siakad-secondary">{{ $pendingKrs }} mahasiswa</p>
                    </div>
                </div>
                <a href="{{ route('dosen.bimbingan.krs-approval') }}" class="block w-full text-center px-4 py-2 bg-siakad-primary text-white rounded-lg text-sm font-medium hover:bg-siakad-primary/90 transition">
                    Review Sekarang
                </a>
            </div>
            @endif

            <!-- Kelas List -->
            <div class="card-saas overflow-hidden">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Kelas Diampu</h3>
                </div>
                @if($kelasList->isEmpty())
                <div class="p-6 text-center text-siakad-secondary text-sm">
                    Belum ada kelas
                </div>
                @else
                <div class="divide-y divide-siakad-light max-h-64 overflow-y-auto">
                    @foreach($kelasList as $kelas)
                    <a href="{{ route('dosen.presensi.kelas', $kelas) }}" class="block p-4 hover:bg-siakad-light/30 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-siakad-dark">{{ $kelas->mataKuliah->nama_mk }}</p>
                                <p class="text-xs text-siakad-secondary">{{ $kelas->mataKuliah->kode_mk }} • Kelas {{ $kelas->nama_kelas }}</p>
                            </div>
                            <span class="text-xs bg-siakad-light text-siakad-secondary px-2 py-1 rounded-full">{{ $kelas->krsDetail->count() }} mhs</span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Progress Nilai (below Kelas Diampu) -->
            <div class="card-saas overflow-hidden">
                <div class="px-6 py-4 border-b border-siakad-light flex items-center justify-between">
                    <h3 class="font-semibold text-siakad-dark">Progress Input Nilai</h3>
                    <a href="{{ route('dosen.penilaian.index') }}" class="text-sm text-siakad-primary hover:underline">Input Nilai</a>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-siakad-secondary">{{ $nilaiDiinput }} dari {{ $totalNilai }} nilai telah diinput</span>
                        <span class="text-sm font-bold text-siakad-primary">{{ $persentaseNilai }}%</span>
                    </div>
                    <div class="h-3 bg-siakad-light rounded-full overflow-hidden">
                        <div class="h-full bg-siakad-primary rounded-full transition-all" style="width: {{ $persentaseNilai }}%"></div>
                    </div>
                    
                    @if($recentNilai->isNotEmpty())
                    <div class="mt-6">
                        <p class="text-xs font-semibold text-siakad-secondary uppercase mb-3">Nilai Terbaru Diinput</p>
                        <div class="space-y-2">
                            @foreach($recentNilai as $nilai)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-siakad-primary/10 text-siakad-primary flex items-center justify-center text-xs font-bold">
                                        {{ strtoupper(substr($nilai->mahasiswa->user->name, 0, 1)) }}
                                    </div>
                                    <span class="text-siakad-dark">{{ $nilai->mahasiswa->user->name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-siakad-secondary">{{ $nilai->kelas->mataKuliah->kode_mk }}</span>
                                    <span class="font-bold text-siakad-primary">{{ $nilai->nilai_huruf }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions (Full Width Horizontal) -->
    <div class="bg-siakad-dark rounded-xl p-6 text-white">
        <h3 class="font-semibold mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('dosen.penilaian.index') }}" class="flex items-center gap-3 p-4 bg-white/10 rounded-lg hover:bg-white/20 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span class="text-sm">Input Nilai</span>
            </a>
            <a href="{{ route('dosen.presensi.index') }}" class="flex items-center gap-3 p-4 bg-white/10 rounded-lg hover:bg-white/20 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span class="text-sm">Kelola Presensi</span>
            </a>
            <a href="{{ route('dosen.bimbingan.index') }}" class="flex items-center gap-3 p-4 bg-white/10 rounded-lg hover:bg-white/20 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span class="text-sm">Mahasiswa Bimbingan</span>
            </a>
        </div>
    </div>
</x-app-layout>
