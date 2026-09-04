<x-app-layout>
    <x-slot name="header">Kehadiran Mengajar</x-slot>

    @php
        $now = now();
        
        // Find next/current class
        $nextClass = null;
        $ongoingClass = null;
        $classStatus = 'none';
        $minutesUntilStart = 0;
        
        foreach($jadwalHariIni as $jadwal) {
            // jam_mulai and jam_selesai are Carbon objects due to datetime cast
            $jadwalStartTime = $jadwal->jam_mulai->format('H:i');
            $jadwalEndTime = $jadwal->jam_selesai->format('H:i');
            $currentTime = $now->format('H:i');
            
            // Check if ongoing
            if ($currentTime >= $jadwalStartTime && $currentTime <= $jadwalEndTime) {
                $ongoingClass = $jadwal;
                $classStatus = 'ongoing';
                break;
            }
            // Check if upcoming
            if ($currentTime < $jadwalStartTime && !$nextClass) {
                $nextClass = $jadwal;
                $classStatus = 'upcoming';
                $minutesUntilStart = $now->diffInMinutes($jadwal->jam_mulai->copy()->setDate($now->year, $now->month, $now->day), false);
            }
        }
        
        $featuredClass = $ongoingClass ?? $nextClass;
        
        // Stats calculation
        $totalKehadiran = ($stats['hadir'] ?? 0) + ($stats['izin'] ?? 0) + ($stats['sakit'] ?? 0) + ($stats['tugas'] ?? 0) + ($stats['alpa'] ?? 0);
        $percentHadir = $totalKehadiran > 0 ? round((($stats['hadir'] ?? 0) / $totalKehadiran) * 100) : 0;
        $percentIzin = $totalKehadiran > 0 ? round((($stats['izin'] ?? 0) / $totalKehadiran) * 100) : 0;
        $percentSakit = $totalKehadiran > 0 ? round((($stats['sakit'] ?? 0) / $totalKehadiran) * 100) : 0;
        $percentTugas = $totalKehadiran > 0 ? round((($stats['tugas'] ?? 0) / $totalKehadiran) * 100) : 0;
        $percentAlpa = $totalKehadiran > 0 ? round((($stats['alpa'] ?? 0) / $totalKehadiran) * 100) : 0;
    @endphp

    <!-- Two Cards Grid with Equal Height -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
        <!-- Card 1: Kelas Berikutnya -->
        <div class="lg:col-span-2">
            @if($featuredClass)
            @php
                $isAbsen = in_array($featuredClass->id, $kehadiranHariIni);
                $kehadiranData = \App\Models\KehadiranDosen::where('dosen_id', $dosen->id)
                    ->where('jadwal_kuliah_id', $featuredClass->id)
                    ->whereDate('tanggal', now())
                    ->first();
                $isCheckedOut = $kehadiranData && $kehadiranData->jam_keluar;
                
                // Time calculations
                $jamMulai = $featuredClass->jam_mulai;
                $jamSelesai = $featuredClass->jam_selesai;
                $durasiTotal = $jamMulai->diffInMinutes($jamSelesai);
                $menitBerjalan = $classStatus === 'ongoing' ? max(0, $now->diffInMinutes($jamMulai->copy()->setDate($now->year, $now->month, $now->day))) : 0;
                $progressPersen = $durasiTotal > 0 ? min(100, ($menitBerjalan / $durasiTotal) * 100) : 0;
                $menitTersisa = max(0, $durasiTotal - $menitBerjalan);
                
                // Absen window (10 mins before start until class end)
                $absenAktifSampai = $jamSelesai->format('H:i');
                $canAbsen = $classStatus === 'ongoing' || ($minutesUntilStart <= 10 && $minutesUntilStart >= -$durasiTotal);
                
                // Progress label
                if ($classStatus === 'ongoing') {
                    if ($menitBerjalan < 5) {
                        $progressLabel = 'Baru dimulai';
                    } elseif ($menitTersisa <= 10) {
                        $progressLabel = 'Hampir selesai';
                    } else {
                        $progressLabel = $menitBerjalan . ' menit berjalan dari ' . $durasiTotal . ' menit';
                    }
                } else {
                    $progressLabel = 'Kelas dimulai ' . abs($minutesUntilStart) . ' menit lagi';
                }
                
                // Progress bar color
                $progressBarColor = ($menitTersisa <= 10 && $classStatus === 'ongoing') ? 'bg-amber-500' : 'bg-siakad-primary';
            @endphp
            <div class="card-saas p-6 h-full border-l-4 flex flex-col {{ $classStatus === 'ongoing' ? 'border-l-emerald-500' : 'border-l-siakad-primary' }}">
                
                <!-- Header: Status Badge + Time -->
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium {{ $classStatus === 'ongoing' ? 'bg-emerald-100 text-emerald-700' : 'bg-siakad-light text-siakad-primary' }}">
                        @if($classStatus === 'ongoing')
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Sedang Berlangsung
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Akan Dimulai
                        @endif
                    </span>
                    <span class="text-base font-medium text-siakad-dark">{{ $jamMulai->format('H:i') }} – {{ $jamSelesai->format('H:i') }}</span>
                </div>
                
                <!-- Course Title -->
                <h2 class="text-2xl font-bold text-siakad-dark mb-4">{{ $featuredClass->kelas->mataKuliah->nama_mk ?? '-' }}</h2>
                
                <!-- Teaching Context Row -->
                <div class="flex flex-col sm:flex-row flex-wrap items-start sm:items-center gap-2 sm:gap-4 py-3 px-4 bg-siakad-light/30 dark:bg-gray-700/50 rounded-lg mb-4 text-sm">
                    <div class="flex items-center gap-2 text-siakad-dark dark:text-white">
                        <svg class="w-4 h-4 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <span>Kelas {{ $featuredClass->kelas->nama_kelas }}</span>
                    </div>
                    <div class="hidden sm:block w-px h-4 bg-siakad-secondary/30"></div>
                    <div class="flex items-center gap-2 text-siakad-dark dark:text-white">
                        <svg class="w-4 h-4 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>{{ $featuredClass->ruangan ?? 'Ruang TBA' }}</span>
                    </div>
                    <div class="hidden sm:block w-px h-4 bg-siakad-secondary/30"></div>
                    <div class="flex items-center gap-2 text-siakad-dark dark:text-white">
                        <svg class="w-4 h-4 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>{{ $featuredClass->kelas->krsDetail()->count() }} Mahasiswa</span>
                    </div>
                </div>
                
                <!-- Attendance State + Action -->
                <div class="mt-auto pt-4 border-t border-siakad-light/50">
                    <div class="flex items-center justify-between">
                        <!-- Attendance State Box -->
                        <div class="flex items-center gap-3">
                            @if($isCheckedOut)
                                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-emerald-700">Kehadiran Tercatat</p>
                                    <p class="text-xs text-siakad-secondary">Masuk {{ substr($kehadiranData->jam_masuk, 0, 5) }} • Keluar {{ substr($kehadiranData->jam_keluar, 0, 5) }}</p>
                                </div>
                            @elseif($isAbsen)
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-blue-700">Absen Masuk Tercatat</p>
                                    <p class="text-xs text-siakad-secondary">Jam {{ substr($kehadiranData->jam_masuk, 0, 5) }} • Absen keluar aktif sampai {{ $absenAktifSampai }}</p>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full {{ $canAbsen ? 'bg-amber-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <svg class="w-5 h-5 {{ $canAbsen ? 'text-amber-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold {{ $canAbsen ? 'text-amber-700' : 'text-siakad-dark' }}">{{ $canAbsen ? 'Belum Absen' : 'Menunggu Waktu Absen' }}</p>
                                    <p class="text-xs text-siakad-secondary">{{ $canAbsen ? 'Absen aktif sampai ' . $absenAktifSampai : 'Absen bisa diisi 10 menit sebelum kelas' }}</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Primary Action with Context -->
                        <div class="text-right">
                            @if($isCheckedOut)
                                <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Selesai
                                </span>
                            @elseif($isAbsen)
                                <form action="{{ route('dosen.kehadiran.checkout', $kehadiranData) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-5 py-2.5 bg-siakad-primary text-white rounded-lg text-sm font-semibold hover:bg-siakad-primary/90 transition min-h-[44px]">
                                        Absen Keluar
                                    </button>
                                    <p class="text-xs text-siakad-secondary mt-1.5">Isi saat meninggalkan kelas</p>
                                </form>
                            @elseif($canAbsen)
                                <button onclick="showModal({{ $featuredClass->id }}, '{{ $featuredClass->kelas->mataKuliah->nama_mk }}')" class="px-6 py-2.5 bg-siakad-primary text-white rounded-lg text-sm font-semibold hover:bg-siakad-primary/90 transition min-h-[44px] shadow-sm">
                                    Absen Masuk
                                </button>
                                <p class="text-xs text-siakad-secondary mt-1.5">Disarankan di 10 menit pertama</p>
                            @else
                                <button disabled class="px-5 py-2.5 bg-gray-200 text-gray-500 rounded-lg text-sm font-medium cursor-not-allowed min-h-[44px]">
                                    Absen Belum Aktif
                                </button>
                                <p class="text-xs text-siakad-secondary mt-1.5">Aktif 10 menit sebelum kelas</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="card-saas p-5 h-full flex flex-col items-center justify-center text-center">
                <div class="w-14 h-14 rounded-full bg-siakad-light flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <p class="font-semibold text-siakad-dark mb-1">Tidak Ada Kelas Mendatang</p>
                <p class="text-sm text-siakad-secondary">Semua jadwal hari ini sudah selesai.</p>
            </div>
            @endif
        </div>

        <!-- Card 2: Ringkasan -->
        <div class="lg:col-span-1">
            <div class="card-saas p-5 h-full flex flex-col">
                <h3 class="font-semibold text-siakad-dark mb-3 text-sm">Ringkasan Bulan Ini</h3>
                @if($totalKehadiran > 0)
                <div class="h-2.5 bg-siakad-light rounded-full overflow-hidden flex mb-3">
                    @if($percentHadir > 0)<div class="h-full bg-siakad-primary" style="width: {{ $percentHadir }}%"></div>@endif
                    @if($percentIzin > 0)<div class="h-full bg-siakad-primary/70" style="width: {{ $percentIzin }}%"></div>@endif
                    @if($percentSakit > 0)<div class="h-full bg-siakad-primary/50" style="width: {{ $percentSakit }}%"></div>@endif
                    @if($percentTugas > 0)<div class="h-full bg-siakad-primary/35" style="width: {{ $percentTugas }}%"></div>@endif
                    @if($percentAlpa > 0)<div class="h-full bg-siakad-secondary/50" style="width: {{ $percentAlpa }}%"></div>@endif
                </div>
                <div class="space-y-2 text-sm flex-1">
                    <div class="flex items-center justify-between"><div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-siakad-primary"></span><span class="text-siakad-dark">Hadir</span></div><span class="font-semibold">{{ $stats['hadir'] ?? 0 }} <span class="text-siakad-secondary font-normal">({{ $percentHadir }}%)</span></span></div>
                    <div class="flex items-center justify-between"><div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-siakad-primary/70"></span><span class="text-siakad-dark">Izin</span></div><span class="font-semibold">{{ $stats['izin'] ?? 0 }} <span class="text-siakad-secondary font-normal">({{ $percentIzin }}%)</span></span></div>
                    <div class="flex items-center justify-between"><div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-siakad-primary/50"></span><span class="text-siakad-dark">Sakit</span></div><span class="font-semibold">{{ $stats['sakit'] ?? 0 }} <span class="text-siakad-secondary font-normal">({{ $percentSakit }}%)</span></span></div>
                    <div class="flex items-center justify-between"><div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-siakad-primary/35"></span><span class="text-siakad-dark">Tugas</span></div><span class="font-semibold">{{ $stats['tugas'] ?? 0 }} <span class="text-siakad-secondary font-normal">({{ $percentTugas }}%)</span></span></div>
                    <div class="flex items-center justify-between"><div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-siakad-secondary/50"></span><span class="text-siakad-dark">Alpa</span></div><span class="font-semibold">{{ $stats['alpa'] ?? 0 }} <span class="text-siakad-secondary font-normal">({{ $percentAlpa }}%)</span></span></div>
                </div>
                <div class="mt-auto pt-3 border-siakad-light flex items-center justify-between">
                    <span class="text-sm font-medium text-siakad-dark">Total Kehadiran</span>
                    <span class="text-lg font-bold text-siakad-primary">{{ $totalKehadiran }}</span>
                </div>
                @else
                <div class="flex-1 flex items-center justify-center">
                    <p class="text-siakad-secondary text-sm">Belum ada data</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Section B: Jadwal Hari Ini -->
    <div class="card-saas mb-6 overflow-hidden">
        <div class="px-5 py-3 border-b border-siakad-light flex items-center justify-between">
            <h3 class="font-semibold text-siakad-dark">Jadwal Hari Ini</h3>
            <span class="text-sm text-siakad-secondary">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
        </div>
        
        @if($jadwalHariIni->isNotEmpty())
        <div class="divide-y divide-siakad-light/50">
            @foreach($jadwalHariIni as $jadwal)
            @php
                $jadwalStartTime = $jadwal->jam_mulai->format('H:i');
                $jadwalEndTime = $jadwal->jam_selesai->format('H:i');
                $currentTime = now()->format('H:i');
                $isOngoing = $currentTime >= $jadwalStartTime && $currentTime <= $jadwalEndTime;
                $isDone = $currentTime > $jadwalEndTime;
                $isNext = !$isDone && !$isOngoing && $jadwal->id === ($nextClass?->id ?? null);
                $isAbsenJadwal = in_array($jadwal->id, $kehadiranHariIni);
            @endphp
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-5 py-4 {{ $isOngoing ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : '' }} hover:bg-siakad-light/20 dark:hover:bg-gray-700/30 transition">
                <!-- Time -->
                <div class="flex items-center sm:block gap-2 sm:w-16 flex-shrink-0 text-left sm:text-center">
                    <p class="text-base font-bold {{ $isOngoing ? 'text-emerald-600 dark:text-emerald-400' : 'text-siakad-dark dark:text-white' }}">{{ $jadwalStartTime }}</p>
                    <span class="sm:hidden text-siakad-secondary">-</span>
                    <p class="text-xs sm:text-xs text-siakad-secondary dark:text-gray-400">{{ $jadwalEndTime }}</p>
                </div>
                
                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-siakad-dark text-sm truncate">{{ $jadwal->kelas->mataKuliah->nama_mk ?? '-' }}</p>
                    <p class="text-xs text-siakad-secondary">Kelas {{ $jadwal->kelas->nama_kelas }} • {{ $jadwal->ruangan ?? '-' }}</p>
                </div>
                
                <!-- Status & Action Flex Container -->
                <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto mt-2 sm:mt-0">
                    <!-- Status -->
                    <div class="flex-shrink-0">
                        @if($isDone)
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-siakad-light text-siakad-secondary">Selesai</span>
                        @elseif($isOngoing)
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">● Berlangsung</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-siakad-light text-siakad-primary">Mendatang</span>
                        @endif
                    </div>
                    
                    <!-- Action -->
                    <div class="flex-shrink-0 text-right">
                        @if($isAbsenJadwal)
                            <span class="text-xs font-medium text-emerald-600">✓ Sudah Absen</span>
                        @elseif($isOngoing || $isNext)
                            <button onclick="showModal({{ $jadwal->id }}, '{{ $jadwal->kelas->mataKuliah->nama_mk }}')" class="px-4 py-2 bg-siakad-primary text-white rounded-lg text-xs font-bold hover:bg-siakad-primary/90 transition shadow-sm w-full sm:w-auto">
                                Absen
                            </button>
                        @else
                            <span class="text-xs text-siakad-secondary">-</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-8 text-center">
            <p class="text-siakad-secondary text-sm">Tidak ada jadwal mengajar hari ini</p>
        </div>
        @endif
    </div>

    <!-- Section D: Riwayat -->
    <div id="riwayat" class="card-saas overflow-hidden">
        <div class="px-5 py-4 border-b border-siakad-light">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <h3 class="font-semibold text-siakad-dark dark:text-white">Riwayat Kehadiran</h3>
                <form method="GET" class="flex flex-col md:flex-row md:items-center gap-3 w-full lg:w-auto">
                    <div class="relative w-full lg:w-48">
                        <input type="text" name="search" placeholder="Cari mata kuliah..." class="input-saas w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-gray-900 border-siakad-light dark:border-gray-700 text-siakad-dark dark:text-white" value="{{ request('search') }}">
                        <svg class="w-4 h-4 text-siakad-secondary dark:text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <div class="flex gap-2 w-full lg:w-auto">
                        <select name="month" class="input-saas flex-1 text-sm py-2 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->locale('id')->monthName }}</option>
                            @endfor
                        </select>
                        <select name="year" class="input-saas flex-1 text-sm py-2 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            @for($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="w-full lg:w-auto px-4 py-2 bg-siakad-primary text-white rounded-lg text-sm font-medium hover:bg-siakad-primary/90 transition min-h-[40px]">Terapkan</button>
                </form>
            </div>
        </div>
        
        <!-- Desktop Table -->
        <table class="hidden md:table w-full table-saas">
            <thead>
                <tr class="bg-siakad-light/30 dark:bg-gray-900">
                    <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Tanggal</th>
                    <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Mata Kuliah</th>
                    <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Jam Masuk</th>
                    <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Jam Keluar</th>
                    <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $index => $r)
                <tr class="{{ $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-siakad-light/10 dark:bg-gray-700/30' }} border-b border-siakad-light/30 dark:border-gray-700 hover:bg-siakad-light/30 dark:hover:bg-gray-700 transition">
                    <td class="py-4 px-5 text-sm text-siakad-dark dark:text-white font-medium">{{ $r->tanggal->locale('id')->isoFormat('D MMM YYYY') }}</td>
                    <td class="py-4 px-5 text-sm text-siakad-dark dark:text-white">{{ $r->jadwalKuliah?->kelas?->mataKuliah?->nama_mk ?? '-' }}</td>
                    <td class="py-4 px-5 text-center text-sm text-siakad-secondary dark:text-gray-400">{{ $r->jam_masuk ? substr($r->jam_masuk, 0, 5) : '-' }}</td>
                    <td class="py-4 px-5 text-center text-sm text-siakad-secondary dark:text-gray-400">{{ $r->jam_keluar ? substr($r->jam_keluar, 0, 5) : '-' }}</td>
                    <td class="py-4 px-5 text-center"><span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $r->status_color }}-100 text-{{ $r->status_color }}-700 dark:bg-gray-800 dark:text-{{ $r->status_color }}-400 border dark:border-{{ $r->status_color }}-400/20">{{ $r->status_label }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-10 text-center text-siakad-secondary">Belum ada data kehadiran</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- Mobile Card View -->
        <div class="md:hidden divide-y divide-siakad-light dark:divide-gray-700">
            @forelse($riwayat as $r)
            <div class="p-4 bg-white dark:bg-gray-800">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <span class="text-xs font-medium text-siakad-secondary dark:text-gray-400">{{ $r->tanggal->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</span>
                        <h4 class="font-bold text-siakad-dark dark:text-white text-sm mt-0.5">{{ $r->jadwalKuliah?->kelas?->mataKuliah?->nama_mk ?? '-' }}</h4>
                    </div>
                    <span class="px-2 py-1 text-[10px] font-medium rounded-full bg-{{ $r->status_color }}-100 text-{{ $r->status_color }}-700 dark:bg-gray-800 dark:text-{{ $r->status_color }}-400">{{ $r->status_label }}</span>
                </div>
                <div class="flex items-center gap-4 text-xs text-siakad-secondary dark:text-gray-400 bg-siakad-light/30 dark:bg-gray-700/30 p-2 rounded-lg">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        <span>Masuk: <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $r->jam_masuk ? substr($r->jam_masuk, 0, 5) : '-' }}</span></span>
                    </div>
                    <div class="w-px h-3 bg-siakad-light dark:bg-gray-600"></div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        <span>Keluar: <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $r->jam_keluar ? substr($r->jam_keluar, 0, 5) : '-' }}</span></span>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-siakad-secondary dark:text-gray-400 text-sm">Belum ada data kehadiran</div>
            @endforelse
        </div>
    </div>

    <!-- Modal -->
    <div id="absenModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-siakad-dark dark:text-white mb-1">Absen Kehadiran</h3>
            <p id="modalMatkul" class="text-sm text-siakad-secondary dark:text-gray-400 mb-5"></p>
            <form action="{{ route('dosen.kehadiran.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="jadwal_kuliah_id" id="modalJadwalId">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-300 mb-1">Jam Masuk</label>
                        <input type="time" name="jam_masuk" value="{{ now()->format('H:i') }}" class="input-saas w-full text-sm py-2.5 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="input-saas w-full text-sm py-2.5 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            @foreach(\App\Models\KehadiranDosen::getStatusList() as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-300 mb-1">Keterangan (Opsional)</label>
                    <textarea name="keterangan" rows="2" class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Catatan..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="hideModal()" class="px-4 py-2 border border-siakad-light dark:border-gray-700 text-siakad-dark dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-siakad-light dark:hover:bg-gray-700 transition min-h-[44px]">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-siakad-primary text-white rounded-lg text-sm font-semibold hover:bg-siakad-primary/90 transition min-h-[44px]">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showModal(id, matkul) {
            document.getElementById('modalJadwalId').value = id;
            document.getElementById('modalMatkul').textContent = matkul;
            document.getElementById('absenModal').classList.remove('hidden');
            document.getElementById('absenModal').classList.add('flex');
        }
        function hideModal() {
            document.getElementById('absenModal').classList.add('hidden');
            document.getElementById('absenModal').classList.remove('flex');
        }
    </script>
</x-app-layout>
