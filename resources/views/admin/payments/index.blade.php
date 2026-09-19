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
            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">
                Pilih mahasiswa untuk melihat seluruh rincian tagihan (Pendaftaran & Semester 1-8) dan memproses pembayaran
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.payments.dashboard') }}" class="btn-ghost-saas px-3.5 py-2 text-xs font-semibold rounded-xl inline-flex items-center gap-2 bg-white dark:bg-gray-800 shadow-sm">
                <svg class="w-4 h-4 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Dashboard Keuangan</span>
            </a>
            <a href="{{ route('admin.payments.export') }}" class="btn-ghost-saas px-3.5 py-2 text-xs font-semibold rounded-xl inline-flex items-center gap-2 bg-white dark:bg-gray-800 shadow-sm">
                <svg class="w-4 h-4 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Ekspor Rekap</span>
            </a>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-1 border-b border-siakad-light dark:border-gray-700 overflow-x-auto w-full md:w-auto">
            <a href="{{ route('admin.payments.index', array_merge(request()->except('status'), ['status' => ''])) }}" 
               class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap {{ !request('status') || request('status') === 'all' ? 'text-siakad-primary dark:text-blue-400 border-siakad-primary dark:border-blue-400 font-bold' : 'text-siakad-secondary dark:text-gray-400 border-transparent hover:text-siakad-dark' }}">
                Semua Mahasiswa
            </a>
            <a href="{{ route('admin.payments.index', array_merge(request()->except('status'), ['status' => 'debt'])) }}" 
               class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap {{ request('status') === 'debt' || request('status') === 'unpaid' ? 'text-siakad-primary dark:text-blue-400 border-siakad-primary dark:border-blue-400 font-bold' : 'text-siakad-secondary dark:text-gray-400 border-transparent hover:text-siakad-dark' }}">
                Ada Tunggakan / Belum Lunas
            </a>
            <a href="{{ route('admin.payments.index', array_merge(request()->except('status'), ['status' => 'partial'])) }}" 
               class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap {{ request('status') === 'partial' ? 'text-siakad-primary dark:text-blue-400 border-siakad-primary dark:border-blue-400 font-bold' : 'text-siakad-secondary dark:text-gray-400 border-transparent hover:text-siakad-dark' }}">
                Sedang Mencicil
            </a>
            <a href="{{ route('admin.payments.index', array_merge(request()->except('status'), ['status' => 'paid'])) }}" 
               class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap {{ request('status') === 'paid' ? 'text-siakad-primary dark:text-blue-400 border-siakad-primary dark:border-blue-400 font-bold' : 'text-siakad-secondary dark:text-gray-400 border-transparent hover:text-siakad-dark' }}">
                Lunas Seluruhnya
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
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Total Kewajiban</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Telah Dibayar</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Sisa Tunggakan</th>
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
                                    <div class="text-[11px] text-siakad-secondary font-mono">
                                        NIM: {{ $m->nim }} &bull; Angkatan {{ $m->angkatan ?? '-' }}
                                    </div>
                                </div>
                            </a>
                        </td>

                        <!-- Program Studi & Fakultas -->
                        <td class="px-4 py-3">
                            <div class="font-semibold text-siakad-dark dark:text-white">{{ $m->prodi->nama ?? '-' }}</div>
                            <div class="text-[10px] text-siakad-secondary">{{ $m->prodi->fakultas->nama ?? '-' }}</div>
                        </td>

                        <!-- Total Kewajiban -->
                        <td class="px-4 py-3 font-semibold text-siakad-dark dark:text-white">
                            Rp {{ number_format($m->total_kewajiban, 0, ',', '.') }}
                        </td>

                        <!-- Telah Dibayar & Persentase -->
                        <td class="px-4 py-3">
                            <div class="font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($m->total_dibayar, 0, ',', '.') }}
                            </div>
                            <div class="flex items-center gap-1.5 mt-1">
                                <div class="flex-1 bg-siakad-light/60 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden w-20">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $m->persen_lunas }}%"></div>
                                </div>
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">{{ $m->persen_lunas }}%</span>
                            </div>
                        </td>

                        <!-- Sisa Tunggakan -->
                        <td class="px-4 py-3 font-bold {{ $m->sisa_tunggakan > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">
                            Rp {{ number_format($m->sisa_tunggakan, 0, ',', '.') }}
                        </td>

                        <!-- Status Ringkas Pembayaran -->
                        <td class="px-4 py-3">
                            @if($m->sisa_tunggakan <= 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                    ✓ Lunas Seluruhnya
                                </span>
                            @elseif($m->partial_count > 0)
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200">
                                        Sedang Mencicil
                                    </span>
                                    @if($m->next_payment)
                                        <div class="text-[10px] text-amber-700 dark:text-amber-400 mt-0.5">
                                            Siap: {{ $m->next_payment->paymentType->name ?? 'Heregistrasi' }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">
                                        {{ $m->unpaid_count }} Belum Lunas
                                    </span>
                                    @if($m->next_payment)
                                        <div class="text-[10px] text-siakad-secondary dark:text-gray-400 mt-0.5">
                                            Urutan: {{ $m->next_payment->paymentType->name ?? 'Heregistrasi' }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </td>

                        <!-- Aksi: Menuju Detail Semua Pembayaran -->
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.payments.student', $m->id) }}" 
                               class="btn-primary-saas px-3.5 py-1.5 text-xs font-semibold rounded-lg inline-flex items-center gap-1.5 shadow-sm">
                                <span>Detail Tagihan</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
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
