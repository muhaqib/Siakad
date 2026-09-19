<x-app-layout>
    <x-slot name="header">
        Dashboard Pembayaran Mahasiswa
    </x-slot>

    <!-- Page Title & Actions -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-siakad-dark dark:text-white hidden md:block">Dashboard Pembayaran Mahasiswa</h1>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-1">Monitoring keuangan, rekap tunggakan, dan pelunasan SPP/Akademik STIT Mambaul Hikmah</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.payments.index') }}" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                Daftar Pembayaran
            </a>
            <a href="{{ route('admin.payments.export') }}" target="_blank" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2 bg-white dark:bg-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Laporan
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-saas p-4 mb-6 dark:bg-gray-800">
        <form method="GET" action="{{ route('admin.payments.dashboard') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @if($user->isSuperAdmin() && $fakultasList->count() > 0)
            <div>
                <label class="block text-xs font-medium text-siakad-secondary dark:text-gray-400 mb-1">Fakultas</label>
                <select name="fakultas_id" onchange="this.form.submit()" class="input-saas w-full px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                    <option value="">Semua Fakultas</option>
                    @foreach($fakultasList as $f)
                        <option value="{{ $f->id }}" {{ request('fakultas_id') == $f->id ? 'selected' : '' }}>{{ $f->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-siakad-secondary dark:text-gray-400 mb-1">Program Studi</label>
                <select name="prodi_id" onchange="this.form.submit()" class="input-saas w-full px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                    <option value="">Semua Prodi</option>
                    @foreach($prodiList as $p)
                        <option value="{{ $p->id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-siakad-secondary dark:text-gray-400 mb-1">Angkatan</label>
                <select name="angkatan" onchange="this.form.submit()" class="input-saas w-full px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                    <option value="">Semua Angkatan</option>
                    @foreach($angkatanList as $akt)
                        <option value="{{ $akt }}" {{ request('angkatan') == $akt ? 'selected' : '' }}>{{ $akt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-siakad-secondary dark:text-gray-400 mb-1">Jenis Pembayaran</label>
                <select name="payment_type_id" onchange="this.form.submit()" class="input-saas w-full px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                    <option value="">Semua Jenis</option>
                    @foreach($paymentTypes as $pt)
                        <option value="{{ $pt->id }}" {{ request('payment_type_id') == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary-saas flex-1 px-4 py-2 rounded-lg text-sm font-medium">
                    Filter
                </button>
                <a href="{{ route('admin.payments.dashboard') }}" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Mahasiswa -->
        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-siakad-secondary dark:text-gray-400">Total Mahasiswa</p>
                    <p class="text-2xl font-bold text-siakad-dark dark:text-white mt-1">{{ number_format($stats['total_mahasiswa']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-siakad-primary/10 dark:bg-blue-500/20 text-siakad-primary dark:text-blue-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>
            <p class="text-[11px] text-siakad-secondary/70 dark:text-gray-500 mt-3">Data mahasiswa terdaftar</p>
        </div>

        <!-- Total Sudah Lunas -->
        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Total Sudah Lunas</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($stats['total_nominal_lunas'], 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="flex items-center justify-between mt-3 text-[11px] text-siakad-secondary dark:text-gray-400">
                <span>{{ number_format($stats['total_lunas']) }} transaksi</span>
                @php
                    $pctLunas = $stats['total_tagihan'] > 0 ? round(($stats['total_lunas'] / $stats['total_tagihan']) * 100, 1) : 0;
                @endphp
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $pctLunas }}% lunas</span>
            </div>
        </div>

        <!-- Total Tunggakan -->
        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-amber-600 dark:text-amber-400">Total Tunggakan</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">Rp {{ number_format($stats['total_tunggakan'], 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-3">{{ number_format($stats['total_belum_lunas']) }} tagihan belum lunas</p>
        </div>

        <!-- Pembayaran Bulan Ini -->
        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-siakad-secondary dark:text-gray-400">Pembayaran Bulan Ini</p>
                    <p class="text-xl font-bold text-siakad-dark dark:text-white mt-1">Rp {{ number_format($stats['pembayaran_bulan_ini'], 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-siakad-secondary/10 dark:bg-gray-700/50 text-siakad-secondary dark:text-gray-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-siakad-secondary dark:text-gray-400 flex justify-between border-t border-siakad-light/50 dark:border-gray-700 pt-2">
                <span>Hari ini:</span>
                <span class="font-semibold text-siakad-dark dark:text-white">Rp {{ number_format($stats['pembayaran_hari_ini'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Tagihan Mahasiswa Yang Harus Dibayar (Urutan Berurutan) -->
    <div class="card-saas overflow-hidden dark:bg-gray-800 mb-8 border-t-4 border-t-[#234C6A]">
        <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700 flex items-center justify-between bg-siakad-light/10 dark:bg-gray-900/50">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <h3 class="font-bold text-sm text-siakad-dark dark:text-white">Daftar Tagihan Mahasiswa yang Harus Dibayar</h3>
                </div>
                <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-0.5">
                    Ketentuan berurutan: Heregistrasi &rarr; Semester 1 s/d 8. Pembayaran loncat hanya dapat dieksekusi dengan menyertakan keterangan dispensasi.
                </p>
            </div>
            <a href="{{ route('admin.payments.index', ['status' => 'unpaid']) }}" class="text-xs font-semibold text-siakad-primary hover:text-siakad-dark dark:text-blue-400 dark:hover:text-blue-300">
                Lihat Semua Tunggakan &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full table-saas text-left text-xs">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Invoice</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Mahasiswa</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Prodi</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Jenis Tagihan</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Nominal</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Status Urutan</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light/60 dark:divide-gray-700/60 text-siakad-dark dark:text-gray-300">
                    @forelse($pendingPayments as $p)
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition">
                        <td class="px-5 py-3.5 font-mono font-medium text-siakad-primary dark:text-blue-400">
                            {{ $p->invoice_number }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-siakad-dark dark:text-white">{{ $p->mahasiswa->user->name ?? '-' }}</div>
                            <div class="text-[11px] text-siakad-secondary font-mono">{{ $p->mahasiswa->nim }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            {{ $p->mahasiswa->prodi->nama ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5 font-medium">
                            {{ $p->paymentType->name ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-siakad-dark dark:text-white">Rp {{ number_format($p->amount, 0, ',', '.') }}</div>
                            @if($p->isPartial())
                                <div class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">
                                    Sisa: Rp {{ number_format($p->remaining_amount, 0, ',', '.') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            @if(isset($p->is_ready) && $p->is_ready)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Siap Bayar (Urutan Terdepan)
                                </span>
                            @elseif(isset($p->unpaid_prereq) && $p->unpaid_prereq)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200" title="Wajib melunasi {{ $p->unpaid_prereq->paymentType->name }} terlebih dahulu">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Menunggu: {{ $p->unpaid_prereq->paymentType->name }} (Wajib Keterangan jika Loncat)
                                </span>
                            @elseif($p->isPartial())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Cicilan (Sisa Rp {{ number_format($p->remaining_amount, 0, ',', '.') }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    Belum Lunas
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.payments.show', $p->id) }}" 
                               class="btn-primary-saas px-3 py-1.5 text-xs font-semibold rounded-lg inline-flex items-center gap-1">
                                <span>Konfirmasi</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-siakad-secondary dark:text-gray-400">
                            Tidak ada antrean tagihan yang harus dibayar saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Transactions Table Card -->
    <div class="card-saas overflow-hidden dark:bg-gray-800">
        <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700 flex items-center justify-between bg-siakad-light/10 dark:bg-gray-900/50">
            <div>
                <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">Aktivitas Pembayaran Terkini</h3>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">10 transaksi atau perubahan status pembayaran terbaru</p>
            </div>
            <a href="{{ route('admin.payments.index') }}" class="text-xs font-semibold text-siakad-primary hover:text-siakad-dark dark:text-blue-400 dark:hover:text-blue-300">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full table-saas text-left text-xs">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Invoice</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Mahasiswa</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Prodi</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Jenis Pembayaran</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Nominal</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Tgl Bayar / Konfirmasi</th>
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light/60 dark:divide-gray-700/60 text-siakad-dark dark:text-gray-300">
                    @forelse($recentPayments as $p)
                    <tr>
                        <td class="px-5 py-3.5 font-mono font-medium text-siakad-primary dark:text-blue-400">
                            {{ $p->invoice_number }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-siakad-dark dark:text-white">{{ $p->mahasiswa->user->name ?? '-' }}</div>
                            <div class="text-[11px] text-siakad-secondary font-mono">{{ $p->mahasiswa->nim }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            {{ $p->mahasiswa->prodi->nama ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-medium">{{ $p->paymentType->name ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-semibold">
                            Rp {{ number_format($p->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($p->isPaid())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                    ✓ Lunas
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                                    Belum Lunas
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-siakad-secondary dark:text-gray-400 text-[11px]">
                            {{ $p->payment_date ? $p->payment_date->format('d M Y') : ($p->confirmed_at ? $p->confirmed_at->format('d M Y H:i') : '-') }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.payments.show', $p->id) }}" class="btn-ghost-saas px-3 py-1 text-xs rounded-lg inline-block">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-siakad-secondary">Belum ada data transaksi pembayaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
