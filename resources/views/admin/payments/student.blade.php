<x-app-layout>
    <x-slot name="header">
        Rincian Pembayaran Mahasiswa
    </x-slot>

    <!-- Header bar -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.payments.index') }}" class="btn-ghost-saas p-2 rounded-xl inline-flex items-center justify-center bg-white dark:bg-gray-800 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-siakad-dark dark:text-white">
                        {{ $mahasiswa->user->name ?? '-' }}
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-siakad-light/70 dark:bg-gray-700 text-siakad-dark dark:text-gray-200">
                        {{ $mahasiswa->nim }}
                    </span>
                </div>
                <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">
                    {{ $mahasiswa->prodi->nama ?? '-' }} &bull; {{ $mahasiswa->prodi->fakultas->nama ?? '-' }} &bull; Angkatan {{ $mahasiswa->angkatan ?? '-' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.payments.index') }}" class="btn-ghost-saas px-3.5 py-2 text-xs font-semibold rounded-xl inline-flex items-center gap-1.5 bg-white dark:bg-gray-800 shadow-sm">
                <span>&larr; Kembali ke Daftar Mahasiswa</span>
            </a>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Status KRS & Semester Aktif Banner -->
        @if(!$krsAccess['allowed'])
            <div class="rounded-xl p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-200 flex items-start gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/60 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div class="text-xs">
                    <h5 class="font-bold text-amber-950 dark:text-amber-100">KRS Semester {{ $activeSemester }} Terkunci</h5>
                    <p class="mt-0.5 text-amber-800 dark:text-amber-300">
                        {{ $krsAccess['reason'] ?? "Mahasiswa belum melunasi kewajiban pembayaran Semester {$activeSemester}." }}
                    </p>
                </div>
            </div>
        @else
            <div class="rounded-xl p-4 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 text-emerald-900 dark:text-emerald-200 flex items-center gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="text-xs">
                    <h5 class="font-bold text-emerald-950 dark:text-emerald-100">Akses KRS Semester {{ $activeSemester }} Aktif</h5>
                    <p class="mt-0.5 text-emerald-700 dark:text-emerald-300">Kewajiban semester berjalan mahasiswa telah lunas terverifikasi.</p>
                </div>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="card-saas p-5 bg-white dark:bg-gray-800 shadow-sm">
                <p class="text-xs font-medium text-siakad-secondary dark:text-gray-400">Total Seluruh Kewajiban</p>
                <p class="text-xl font-bold text-siakad-dark dark:text-white mt-1">Rp {{ number_format($totalKewajiban, 0, ',', '.') }}</p>
                <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-2">Pendaftaran & Semester 1 s/d 8</p>
            </div>

            <div class="card-saas p-5 bg-white dark:bg-gray-800 shadow-sm">
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Total Telah Disetor</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <div class="flex-1 bg-siakad-light/50 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $persenLunas }}%"></div>
                    </div>
                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">{{ $persenLunas }}%</span>
                </div>
            </div>

            <div class="card-saas p-5 bg-white dark:bg-gray-800 shadow-sm">
                <p class="text-xs font-medium text-amber-600 dark:text-amber-400">Total Sisa Tunggakan</p>
                <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">Rp {{ number_format($sisaTunggakan, 0, ',', '.') }}</p>
                <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-2">
                    {{ $sisaTunggakan > 0 ? 'Terdapat tagihan yang belum tuntas' : '✓ Semua kewajiban telah lunas' }}
                </p>
            </div>
        </div>

        <!-- Daftar Tagihan Berurutan (Pendaftaran & Semester 1-8) -->
        <div class="card-saas overflow-hidden dark:bg-gray-800 shadow-sm">
            <div class="p-5 border-b border-siakad-light dark:border-gray-700 bg-white dark:bg-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-siakad-primary/10 dark:bg-blue-500/20 flex items-center justify-center text-siakad-primary dark:text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-siakad-dark dark:text-white">
                            Rincian Semua Tagihan Pembayaran Mahasiswa
                        </h3>
                        <p class="text-xs text-siakad-secondary dark:text-gray-400">
                            Pilih tagihan untuk membuka terminal kasir pembayaran atau mencetak bukti kuitansi
                        </p>
                    </div>
                </div>
                <span class="text-xs font-semibold text-siakad-secondary dark:text-gray-400 bg-gray-50 dark:bg-gray-700 px-3 py-1.5 rounded-lg">
                    Semester Berjalan: <strong class="text-siakad-dark dark:text-white">Semester {{ $activeSemester }}</strong>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-saas text-left text-xs">
                    <thead>
                        <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                            <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider text-center w-12">#</th>
                            <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Invoice</th>
                            <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Jenis Tagihan</th>
                            <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider text-center">Semester</th>
                            <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Nominal</th>
                            <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Sudah Dibayar</th>
                            <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Sisa</th>
                            <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Status & Urutan</th>
                            <th class="py-3 px-4 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-siakad-light/60 dark:divide-gray-700/60 text-siakad-dark dark:text-gray-300">
                        @foreach($payments as $idx => $p)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition">
                            <td class="px-4 py-3 text-center text-siakad-secondary font-mono">
                                {{ $idx + 1 }}
                            </td>
                            <td class="px-4 py-3 font-mono font-medium text-siakad-primary dark:text-blue-400">
                                <a href="{{ route('admin.payments.show', $p->id) }}" class="hover:underline">
                                    {{ $p->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 font-medium">
                                <div class="font-semibold text-siakad-dark dark:text-white">{{ $p->paymentType->name ?? '-' }}</div>
                                <div class="text-[11px] text-siakad-secondary">T.A {{ $p->tahunAkademik->nama ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($p->paymentType->semester)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-siakad-light/60 dark:bg-gray-700 text-siakad-dark dark:text-gray-300">
                                        Sem {{ $p->paymentType->semester }}
                                    </span>
                                @else
                                    <span class="text-siakad-secondary">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-bold text-siakad-dark dark:text-white">
                                Rp {{ number_format($p->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($p->paid_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 font-semibold {{ $p->remaining_amount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">
                                Rp {{ number_format($p->remaining_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($p->isPaid())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200">
                                        ✓ Lunas
                                    </span>
                                    {{-- Badge jika dibayar via Midtrans --}}
                                    @if($p->isPaidViaMidtrans())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 ml-1">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                            Online
                                        </span>
                                    @endif
                                @elseif($p->isPartial())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">
                                        Cicilan (Sisa Rp {{ number_format($p->remaining_amount, 0, ',', '.') }})
                                    </span>
                                @elseif($p->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                                        Menunggu Bank
                                    </span>
                                    {{-- Tombol konfirmasi manual untuk pembayaran Transfer yang pending --}}
                                    <div class="mt-1">
                                        <a href="{{ route('admin.payments.show', $p->id) }}"
                                           class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 dark:text-amber-400 hover:underline">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Konfirmasi Transfer Manual
                                        </a>
                                    </div>
                                @elseif($p->status === 'cancelled')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200">
                                        Dibatalkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                        Belum Bayar
                                    </span>
                                @endif

                                <!-- Sequential Indicator -->
                                @if(!$p->isPaid())
                                    @if(isset($p->is_ready) && $p->is_ready)
                                        <div class="inline-flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Siap Bayar (Urutan Terdepan)
                                        </div>
                                    @elseif(isset($p->unpaid_prereq) && $p->unpaid_prereq)
                                        <div class="inline-flex items-center gap-1 text-[10px] text-amber-700 dark:text-amber-400 font-medium mt-1" title="Harus melunasi {{ $p->unpaid_prereq->paymentType->name }} terlebih dahulu">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            <span>Menunggu {{ $p->unpaid_prereq->paymentType->name }}</span>
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.payments.show', $p->id) }}" 
                                       class="btn-primary-saas px-3 py-1.5 text-xs font-semibold rounded-lg inline-flex items-center gap-1 shadow-sm">
                                        @if(!$p->isPaid())
                                            <span>Buka Kasir / Bayar</span>
                                        @else
                                            <span>Detail Transaksi</span>
                                        @endif
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>

                                    @if($p->paid_amount > 0)
                                        <a href="{{ route('admin.payments.receipt', $p->id) }}" target="_blank"
                                           class="btn-ghost-saas p-1.5 rounded-lg text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30"
                                           title="Cetak Kuitansi Resmi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
