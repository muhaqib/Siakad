<x-app-layout>
    <x-slot name="header">
        Jadwal Perkuliahan
    </x-slot>

    @php
        $now = \Carbon\Carbon::now();
        $todayRaw = $now->locale('id')->isoFormat('dddd');
        // Map Minggu to Ahad if needed
        $today = ($todayRaw === 'Minggu') ? 'Ahad' : $todayRaw;
        $allDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'];
        $totalSksCount = $totalSks ?? 0;
        $totalKelasCount = $totalKelas ?? 0;
        $activeDaysCount = $jadwalPerHari->filter(fn($list) => $list->isNotEmpty())->count();
    @endphp

    <div x-data="{ 
        activeTab: 'all', 
        selectedDay: '{{ in_array($today, $allDays) ? $today : 'Senin' }}'
    }" class="space-y-6">

        @if(!$activeTA)
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-2xl p-8 text-center max-w-md mx-auto">
            <div class="w-14 h-14 rounded-full bg-amber-100 dark:bg-amber-800/40 flex items-center justify-center mx-auto mb-4 text-amber-600 dark:text-amber-400">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="font-bold text-amber-900 dark:text-amber-200 text-lg mb-1">Tahun Akademik Belum Aktif</h3>
            <p class="text-sm text-amber-700 dark:text-amber-300/80">Saat ini belum ada tahun akademik yang aktif. Silakan hubungi bagian administrasi akademik.</p>
        </div>
        @else

        <!-- Top Header & Action Controls -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Semester Aktif
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">T.A. {{ $activeTA->tahun }} &bull; Semester {{ $activeTA->semester }}</span>
                </div>
                <h2 class="text-xl font-bold text-siakad-dark dark:text-white">Jadwal Kuliah Mingguan</h2>
                <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-0.5">Menampilkan jadwal perkuliahan dari Senin sampai Ahad</p>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- View Mode Switch -->
                <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-700/60 rounded-xl">
                    <button type="button" @click="activeTab = 'all'" 
                            :class="activeTab === 'all' ? 'bg-white dark:bg-gray-800 text-siakad-dark dark:text-white shadow-xs font-semibold' : 'text-gray-500 dark:text-gray-400 font-medium hover:text-gray-800'"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all duration-150 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <span>Menyeluruh</span>
                    </button>
                    <button type="button" @click="activeTab = 'tab'" 
                            :class="activeTab === 'tab' ? 'bg-white dark:bg-gray-800 text-siakad-dark dark:text-white shadow-xs font-semibold' : 'text-gray-500 dark:text-gray-400 font-medium hover:text-gray-800'"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all duration-150 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Per Hari</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Summary Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xs">
                <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">TOTAL BEBAN SKS</p>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white mt-1">{{ $totalSksCount }} <span class="text-xs font-normal text-gray-500">SKS</span></p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xs">
                <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">MATA KULIAH</p>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white mt-1">{{ $totalKelasCount }} <span class="text-xs font-normal text-gray-500">Kelas</span></p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xs">
                <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">HARI AKTIF</p>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white mt-1">{{ $activeDaysCount }} <span class="text-xs font-normal text-gray-500">Hari / Minggu</span></p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xs">
                <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">HARI INI</p>
                <p class="text-base font-bold text-emerald-600 dark:text-emerald-400 mt-1.5 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    {{ $today }}
                </p>
            </div>
        </div>

        <!-- Per-Day Filter Bar (Only shown in 'tab' mode) -->
        <div x-show="activeTab === 'tab'" x-cloak class="overflow-x-auto pb-1">
            <div class="flex items-center gap-2 min-w-max">
                @foreach($allDays as $hari)
                @php
                    $list = $jadwalPerHari->get($hari, collect());
                    $count = $list->count();
                    $isHariIni = ($hari === $today);
                @endphp
                <button type="button" 
                        @click="selectedDay = '{{ $hari }}'"
                        :class="selectedDay === '{{ $hari }}' 
                            ? 'bg-[#234C6A] text-white shadow-sm font-semibold' 
                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:border-gray-300'"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-medium transition-all duration-150 flex items-center gap-2">
                    <span>{{ $hari }}</span>
                    @if($isHariIni)
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400" title="Hari Ini"></span>
                    @endif
                    <span :class="selectedDay === '{{ $hari }}' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'"
                          class="px-1.5 py-0.5 rounded-md text-[10px] font-bold">
                        {{ $count }}
                    </span>
                </button>
                @endforeach
            </div>
        </div>

        <!-- DOCUMENT SHEET CONTAINER (PDF Preview Style) -->
        <!-- VIEW: MENYELURUH (All Days) -->
        <div x-show="activeTab === 'all'">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
                <!-- Document Header Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-8 mb-6 text-xs sm:text-sm">
                    <div class="space-y-1.5">
                        <div class="flex">
                            <span class="w-36 text-gray-800 dark:text-gray-300 font-medium flex-shrink-0">Nama Mahasiswa</span>
                            <span class="mr-2 text-gray-800 dark:text-gray-300">:</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $mahasiswa->user->name }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-36 text-gray-800 dark:text-gray-300 font-medium flex-shrink-0">NIM</span>
                            <span class="mr-2 text-gray-800 dark:text-gray-300">:</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $mahasiswa->nim }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-36 text-gray-800 dark:text-gray-300 font-medium flex-shrink-0">Semester / Angkatan</span>
                            <span class="mr-2 text-gray-800 dark:text-gray-300">:</span>
                            <span class="text-gray-900 dark:text-white">Angkatan {{ $mahasiswa->angkatan ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex">
                            <span class="w-36 text-gray-800 dark:text-gray-300 font-medium flex-shrink-0">Program Studi</span>
                            <span class="mr-2 text-gray-800 dark:text-gray-300">:</span>
                            <span class="text-gray-900 dark:text-white">{{ $mahasiswa->prodi->nama ?? '-' }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-36 text-gray-800 dark:text-gray-300 font-medium flex-shrink-0">Dosen Pembimbing</span>
                            <span class="mr-2 text-gray-800 dark:text-gray-300">:</span>
                            <span class="text-gray-900 dark:text-white">{{ $mahasiswa->dosenPa->user->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- PDF Style Schedule Table -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-black dark:border-gray-600 text-xs sm:text-[13px] text-gray-900 dark:text-gray-100">
                        <thead>
                            <tr class="font-bold text-center bg-gray-50/80 dark:bg-gray-700/50">
                                <th class="border border-black dark:border-gray-600 px-2.5 py-2 w-10 text-center font-bold">No</th>
                                <th class="border border-black dark:border-gray-600 px-3 py-2 w-20 text-center font-bold">Hari</th>
                                <th class="border border-black dark:border-gray-600 px-3 py-2 w-28 text-center font-bold">Waktu</th>
                                <th class="border border-black dark:border-gray-600 px-3 py-2 w-20 text-center font-bold">Kode MK</th>
                                <th class="border border-black dark:border-gray-600 px-3 py-2 text-center font-bold">Mata Kuliah</th>
                                <th class="border border-black dark:border-gray-600 px-2 py-2 w-14 text-center font-bold">SKS</th>
                                <th class="border border-black dark:border-gray-600 px-2 py-2 w-14 text-center font-bold">Kelas</th>
                                <th class="border border-black dark:border-gray-600 px-3 py-2 w-28 text-center font-bold">Ruang</th>
                                <th class="border border-black dark:border-gray-600 px-3 py-2 text-center font-bold">Dosen Pengampu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; $hasAnySchedule = false; @endphp
                            @foreach($hariOrder as $hari)
                                @php $list = $jadwalPerHari->get($hari, collect()); @endphp
                                @foreach($list as $item)
                                    @php
                                        $hasAnySchedule = true;
                                        $jamMulai = \Carbon\Carbon::parse($item['jadwal']->jam_mulai)->format('H:i');
                                        $jamSelesai = \Carbon\Carbon::parse($item['jadwal']->jam_selesai)->format('H:i');
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">{{ $no++ }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center font-bold">{{ $hari }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center whitespace-nowrap">{{ $jamMulai }} - {{ $jamSelesai }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center font-mono">{{ $item['kelas']->mataKuliah->kode_mk }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-left">{{ $item['kelas']->mataKuliah->nama_mk }}</td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">{{ $item['kelas']->mataKuliah->sks }}</td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">{{ $item['kelas']->nama_kelas }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center">{{ $item['jadwal']->ruangan ?? '-' }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-left">{{ $item['kelas']->dosen->user->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            @endforeach

                            @if(!$hasAnySchedule)
                                <tr>
                                    <td colspan="9" class="border border-black dark:border-gray-600 px-4 py-8 text-center text-gray-500 italic">
                                        Belum ada jadwal perkuliahan yang disetujui untuk semester ini.
                                    </td>
                                </tr>
                            @endif

                            <tr class="font-bold bg-gray-50/30 dark:bg-gray-800/40">
                                <td colspan="5" class="border border-black dark:border-gray-600 px-3 py-2 text-right font-bold">
                                    Total SKS Terjadwal
                                </td>
                                <td class="border border-black dark:border-gray-600 px-2 py-2 text-center font-bold">
                                    {{ $totalSksCount }}
                                </td>
                                <td colspan="3" class="border border-black dark:border-gray-600 px-3 py-2"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- VIEW: PER HARI (Filtered Table) -->
        <div x-show="activeTab === 'tab'" x-cloak class="space-y-4">
            @foreach($allDays as $hari)
            @php
                $list = $jadwalPerHari->get($hari, collect());
                $daySks = $list->sum(fn($i) => $i['kelas']->mataKuliah->sks ?? 0);
            @endphp
            <div x-show="selectedDay === '{{ $hari }}'">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
                    <!-- Document Header Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-8 mb-6 text-xs sm:text-sm">
                        <div class="space-y-1.5">
                            <div class="flex">
                                <span class="w-36 text-gray-800 dark:text-gray-300 font-medium flex-shrink-0">Nama Mahasiswa</span>
                                <span class="mr-2 text-gray-800 dark:text-gray-300">:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $mahasiswa->user->name }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-36 text-gray-800 dark:text-gray-300 font-medium flex-shrink-0">NIM</span>
                                <span class="mr-2 text-gray-800 dark:text-gray-300">:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $mahasiswa->nim }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-36 text-gray-800 dark:text-gray-300 font-medium flex-shrink-0">Semester / Angkatan</span>
                                <span class="mr-2 text-gray-800 dark:text-gray-300">:</span>
                                <span class="text-gray-900 dark:text-white">Angkatan {{ $mahasiswa->angkatan ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <div class="flex">
                                <span class="w-36 text-gray-800 dark:text-gray-300 font-medium flex-shrink-0">Program Studi</span>
                                <span class="mr-2 text-gray-800 dark:text-gray-300">:</span>
                                <span class="text-gray-900 dark:text-white">{{ $mahasiswa->prodi->nama ?? '-' }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-36 text-gray-800 dark:text-gray-300 font-medium flex-shrink-0">Dosen Pembimbing</span>
                                <span class="mr-2 text-gray-800 dark:text-gray-300">:</span>
                                <span class="text-gray-900 dark:text-white">{{ $mahasiswa->dosenPa->user->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Single Day Schedule Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-black dark:border-gray-600 text-xs sm:text-[13px] text-gray-900 dark:text-gray-100">
                            <thead>
                                <tr class="font-bold text-center bg-gray-50/80 dark:bg-gray-700/50">
                                    <th class="border border-black dark:border-gray-600 px-2.5 py-2 w-10 text-center font-bold">No</th>
                                    <th class="border border-black dark:border-gray-600 px-3 py-2 w-20 text-center font-bold">Hari</th>
                                    <th class="border border-black dark:border-gray-600 px-3 py-2 w-28 text-center font-bold">Waktu</th>
                                    <th class="border border-black dark:border-gray-600 px-3 py-2 w-20 text-center font-bold">Kode MK</th>
                                    <th class="border border-black dark:border-gray-600 px-3 py-2 text-center font-bold">Mata Kuliah</th>
                                    <th class="border border-black dark:border-gray-600 px-2 py-2 w-14 text-center font-bold">SKS</th>
                                    <th class="border border-black dark:border-gray-600 px-2 py-2 w-14 text-center font-bold">Kelas</th>
                                    <th class="border border-black dark:border-gray-600 px-3 py-2 w-28 text-center font-bold">Ruang</th>
                                    <th class="border border-black dark:border-gray-600 px-3 py-2 text-center font-bold">Dosen Pengampu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $dayNo = 1; @endphp
                                @forelse($list as $item)
                                    @php
                                        $jamMulai = \Carbon\Carbon::parse($item['jadwal']->jam_mulai)->format('H:i');
                                        $jamSelesai = \Carbon\Carbon::parse($item['jadwal']->jam_selesai)->format('H:i');
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">{{ $dayNo++ }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center font-bold">{{ $hari }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center whitespace-nowrap">{{ $jamMulai }} - {{ $jamSelesai }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center font-mono">{{ $item['kelas']->mataKuliah->kode_mk }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-left">{{ $item['kelas']->mataKuliah->nama_mk }}</td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">{{ $item['kelas']->mataKuliah->sks }}</td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">{{ $item['kelas']->nama_kelas }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center">{{ $item['jadwal']->ruangan ?? '-' }}</td>
                                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-left">{{ $item['kelas']->dosen->user->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="border border-black dark:border-gray-600 px-4 py-8 text-center text-gray-500 italic">
                                            Tidak ada jadwal perkuliahan pada hari {{ $hari }}.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="font-bold bg-gray-50/30 dark:bg-gray-800/40">
                                    <td colspan="5" class="border border-black dark:border-gray-600 px-3 py-2 text-right font-bold">
                                        Total SKS Terjadwal ({{ $hari }})
                                    </td>
                                    <td class="border border-black dark:border-gray-600 px-2 py-2 text-center font-bold">
                                        {{ $daySks }}
                                    </td>
                                    <td colspan="3" class="border border-black dark:border-gray-600 px-3 py-2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Action Button: Cetak Jadwal (Bottom Right as in contohjadwal.png) -->
        <div class="flex justify-end pt-2">
            <a href="{{ route('mahasiswa.export.jadwal') }}" target="_blank" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#234C6A] hover:bg-[#1B3C53] text-white text-sm font-semibold shadow-sm transition-all duration-150 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Jadwal</span>
            </a>
        </div>

        @endif
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</x-app-layout>
