<x-app-layout>
    <x-slot name="header">
        Daftar Pembayaran Mahasiswa
    </x-slot>

    <!-- Page Title & Header Actions -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-siakad-dark dark:text-white">
                Daftar Pembayaran Mahasiswa
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn-ghost-saas px-3.5 py-2 text-xs font-semibold rounded-xl inline-flex items-center gap-2 bg-white dark:bg-gray-800 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-4 h-4 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Ekspor Rekap</span>
            </a>
        </div>
    </div>

    <!-- Status Filter Tabs (Semester Berjalan) -->
    <div class="mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-1 border-b border-siakad-light dark:border-gray-700 overflow-x-auto w-full md:w-auto">
            <a href="{{ route('admin.payments.index', array_merge(request()->except('status'), ['status' => ''])) }}" 
               class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap flex items-center gap-2 {{ !request('status') || request('status') === 'all' ? 'text-siakad-primary dark:text-blue-400 border-siakad-primary dark:border-blue-400 font-bold' : 'text-siakad-secondary dark:text-gray-400 border-transparent hover:text-siakad-dark' }}">
                <span>Semua Mahasiswa</span>
                <span class="px-2 py-0.5 text-xs rounded-full {{ !request('status') || request('status') === 'all' ? 'bg-siakad-primary/10 text-siakad-primary dark:bg-blue-900/40 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                    {{ $statusStats['all'] ?? 0 }}
                </span>
            </a>
            <a href="{{ route('admin.payments.index', array_merge(request()->except('status'), ['status' => 'debt'])) }}" 
               class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap flex items-center gap-2 {{ request('status') === 'debt' || request('status') === 'unpaid' ? 'text-siakad-primary dark:text-blue-400 border-siakad-primary dark:border-blue-400 font-bold' : 'text-siakad-secondary dark:text-gray-400 border-transparent hover:text-siakad-dark' }}">
                <span>Belum Bayar</span>
                <span class="px-2 py-0.5 text-xs rounded-full {{ request('status') === 'debt' || request('status') === 'unpaid' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                    {{ $statusStats['debt'] ?? 0 }}
                </span>
            </a>
            <a href="{{ route('admin.payments.index', array_merge(request()->except('status'), ['status' => 'partial'])) }}" 
               class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap flex items-center gap-2 {{ request('status') === 'partial' ? 'text-siakad-primary dark:text-blue-400 border-siakad-primary dark:border-blue-400 font-bold' : 'text-siakad-secondary dark:text-gray-400 border-transparent hover:text-siakad-dark' }}">
                <span>Sedang Mencicil</span>
                <span class="px-2 py-0.5 text-xs rounded-full {{ request('status') === 'partial' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                    {{ $statusStats['partial'] ?? 0 }}
                </span>
            </a>
            <a href="{{ route('admin.payments.index', array_merge(request()->except('status'), ['status' => 'paid'])) }}" 
               class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap flex items-center gap-2 {{ request('status') === 'paid' ? 'text-siakad-primary dark:text-blue-400 border-siakad-primary dark:border-blue-400 font-bold' : 'text-siakad-secondary dark:text-gray-400 border-transparent hover:text-siakad-dark' }}">
                <span>Lunas Semester Ini</span>
                <span class="px-2 py-0.5 text-xs rounded-full {{ request('status') === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                    {{ $statusStats['paid'] ?? 0 }}
                </span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card-saas p-4 mb-6 bg-white dark:bg-gray-800 shadow-sm">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="space-y-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <!-- Search Mahasiswa -->
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold text-siakad-secondary dark:text-gray-400 mb-1">Cari Mahasiswa (NIM / Nama / Email)</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama mahasiswa atau NIM..."
                                class="input-saas w-full pl-9 pr-4 py-2 text-sm">
                        <svg class="w-4 h-4 text-siakad-secondary absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <!-- Filter Prodi -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-siakad-secondary dark:text-gray-400 mb-1">Program Studi</label>
                    <select name="prodi_id" class="input-saas w-full px-3 py-2 text-sm">
                        <option value="">Semua Prodi</option>
                        @foreach($prodiList as $p)
                            <option value="{{ $p->id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Angkatan -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-siakad-secondary dark:text-gray-400 mb-1">Angkatan</label>
                    <select name="angkatan" class="input-saas w-full px-3 py-2 text-sm">
                        <option value="">Semua Angkatan</option>
                        @foreach($angkatanList as $akt)
                            <option value="{{ $akt }}" {{ request('angkatan') == $akt ? 'selected' : '' }}>{{ $akt }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Submit Filter -->
                <div class="md:col-span-2 flex items-end gap-2">
                    <button type="submit" class="btn-primary-saas flex-1 py-2 text-sm font-semibold rounded-lg text-center">
                        Filter
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="btn-ghost-saas px-3 py-2 text-sm font-medium rounded-lg text-center bg-gray-100 dark:bg-gray-700">
                        Reset
                    </a>
                </div>
            </div>

            @if($user->isSuperAdmin() && $fakultasList->count() > 0)
            <div class="pt-2 border-t border-siakad-light/50 dark:border-gray-700 flex items-center gap-3 text-xs">
                <span class="text-siakad-secondary font-medium">Filter Fakultas:</span>
                <select name="fakultas_id" onchange="this.form.submit()" class="input-saas py-1 px-2.5 text-xs">
                    <option value="">Semua Fakultas</option>
                    @foreach($fakultasList as $f)
                        <option value="{{ $f->id }}" {{ request('fakultas_id') == $f->id ? 'selected' : '' }}>{{ $f->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </form>
    </div>

    <!-- Mahasiswa Table Card -->
    <div class="card-saas overflow-hidden bg-white dark:bg-gray-800 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-saas text-left text-xs">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider text-center w-12">#</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Mahasiswa</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Program Studi</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Tagihan</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Dibayar</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Kekurangan</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Status Pembayaran</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light/60 dark:divide-gray-700/60 text-siakad-dark dark:text-gray-300">
                    @forelse($mahasiswaList as $index => $m)
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition">
                        <td class="px-4 py-3 text-center text-siakad-secondary font-mono">
                            {{ $mahasiswaList->firstItem() + $index }}
                        </td>

                        <!-- Identitas Mahasiswa (Clickable) -->
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.payments.student', $m->id) }}" class="group flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-siakad-primary/10 text-siakad-primary dark:bg-blue-500/20 dark:text-blue-400 flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:bg-siakad-primary group-hover:text-white transition">
                                    {{ strtoupper(substr($m->user->name ?? 'M', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-siakad-dark dark:text-white group-hover:text-siakad-primary transition flex items-center gap-1.5">
                                        <span>{{ $m->user->name ?? '-' }}</span>
                                    </div>
                                    <div class="text-[11px] text-siakad-secondary font-mono flex items-center gap-1.5 mt-0.5">
                                        <span>{{ $m->nim }}</span>
                                        <span>&bull;</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400">Sem {{ $m->active_semester }}</span>
                                    </div>
                                </div>
                            </a>
                        </td>

                        <!-- Program Studi & Fakultas -->
                        <td class="px-4 py-3">
                            <div class="font-semibold text-siakad-dark dark:text-white">{{ $m->prodi->nama ?? '-' }}</div>
                            <div class="text-[10px] text-siakad-secondary">{{ $m->prodi->fakultas->nama ?? '-' }}</div>
                        </td>

                        <!-- Tagihan Semester Ini -->
                        <td class="px-4 py-3">
                            <div class="font-semibold text-siakad-dark dark:text-white">
                                Rp {{ number_format($m->kewajiban_semester_ini ?? 0, 0, ',', '.') }}
                            </div>
                            <div class="text-[10px] text-siakad-secondary">
                                Semester {{ $m->active_semester }}{{ (int) $m->active_semester === 1 ? ' + Registrasi' : '' }}
                            </div>
                        </td>

                        <!-- Telah Dibayar Semester Ini & Persentase -->
                        <td class="px-4 py-3">
                            <div class="font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($m->dibayar_semester_ini ?? 0, 0, ',', '.') }}
                            </div>
                            <div class="flex items-center gap-1.5 mt-1">
                                <div class="flex-1 bg-siakad-light/60 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden w-20">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $m->persen_lunas_semester_ini ?? 0 }}%"></div>
                                </div>
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">{{ $m->persen_lunas_semester_ini ?? 0 }}%</span>
                            </div>
                            <div class="text-[10px] text-siakad-secondary mt-0.5" title="Total semua pembayaran yang pernah dibayarkan">
                                Total Riwayat: Rp {{ number_format($m->total_dibayar ?? 0, 0, ',', '.') }}
                            </div>
                        </td>

                        <!-- Sisa Tunggakan Semester Ini -->
                        <td class="px-4 py-3">
                            @if(($m->tunggakan_semester_ini ?? 0) <= 0)
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400 text-xs">
                                    Rp 0 (Lunas)
                                </span>
                            @else
                                <span class="font-bold text-amber-600 dark:text-amber-400 text-xs">
                                    Rp {{ number_format($m->tunggakan_semester_ini, 0, ',', '.') }}
                                </span>
                            @endif
                        </td>

                        <!-- Status Ringkas Pembayaran Semester Ini -->
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-1">
                                @if(($m->semester_status ?? '') === 'paid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                        ✓ Lunas
                                    </span>
                                @elseif(($m->semester_status ?? '') === 'partial')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200">
                                        Cicilan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">
                                        Belum Bayar
                                    </span>
                                @endif

                                @if($m->is_krs_unlocked)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300" title="Akses KRS Terbuka">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                        KRS Terbuka
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Aksi: Menuju Detail Semua Pembayaran & Cetak Tagihan PDF -->
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.payments.student.invoice', $m->id) }}" target="_blank"
                                   class="px-2.5 py-1.5 text-xs font-semibold rounded-lg inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition shadow-xs"
                                   title="Cetak Tagihan Semester Berjalan (PDF)">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                 
                                </a>
                                <a href="{{ route('admin.payments.student', $m->id) }}" 
                                   class="btn-primary-saas px-3 py-1.5 text-xs font-semibold rounded-lg inline-flex items-center gap-1.5 shadow-sm"
                                   title="Detail Tagihan">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-siakad-secondary">
                            <svg class="w-10 h-10 mx-auto mb-2 text-siakad-light dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <p class="font-medium">Tidak ada data mahasiswa yang sesuai kriteria pencarian.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mahasiswaList->hasPages())
        <div class="p-4 border-t border-siakad-light dark:border-gray-700">
            {{ $mahasiswaList->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
