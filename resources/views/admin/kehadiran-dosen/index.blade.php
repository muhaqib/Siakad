<x-app-layout>
    <x-slot name="header">Monitoring Kehadiran Dosen</x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        @foreach([['Hadir', $stats['hadir'] ?? 0, 'emerald'], ['Izin', $stats['izin'] ?? 0, 'blue'], ['Sakit', $stats['sakit'] ?? 0, 'amber'], ['Tugas', $stats['tugas'] ?? 0, 'purple'], ['Alpa', $stats['alpa'] ?? 0, 'red']] as $s)
        <div class="card-saas p-4 text-center dark:bg-gray-800">
            <p class="text-2xl font-bold text-{{ $s[2] }}-600 dark:text-{{ $s[2] }}-400">{{ $s[1] }}</p>
            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $s[0] }}</p>
        </div>
        @endforeach
    </div>

    <!-- Filter -->
    <div class="card-saas p-4 mb-6 dark:bg-gray-800">
        <form method="GET" class="flex flex-col md:flex-row items-end gap-4">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-siakad-dark dark:text-gray-300 mb-1">Dosen</label>
                <select name="dosen_id" class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    <option value="">Semua Dosen</option>
                    @foreach($dosenList as $d)
                    <option value="{{ $d->id }}" {{ $dosenId == $d->id ? 'selected' : '' }}>{{ $d->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-auto">
                <label class="block text-xs font-medium text-siakad-dark dark:text-gray-300 mb-1">Bulan</label>
                <select name="month" class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->locale('id')->monthName }}</option>
                    @endfor
                </select>
            </div>
            <div class="w-full md:w-auto">
                <label class="block text-xs font-medium text-siakad-dark dark:text-gray-300 mb-1">Tahun</label>
                <select name="year" class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="w-full md:w-auto btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Rekap Per Dosen -->
    <div class="card-saas mb-6 overflow-hidden dark:bg-gray-800">
        <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700"><h3 class="font-semibold text-siakad-dark dark:text-white">Rekap Per Dosen</h3></div>
        <div class="overflow-x-auto">
            <!-- Table (Desktop) -->
            <table class="hidden md:table w-full table-saas">
                <thead><tr class="bg-siakad-light/30 dark:bg-gray-900"><th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Dosen</th><th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Hadir</th><th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Izin</th><th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Sakit</th><th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Tugas</th><th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Alpa</th><th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">%</th></tr></thead>
                <tbody>
                @foreach($dosenList as $d)
                @php 
                    $rekap = $rekapDosen[$d->id] ?? collect();
                    $hadirCount = $rekap->where('status', 'hadir')->first()?->count ?? 0;
                    $total = $rekap->sum('count') ?: 1;
                    $persen = round(($hadirCount / $total) * 100);
                @endphp
                <tr class="border-b border-siakad-light/50 dark:border-gray-700/50">
                    <td class="py-3 px-5"><p class="font-medium text-siakad-dark dark:text-white">{{ $d->user->name }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $d->nidn }}</p></td>
                    <td class="py-3 px-5 text-center text-sm text-emerald-600 dark:text-emerald-400 font-medium">{{ $rekap->where('status', 'hadir')->first()?->count ?? 0 }}</td>
                    <td class="py-3 px-5 text-center text-sm text-blue-600 dark:text-blue-400">{{ $rekap->where('status', 'izin')->first()?->count ?? 0 }}</td>
                    <td class="py-3 px-5 text-center text-sm text-amber-600 dark:text-amber-400">{{ $rekap->where('status', 'sakit')->first()?->count ?? 0 }}</td>
                    <td class="py-3 px-5 text-center text-sm text-purple-600 dark:text-purple-400">{{ $rekap->where('status', 'tugas')->first()?->count ?? 0 }}</td>
                    <td class="py-3 px-5 text-center text-sm text-red-600 dark:text-red-400">{{ $rekap->where('status', 'alpa')->first()?->count ?? 0 }}</td>
                    <td class="py-3 px-5 text-center"><span class="px-2 py-1 text-xs font-medium rounded-full {{ $persen >= 80 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : ($persen >= 60 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400') }}">{{ $persen }}%</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>

            <!-- Mobile Card List (Rekap) -->
            <div class="md:hidden divide-y divide-siakad-light dark:divide-gray-700">
                @foreach($dosenList as $d)
                @php 
                    $rekap = $rekapDosen[$d->id] ?? collect();
                    $hadirCount = $rekap->where('status', 'hadir')->first()?->count ?? 0;
                    $total = $rekap->sum('count') ?: 1;
                    $persen = round(($hadirCount / $total) * 100);
                @endphp
                <div class="p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h4 class="font-bold text-siakad-dark dark:text-white text-sm">{{ $d->user->name }}</h4>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $d->nidn }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $persen >= 80 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : ($persen >= 60 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400') }}">{{ $persen }}%</span>
                    </div>
                    <div class="grid grid-cols-5 gap-2 text-center text-xs">
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 p-1.5 rounded">
                            <span class="block text-emerald-600 dark:text-emerald-400 font-bold mb-0.5">{{ $rekap->where('status', 'hadir')->first()?->count ?? 0 }}</span>
                            <span class="text-[10px] text-emerald-600/70 dark:text-emerald-400/70">Hadir</span>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-1.5 rounded">
                            <span class="block text-blue-600 dark:text-blue-400 font-bold mb-0.5">{{ $rekap->where('status', 'izin')->first()?->count ?? 0 }}</span>
                            <span class="text-[10px] text-blue-600/70 dark:text-blue-400/70">Izin</span>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 p-1.5 rounded">
                            <span class="block text-amber-600 dark:text-amber-400 font-bold mb-0.5">{{ $rekap->where('status', 'sakit')->first()?->count ?? 0 }}</span>
                            <span class="text-[10px] text-amber-600/70 dark:text-amber-400/70">Sakit</span>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-1.5 rounded">
                            <span class="block text-purple-600 dark:text-purple-400 font-bold mb-0.5">{{ $rekap->where('status', 'tugas')->first()?->count ?? 0 }}</span>
                            <span class="text-[10px] text-purple-600/70 dark:text-purple-400/70">Tugas</span>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 p-1.5 rounded">
                            <span class="block text-red-600 dark:text-red-400 font-bold mb-0.5">{{ $rekap->where('status', 'alpa')->first()?->count ?? 0 }}</span>
                            <span class="text-[10px] text-red-600/70 dark:text-red-400/70">Alpa</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Detail -->
    <div class="card-saas overflow-hidden dark:bg-gray-800">
        <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700"><h3 class="font-semibold text-siakad-dark dark:text-white">Detail Kehadiran</h3></div>
        <div class="overflow-x-auto">
            <!-- Table (Desktop) -->
            <table class="hidden md:table w-full table-saas">
                <thead><tr class="bg-siakad-light/30 dark:bg-gray-900"><th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Tanggal</th><th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Dosen</th><th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Mata Kuliah</th><th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Jam</th><th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Status</th></tr></thead>
                <tbody>
                @forelse($kehadiranList as $k)
                <tr class="border-b border-siakad-light/50 dark:border-gray-700/50">
                    <td class="py-3 px-5 text-sm text-siakad-dark dark:text-white">{{ $k->tanggal->format('d M Y') }}</td>
                    <td class="py-3 px-5 text-sm text-siakad-dark dark:text-white">{{ $k->dosen->user->name }}</td>
                    <td class="py-3 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $k->jadwalKuliah?->kelas?->mataKuliah?->nama ?? '-' }}</td>
                    <td class="py-3 px-5 text-center text-sm text-siakad-secondary dark:text-gray-400">{{ $k->jam_masuk ? substr($k->jam_masuk, 0, 5) : '-' }} - {{ $k->jam_keluar ? substr($k->jam_keluar, 0, 5) : '-' }}</td>
                    <td class="py-3 px-5 text-center"><span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $k->status_color }}-100 text-{{ $k->status_color }}-700 dark:bg-{{ $k->status_color }}-900/50 dark:text-{{ $k->status_color }}-400">{{ $k->status_label }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-8 text-center text-siakad-secondary dark:text-gray-400">Belum ada data</td></tr>
                @endforelse
                </tbody>
            </table>

            <!-- Mobile Card List (Detail) -->
            <div class="md:hidden divide-y divide-siakad-light dark:divide-gray-700">
                @forelse($kehadiranList as $k)
                <div class="p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <span class="text-xs text-siakad-secondary dark:text-gray-400">{{ $k->tanggal->format('d M Y') }}</span>
                            <h4 class="font-bold text-siakad-dark dark:text-white text-sm mt-0.5">{{ $k->dosen->user->name }}</h4>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-{{ $k->status_color }}-100 text-{{ $k->status_color }}-700 dark:bg-{{ $k->status_color }}-900/50 dark:text-{{ $k->status_color }}-400">{{ $k->status_label }}</span>
                    </div>
                    
                    @if($k->jadwalKuliah)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-xs mb-2">
                        <span class="block font-medium text-siakad-dark dark:text-white mb-1">{{ $k->jadwalKuliah->kelas->mataKuliah->nama ?? '-' }}</span>
                        <div class="flex items-center gap-2 text-siakad-secondary dark:text-gray-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ $k->jam_masuk ? substr($k->jam_masuk, 0, 5) : '-' }} - {{ $k->jam_keluar ? substr($k->jam_keluar, 0, 5) : '-' }}</span>
                        </div>
                    </div>
                    @endif
                </div>
                @empty
                <div class="p-8 text-center text-siakad-secondary dark:text-gray-400 text-sm">Belum ada data</div>
                @endforelse
            </div>
        </div>
        @if($kehadiranList->hasPages())<div class="px-5 py-4 border-t border-siakad-light dark:border-gray-700">{{ $kehadiranList->links() }}</div>@endif
    </div>
</x-app-layout>
