<x-app-layout>
    <x-slot name="header">Kehadiran Dosen</x-slot>

    @php
        $totalKehadiran = ($stats['hadir'] ?? 0) + ($stats['izin'] ?? 0) + ($stats['sakit'] ?? 0) + ($stats['tugas'] ?? 0) + ($stats['alpa'] ?? 0);
        $percentHadir = $totalKehadiran > 0 ? round((($stats['hadir'] ?? 0) / $totalKehadiran) * 100) : 0;
        $percentIzin = $totalKehadiran > 0 ? round((($stats['izin'] ?? 0) / $totalKehadiran) * 100) : 0;
        $percentSakit = $totalKehadiran > 0 ? round((($stats['sakit'] ?? 0) / $totalKehadiran) * 100) : 0;
        $percentTugas = $totalKehadiran > 0 ? round((($stats['tugas'] ?? 0) / $totalKehadiran) * 100) : 0;
        $percentAlpa = $totalKehadiran > 0 ? round((($stats['alpa'] ?? 0) / $totalKehadiran) * 100) : 0;
    @endphp

    <!-- Two Cards Grid with Equal Height -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Card 1: Absensi Hari Ini (Widget) -->
        <div class="lg:col-span-2">
            @include('dosen.dashboard.partials.absensi')
        </div>

        <!-- Card 2: Ringkasan Bulan Ini -->
        <div class="lg:col-span-1">
            <div class="card-saas p-5 h-full flex flex-col dark:bg-gray-800">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-siakad-dark dark:text-white text-sm">Ringkasan Bulan Ini</h3>
                    <span class="text-xs text-siakad-secondary dark:text-gray-400 font-medium">
                        {{ \Carbon\Carbon::create()->month($month)->locale('id')->monthName }} {{ $year }}
                    </span>
                </div>

                @if($totalKehadiran > 0)
                <div class="h-2.5 bg-siakad-light dark:bg-gray-700 rounded-full overflow-hidden flex mb-4">
                    @if($percentHadir > 0)<div class="h-full bg-emerald-500" style="width: {{ $percentHadir }}%" title="Hadir: {{ $percentHadir }}%"></div>@endif
                    @if($percentIzin > 0)<div class="h-full bg-blue-500" style="width: {{ $percentIzin }}%" title="Izin: {{ $percentIzin }}%"></div>@endif
                    @if($percentSakit > 0)<div class="h-full bg-amber-500" style="width: {{ $percentSakit }}%" title="Sakit: {{ $percentSakit }}%"></div>@endif
                    @if($percentTugas > 0)<div class="h-full bg-purple-500" style="width: {{ $percentTugas }}%" title="Tugas: {{ $percentTugas }}%"></div>@endif
                    @if($percentAlpa > 0)<div class="h-full bg-rose-500" style="width: {{ $percentAlpa }}%" title="Alpa: {{ $percentAlpa }}%"></div>@endif
                </div>

                <div class="space-y-2 text-sm flex-1">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="text-siakad-dark dark:text-gray-300">Hadir</span>
                        </div>
                        <span class="font-semibold text-siakad-dark dark:text-white">{{ $stats['hadir'] ?? 0 }} <span class="text-siakad-secondary dark:text-gray-400 font-normal">({{ $percentHadir }}%)</span></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="text-siakad-dark dark:text-gray-300">Izin</span>
                        </div>
                        <span class="font-semibold text-siakad-dark dark:text-white">{{ $stats['izin'] ?? 0 }} <span class="text-siakad-secondary dark:text-gray-400 font-normal">({{ $percentIzin }}%)</span></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="text-siakad-dark dark:text-gray-300">Sakit</span>
                        </div>
                        <span class="font-semibold text-siakad-dark dark:text-white">{{ $stats['sakit'] ?? 0 }} <span class="text-siakad-secondary dark:text-gray-400 font-normal">({{ $percentSakit }}%)</span></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            <span class="text-siakad-dark dark:text-gray-300">Tugas Luar</span>
                        </div>
                        <span class="font-semibold text-siakad-dark dark:text-white">{{ $stats['tugas'] ?? 0 }} <span class="text-siakad-secondary dark:text-gray-400 font-normal">({{ $percentTugas }}%)</span></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <span class="text-siakad-dark dark:text-gray-300">Alpa</span>
                        </div>
                        <span class="font-semibold text-siakad-dark dark:text-white">{{ $stats['alpa'] ?? 0 }} <span class="text-siakad-secondary dark:text-gray-400 font-normal">({{ $percentAlpa }}%)</span></span>
                    </div>
                </div>

                <div class="mt-auto pt-3 border-t border-siakad-light dark:border-gray-700 flex items-center justify-between">
                    <span class="text-sm font-medium text-siakad-dark dark:text-gray-300">Total Absensi</span>
                    <span class="text-lg font-bold text-siakad-primary dark:text-siakad-light">{{ $totalKehadiran }}</span>
                </div>
                @else
                <div class="flex-1 flex flex-col items-center justify-center py-6 text-center">
                    <div class="w-10 h-10 rounded-full bg-siakad-light/50 dark:bg-gray-700 flex items-center justify-center mb-2">
                        <svg class="w-5 h-5 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <p class="text-siakad-secondary dark:text-gray-400 text-sm">Belum ada data kehadiran pada bulan ini</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Section: Riwayat Kehadiran -->
    <div id="riwayat" class="card-saas overflow-hidden dark:bg-gray-800">
        <div class="px-5 py-4 border-b border-siakad-light dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-siakad-dark dark:text-white">Riwayat Kehadiran</h3>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Daftar rekaman kehadiran harian dan jadwal mengajar</p>
                </div>
                <form method="GET" action="{{ route('dosen.kehadiran.index') }}" class="flex flex-col md:flex-row md:items-center gap-3 w-full lg:w-auto">
                    <div class="relative w-full lg:w-48">
                        <input type="text" name="search" placeholder="Cari keterangan..." class="input-saas w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-gray-900 border-siakad-light dark:border-gray-700 text-siakad-dark dark:text-white" value="{{ request('search') }}">
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
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-siakad-primary text-white rounded-lg text-sm font-medium hover:bg-siakad-primary/90 transition min-h-[40px]">Terapkan</button>
                        @if(request()->hasAny(['search', 'month', 'year']))
                        <a href="{{ route('dosen.kehadiran.index') }}" class="px-3 py-2 border border-siakad-light dark:border-gray-700 text-siakad-secondary dark:text-gray-400 rounded-lg text-sm hover:bg-siakad-light/50 transition flex items-center justify-center" title="Reset filter">
                            Reset
                        </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Desktop Table -->
        <table class="hidden md:table w-full table-saas">
            <thead>
                <tr class="bg-siakad-light/30 dark:bg-gray-900">
                    <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Tanggal</th>
                    <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Aktivitas / Keterangan</th>
                    <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Jam Masuk</th>
                    <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Jam Keluar</th>
                    <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $index => $r)
                <tr class="{{ $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-siakad-light/10 dark:bg-gray-700/30' }} border-b border-siakad-light/30 dark:border-gray-700 hover:bg-siakad-light/30 dark:hover:bg-gray-700 transition">
                    <td class="py-4 px-5 text-sm text-siakad-dark dark:text-white font-medium whitespace-nowrap">
                        {{ $r->tanggal->locale('id')->isoFormat('D MMM YYYY') }}
                    </td>
                    <td class="py-4 px-5 text-sm text-siakad-dark dark:text-white">
                        @if($r->jadwalKuliah?->kelas?->mataKuliah)
                            <div class="font-medium">{{ $r->jadwalKuliah->kelas->mataKuliah->nama_mk }}</div>
                            <div class="text-xs text-siakad-secondary dark:text-gray-400">Kelas {{ $r->jadwalKuliah->kelas->nama_kelas }} • Ruang {{ $r->jadwalKuliah->ruangan ?? '-' }}</div>
                        @else
                            <div class="font-medium">Absen Harian</div>
                        @endif

                        @if($r->keterangan)
                            <div class="text-xs text-siakad-secondary dark:text-gray-400 mt-0.5">
                                Catatan: {{ $r->keterangan }}
                            </div>
                        @endif

                        @if($r->bukti_file)
                            <div class="mt-1">
                                <a href="{{ Storage::url($r->bukti_file) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-siakad-primary hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    Lihat Bukti
                                </a>
                            </div>
                        @endif
                    </td>
                    <td class="py-4 px-5 text-center text-sm text-siakad-secondary dark:text-gray-400 whitespace-nowrap">
                        {{ $r->jam_masuk ? substr($r->jam_masuk, 0, 5) : '-' }}
                    </td>
                    <td class="py-4 px-5 text-center text-sm text-siakad-secondary dark:text-gray-400 whitespace-nowrap">
                        {{ $r->jam_keluar ? substr($r->jam_keluar, 0, 5) : '-' }}
                    </td>
                    <td class="py-4 px-5 text-center whitespace-nowrap">
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $r->status_color }}-100 text-{{ $r->status_color }}-700 dark:bg-gray-800 dark:text-{{ $r->status_color }}-400 border dark:border-{{ $r->status_color }}-400/20">
                            {{ $r->status_label }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-10 text-center text-siakad-secondary dark:text-gray-400 text-sm">
                        Belum ada data kehadiran pada periode ini
                    </td>
                </tr>
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
                        <h4 class="font-bold text-siakad-dark dark:text-white text-sm mt-0.5">
                            {{ $r->jadwalKuliah?->kelas?->mataKuliah?->nama_mk ?? 'Absen Harian' }}
                        </h4>
                        @if($r->keterangan)
                            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-0.5">{{ $r->keterangan }}</p>
                        @endif
                    </div>
                    <span class="px-2 py-1 text-[10px] font-medium rounded-full bg-{{ $r->status_color }}-100 text-{{ $r->status_color }}-700 dark:bg-gray-800 dark:text-{{ $r->status_color }}-400">
                        {{ $r->status_label }}
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs text-siakad-secondary dark:text-gray-400 bg-siakad-light/30 dark:bg-gray-700/30 p-2 rounded-lg mt-2">
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
                @if($r->bukti_file)
                    <div class="mt-2 text-right">
                        <a href="{{ Storage::url($r->bukti_file) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-siakad-primary hover:underline">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            Lihat Bukti
                        </a>
                    </div>
                @endif
            </div>
            @empty
            <div class="p-8 text-center text-siakad-secondary dark:text-gray-400 text-sm">Belum ada data kehadiran pada periode ini</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
