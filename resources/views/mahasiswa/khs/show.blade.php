<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">KHS {{ $tahunAkademik->tahun }} {{ $tahunAkademik->semester }}</span>
        <span class="hidden md:inline">KHS {{ $tahunAkademik->tahun }} - Semester {{ $tahunAkademik->semester }}</span>
    </x-slot>

    <div class="mb-6">
        <a href="{{ route('mahasiswa.khs.index') }}" class="inline-flex items-center gap-2 text-siakad-secondary hover:text-siakad-primary transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Daftar KHS
        </a>
    </div>

    <!-- Header Card -->
    <div class="rounded-2xl p-6 bg-[#1B3C53] text-white mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-xl md:text-2xl font-bold">Kartu Hasil Studi</h2>
                <p class="opacity-80 mt-1 text-sm md:text-base">
                    <span class="md:hidden">{{ $tahunAkademik->tahun }} {{ $tahunAkademik->semester }}</span>
                    <span class="hidden md:inline">{{ $tahunAkademik->tahun }} - Semester {{ $tahunAkademik->semester }}</span>
                </p>
                <div class="mt-4 flex flex-col md:flex-row gap-4 md:items-center md:gap-6">
                    <div>
                        <p class="text-[10px] md:text-xs opacity-60 uppercase tracking-wide">Nama</p>
                        <p class="font-semibold text-sm md:text-base">{{ $mahasiswa->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] md:text-xs opacity-60 uppercase tracking-wide">NIM</p>
                        <p class="font-semibold text-sm md:text-base">{{ $mahasiswa->nim }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] md:text-xs opacity-60 uppercase tracking-wide">Program Studi</p>
                        <p class="font-semibold text-sm md:text-base">{{ $mahasiswa->prodi->nama_prodi ?? '-' }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-2 md:mt-0 pt-4 md:pt-0 border-t border-white/10 md:border-0 md:text-right flex flex-row md:flex-col items-center md:items-end justify-between md:justify-start">
                <div>
                    <p class="text-xs opacity-60">IPS Semester Ini</p>
                    <p class="text-4xl md:text-5xl font-bold">{{ number_format($ipsData['ips'], 2) }}</p>
                </div>
                <div class="md:mt-1">
                    <p class="text-sm opacity-80 bg-white/10 px-3 py-1 rounded-lg">{{ $ipsData['total_sks'] }} SKS</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-8">
        <!-- Stats Cards -->
        <div class="card-saas p-5 text-center">
            <div class="w-12 h-12 rounded-xl bg-siakad-primary/10 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <p class="text-2xl font-bold text-siakad-dark">{{ number_format($ipsData['ips'], 2) }}</p>
            <p class="text-sm text-siakad-secondary">IPS</p>
        </div>
        <div class="card-saas p-5 text-center">
            <div class="w-12 h-12 rounded-xl bg-[#234C6A]/10 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-[#234C6A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            </div>
            <p class="text-2xl font-bold text-siakad-dark">{{ number_format($ipkData['ips'], 2) }}</p>
            <p class="text-sm text-siakad-secondary">IPK Kumulatif</p>
        </div>
        <div class="card-saas p-5 text-center">
            <div class="w-12 h-12 rounded-xl bg-[#456882]/10 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-[#456882]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <p class="text-2xl font-bold text-siakad-dark">{{ $nilaiList->count() }}</p>
            <p class="text-sm text-siakad-secondary">Mata Kuliah</p>
        </div>
        <div class="card-saas p-5 text-center">
            <div class="w-12 h-12 rounded-xl bg-[#1B3C53]/10 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-[#1B3C53] dark:text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <p class="text-2xl font-bold text-siakad-dark">{{ $ipsData['total_sks'] }}</p>
            <p class="text-sm text-siakad-secondary">Total SKS</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Nilai Table -->
        <div class="lg:col-span-2">
            <div class="card-saas overflow-hidden">
                <div class="px-6 py-4 border-b border-siakad-light flex items-center justify-between">
                    <h3 class="font-semibold text-siakad-dark">Daftar Nilai</h3>
                    <a href="{{ route('mahasiswa.export.khs', $tahunAkademik) }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-siakad-primary/10 text-siakad-primary rounded-lg text-sm font-medium hover:bg-siakad-primary/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Export PDF
                    </a>
                </div>
                
                @if($nilaiList->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-siakad-secondary">Belum ada nilai untuk semester ini</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full table-saas">
                        <thead>
                            <tr class="bg-[#234C6A] text-white">
                                <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider">Kode</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider">Mata Kuliah</th>
                                <th class="text-center px-6 py-3 text-xs font-semibold uppercase tracking-wider">SKS</th>
                                <th class="text-center px-6 py-3 text-xs font-semibold uppercase tracking-wider">Nilai</th>
                                <th class="text-center px-6 py-3 text-xs font-semibold uppercase tracking-wider">Huruf</th>
                                <th class="text-center px-6 py-3 text-xs font-semibold uppercase tracking-wider">Bobot</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-siakad-light">
                            @foreach($nilaiList as $nilai)
                            @php
                                $mk = $nilai->kelas->mataKuliah;
                                $gradeColor = match($nilai->nilai_huruf) {
                                    'A' => 'emerald',
                                    'B+', 'B' => 'blue',
                                    'C+', 'C' => 'amber',
                                    default => 'red'
                                };
                                $bobot = match($nilai->nilai_huruf) {
                                    'A' => 4.0,
                                    'B+' => 3.5,
                                    'B' => 3.0,
                                    'C+' => 2.5,
                                    'C' => 2.0,
                                    'D' => 1.0,
                                    default => 0
                                };
                            @endphp
                            <tr class="hover:bg-siakad-light/20 transition">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm bg-siakad-light px-2 py-1 rounded text-siakad-dark">{{ $mk->kode_mk }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-siakad-dark">{{ $mk->nama_mk }}</p>
                                    <p class="text-xs text-siakad-secondary">{{ $nilai->kelas->dosen->user->name ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4 text-center text-siakad-secondary">{{ $mk->sks }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-siakad-dark font-medium">{{ $nilai->nilai_angka ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($nilai->nilai_huruf)
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl font-bold text-lg bg-{{ $gradeColor }}-100 text-{{ $gradeColor }}-700">
                                        {{ $nilai->nilai_huruf }}
                                    </span>
                                    @else
                                    <span class="text-siakad-light text-xl">•</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-siakad-secondary">{{ number_format($bobot * $mk->sks, 1) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-[#234C6A] text-white font-semibold">
                                <td colspan="2" class="px-6 py-4 text-right">Total</td>
                                <td class="px-6 py-4 text-center">{{ $ipsData['total_sks'] }}</td>
                                <td colspan="2" class="px-6 py-4 text-center">IPS</td>
                                <td class="px-6 py-4 text-center text-lg">{{ number_format($ipsData['ips'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <!-- Grade Distribution -->
        <div>
            <div class="card-saas overflow-hidden">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Distribusi Nilai</h3>
                </div>
                <div class="p-6 space-y-4">
                    @foreach(['A', 'B+', 'B', 'C+', 'C', 'D', 'E'] as $grade)
                    @php
                        $count = $gradeDistribution[$grade] ?? 0;
                        $percentage = $nilaiList->count() > 0 ? ($count / $nilaiList->count() * 100) : 0;
                        $gradeColor = match($grade) {
                            'A' => 'emerald',
                            'B+', 'B' => 'blue',
                            'C+', 'C' => 'amber',
                            default => 'red'
                        };
                    @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-{{ $gradeColor }}-100 text-{{ $gradeColor }}-700 flex items-center justify-center font-bold text-sm">{{ $grade }}</span>
                        <div class="flex-1">
                            <div class="h-3 bg-siakad-light rounded-full overflow-hidden">
                                <div class="h-full bg-{{ $gradeColor }}-500 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        <span class="text-sm text-siakad-secondary w-6 text-right">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 bg-siakad-dark rounded-2xl p-6 text-white">
                <h3 class="font-semibold mb-4">Aksi Cepat</h3>
                <div class="space-y-3">
                    <a href="{{ route('mahasiswa.transkrip.index') }}" class="flex items-center gap-3 p-3 bg-white/10 rounded-lg hover:bg-white/20 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span class="text-sm">Lihat Transkrip</span>
                    </a>
                    <a href="{{ route('mahasiswa.presensi.index') }}" class="flex items-center gap-3 p-3 bg-white/10 rounded-lg hover:bg-white/20 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        <span class="text-sm">Lihat Presensi</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
