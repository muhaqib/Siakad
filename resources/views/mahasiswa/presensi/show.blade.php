<x-app-layout>
    <x-slot name="header">
        Detail Presensi
    </x-slot>

    <div class="mb-6">
        <a href="{{ route('mahasiswa.presensi.index') }}" class="inline-flex items-center gap-2 text-siakad-secondary hover:text-[#234C6A] transition text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Rekap Presensi
        </a>
    </div>

    <!-- Course Info Header -->
    <div class="bg-[#1B3C53] rounded-2xl p-6 md:p-8 text-white mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-block px-2.5 py-1 bg-white/20 text-white/90 text-xs font-bold rounded mb-3">{{ $kelas->mataKuliah->kode_mk }}</span>
                <h2 class="text-xl md:text-2xl font-bold">{{ $kelas->mataKuliah->nama_mk }}</h2>
                <p class="text-white/70 mt-2 text-sm">{{ $kelas->mataKuliah->sks }} SKS • Kelas {{ $kelas->nama_kelas }}</p>
                <p class="text-white/50 text-xs mt-1">Dosen: {{ $kelas->dosen->user->name ?? '-' }}</p>
            </div>
            <div class="text-center md:text-right">
                @php
                    $pctColor = $rekap['persentase'] >= 80 ? 'text-emerald-300' : ($rekap['persentase'] >= 75 ? 'text-amber-300' : 'text-red-300');
                @endphp
                <p class="text-4xl md:text-5xl font-extrabold {{ $pctColor }}">{{ $rekap['persentase'] }}%</p>
                <p class="text-white/60 text-xs uppercase tracking-wider mt-1">Kehadiran</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card-saas p-4 text-center">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <p class="text-2xl font-bold text-siakad-dark">{{ $rekap['hadir'] }}</p>
            <p class="text-xs text-siakad-secondary">Hadir</p>
        </div>
        <div class="card-saas p-4 text-center">
            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-2xl font-bold text-siakad-dark">{{ $rekap['sakit'] }}</p>
            <p class="text-xs text-siakad-secondary">Sakit</p>
        </div>
        <div class="card-saas p-4 text-center">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <p class="text-2xl font-bold text-siakad-dark">{{ $rekap['izin'] }}</p>
            <p class="text-xs text-siakad-secondary">Izin</p>
        </div>
        <div class="card-saas p-4 text-center">
            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <p class="text-2xl font-bold text-siakad-dark">{{ $rekap['alpa'] }}</p>
            <p class="text-xs text-siakad-secondary">Alpa</p>
        </div>
    </div>

    <!-- Attendance History -->
    <div class="card-saas overflow-hidden">
        <div class="px-6 py-4 border-b border-siakad-light dark:border-slate-700">
            <h3 class="font-bold text-siakad-dark">Riwayat Presensi per Pertemuan</h3>
        </div>
        
        @if($pertemuanList->isEmpty())
        <div class="p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <p class="text-siakad-secondary">Belum ada pertemuan yang tercatat</p>
        </div>
        @else
        <!-- Mobile View -->
        <div class="md:hidden divide-y divide-siakad-light dark:divide-slate-700">
            @foreach($pertemuanList as $pertemuan)
            @php
                $presensi = $presensiData[$pertemuan->id] ?? null;
                $status = $presensi?->status ?? 'belum';
                $statusColors = [
                    'hadir' => 'bg-emerald-100 text-emerald-700',
                    'sakit' => 'bg-amber-100 text-amber-700',
                    'izin' => 'bg-blue-100 text-blue-700',
                    'alpa' => 'bg-red-100 text-red-700',
                    'belum' => 'bg-slate-100 text-slate-500',
                ];
                $statusLabels = [
                    'hadir' => 'Hadir',
                    'sakit' => 'Sakit',
                    'izin' => 'Izin',
                    'alpa' => 'Alpa',
                    'belum' => 'Belum Dicatat',
                ];
            @endphp
            <div class="p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#234C6A]/10 text-[#234C6A] flex items-center justify-center font-bold text-sm">
                            {{ $pertemuan->pertemuan_ke }}
                        </div>
                        <span class="font-medium text-siakad-dark text-sm">Pertemuan {{ $pertemuan->pertemuan_ke }}</span>
                    </div>
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$status] }}">
                        {{ $statusLabels[$status] }}
                    </span>
                </div>
                <div class="text-xs text-siakad-secondary ml-11">
                    {{ $pertemuan->tanggal->format('d M Y') }} 
                    @if($pertemuan->materi) • {{ $pertemuan->materi }} @endif
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-[#234C6A] text-white">
                        <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider">Pertemuan</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider">Tanggal</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider">Materi</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-slate-700">
                    @foreach($pertemuanList as $pertemuan)
                    @php
                        $presensi = $presensiData[$pertemuan->id] ?? null;
                        $status = $presensi?->status ?? 'belum';
                        $statusColors = [
                            'hadir' => 'bg-emerald-100 text-emerald-700',
                            'sakit' => 'bg-amber-100 text-amber-700',
                            'izin' => 'bg-blue-100 text-blue-700',
                            'alpa' => 'bg-red-100 text-red-700',
                            'belum' => 'bg-slate-100 text-slate-500',
                        ];
                        $statusLabels = [
                            'hadir' => 'Hadir',
                            'sakit' => 'Sakit',
                            'izin' => 'Izin',
                            'alpa' => 'Alpa',
                            'belum' => 'Belum Dicatat',
                        ];
                    @endphp
                    <tr class="hover:bg-siakad-light/30 dark:hover:bg-slate-700/50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-[#234C6A]/10 text-[#234C6A] flex items-center justify-center font-bold">
                                    {{ $pertemuan->pertemuan_ke }}
                                </div>
                                <span class="font-medium text-siakad-dark">Pertemuan {{ $pertemuan->pertemuan_ke }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-siakad-secondary">
                            {{ $pertemuan->tanggal->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-siakad-secondary">
                            {{ $pertemuan->materi ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$status] }}">
                                {{ $statusLabels[$status] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</x-app-layout>
