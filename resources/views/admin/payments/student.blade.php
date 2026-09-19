<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">Kasir Pembayaran</span>
        <span class="hidden md:inline">Kasir Pembayaran Tunai &bull; {{ $mahasiswa->user->name ?? $mahasiswa->nim }}</span>
    </x-slot>

    @php
        $unpaidPayments = $payments->filter(fn($p) => !$p->isPaid())->values();
        $billsData = $unpaidPayments->map(function ($p, $index) {
            return [
                'id' => $p->id,
                'name' => $p->paymentType->name,
                'invoice' => $p->invoice_number,
                'remaining' => (int) $p->remaining_amount,
                'amount' => $index === 0 ? (int) $p->remaining_amount : 0,
            ];
        })->values();
    @endphp

    <div
        class="space-y-6"
        x-data="cashierApp()"
    >
        <!-- Page Header Bar with Mahasiswa Info & KRS Lock Toggle -->
        <div class="card-saas p-5 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <!-- Info Mahasiswa -->
                <div class="flex items-start sm:items-center gap-3.5">
                    <a href="{{ route('admin.payments.index') }}" class="p-2.5 rounded-xl inline-flex items-center justify-center bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-siakad-dark dark:text-gray-200 transition shadow-xs" title="Kembali ke Daftar Pembayaran">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-black text-siakad-dark dark:text-white tracking-tight">
                                {{ $mahasiswa->user->name ?? '-' }}
                            </h1>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-siakad-light/80 dark:bg-gray-700 text-siakad-dark dark:text-gray-200 border border-siakad-light dark:border-gray-600">
                                {{ $mahasiswa->nim }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $mahasiswa->status === 'aktif' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ ucfirst($mahasiswa->status ?? 'aktif') }}
                            </span>
                        </div>
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                            <span>{{ $mahasiswa->prodi->nama ?? '-' }} ({{ $mahasiswa->prodi->jenjang ?? 'S1' }})</span>
                            <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                            <span>{{ $mahasiswa->prodi->fakultas->nama ?? '-' }}</span>
                            <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                            <span>Angkatan {{ $mahasiswa->angkatan ?? '-' }}</span>
                            <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                            <span class="font-bold text-siakad-primary dark:text-blue-400">Semester Berjalan: {{ $activeSemester }}</span>
                        </p>
                    </div>
                </div>

                <!-- Action Controls: KRS Lock/Unlock Button & Back Link -->
                <div class="flex flex-wrap items-center gap-2.5 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100 dark:border-gray-700">
                    <!-- Form Toggle Kunci KRS -->
                    <form action="{{ route('admin.payments.student.toggle-krs', $mahasiswa->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $mahasiswa->is_krs_unlocked ? 'mengunci kembali' : 'membuka izin (dispensasi)' }} KRS mahasiswa ini?');">
                        @csrf
                        @if($mahasiswa->is_krs_unlocked)
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-white shadow-sm transition hover:shadow cursor-pointer"
                                title="Akses KRS Terbuka karena Dispensasi. Klik untuk Kunci Kembali."
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                </svg>
                                <span>KRS Terbuka (Dispensasi Aktif)</span>
                                <span class="text-[10px] bg-emerald-700/60 px-1.5 py-0.5 rounded font-mono">Buka</span>
                            </button>
                        @else
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition hover:shadow cursor-pointer"
                                title="Akses KRS Terkunci sesuai aturan pembayaran. Klik untuk Buka Dispensasi."
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Buka KRS</span>
                                <span class="text-[10px] bg-amber-700/60 px-1.5 py-0.5 rounded font-mono">Kunci</span>
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Status Banner KRS & Pembayaran -->
        @if($mahasiswa->is_krs_unlocked)
            <div class="rounded-xl p-3.5 bg-emerald-50/90 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 text-emerald-900 dark:text-emerald-200 text-xs flex items-start gap-3 shadow-xs">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex-shrink-0 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <span class="font-bold text-emerald-950 dark:text-emerald-100">Dispensasi KRS Aktif:</span>
                    <span class="ml-1 text-emerald-800 dark:text-emerald-300">
                        Admin telah membuka akses KRS untuk mahasiswa ini secara manual. Mahasiswa dapat mengisi dan mengajukan KRS Semester {{ $activeSemester }} meskipun tagihan belum lunas.
                    </span>
                </div>
            </div>
        @elseif(!$krsAccess['allowed'])
            <div class="rounded-xl p-3.5 bg-amber-50/90 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-200 text-xs flex items-start gap-3 shadow-xs">
                <div class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex-shrink-0 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <span class="font-bold text-amber-950 dark:text-amber-100">Portal KRS Semester {{ $activeSemester }} Terkunci:</span>
                    <span class="ml-1 text-amber-800 dark:text-amber-300">{{ $krsAccess['reason'] ?? "Mahasiswa belum menyelesaikan kewajiban pembayaran untuk membuka KRS." }}</span>
                    <div class="mt-1 text-[11px] text-amber-700 dark:text-amber-400">
                        Klik tombol <strong>"KRS Terkunci (Buka Dispensasi)"</strong> di atas jika ingin mengizinkan mahasiswa mengisi KRS sekarang.
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-xl p-3.5 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 text-emerald-900 dark:text-emerald-200 text-xs flex items-center gap-3 shadow-xs">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <span class="font-bold text-emerald-950 dark:text-emerald-100">KRS Semester {{ $activeSemester }} Terbuka Otomatis:</span>
                    <span class="ml-1 text-emerald-700 dark:text-emerald-300">Seluruh kewajiban pembayaran semester berjalan telah lunas terverifikasi.</span>
                </div>
            </div>
        @endif

        <!-- Summary KPI Ringkasan Kewajiban -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="card-saas p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700">
                <p class="text-[11px] font-bold uppercase tracking-wider text-siakad-secondary dark:text-gray-400">Total Kewajiban</p>
                <p class="text-xl font-black text-siakad-dark dark:text-white mt-1 font-mono">Rp {{ number_format($totalKewajiban, 0, ',', '.') }}</p>
                <p class="text-[10px] text-siakad-secondary dark:text-gray-400 mt-1">Pendaftaran &amp; Semester 1-8</p>
            </div>

            <div class="card-saas p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700">
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Total Telah Disetor</p>
                <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</p>
                <div class="flex items-center gap-2 mt-1.5">
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-300" style="width: {{ $persenLunas }}%"></div>
                    </div>
                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">{{ $persenLunas }}%</span>
                </div>
            </div>

            <div class="card-saas p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700">
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Sisa Tunggakan</p>
                <p class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1 font-mono">Rp {{ number_format($sisaTunggakan, 0, ',', '.') }}</p>
                <p class="text-[10px] text-siakad-secondary dark:text-gray-400 mt-1">
                    {{ $sisaTunggakan > 0 ? 'Terdapat tagihan yang belum tuntas' : '✓ Semua kewajiban telah lunas' }}
                </p>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- Main 2-Column Cashier Layout (Mirip Mahasiswa Payment)     -->
        <!-- ======================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- ======================================================== -->
            <!-- KOLOM KIRI (5 Kolom): Terminal Kasir Tunai & Riwayat     -->
            <!-- ======================================================== -->
            <div class="lg:col-span-5 space-y-6">

                <!-- ---------------------------------------------------- -->
                <!-- CARD 1: TERMINAL KASIR TUNAI (CASH)                  -->
                <!-- ---------------------------------------------------- -->
                <div class="card-saas p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-siakad-dark dark:text-white">
                                    KASIR PEMBAYARAN TUNAI
    </h3>
                            </div>
                        </div>
                    </div>

                    {{-- Form Pembayaran Tunai --}}
                    <form action="{{ route('admin.payments.student.cash-pay', $mahasiswa->id) }}" method="POST" class="space-y-4">
                        @csrf

                        {{-- Info Tagihan Yang Sedang Aktif Dipilih --}}
                        <template x-if="selectedBills.length > 0">
                            <div class="p-3 rounded-xl bg-blue-50/60 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50 text-xs">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 block mb-1.5">
                                    Tagihan Dipilih (<span x-text="selectedBills.length"></span>):
                                </span>
                                <div class="space-y-1.5 divide-y divide-blue-100/70 dark:divide-blue-900/40">
                                    <template x-for="(bill, index) in selectedBills" :key="bill.id">
                                        <div class="pt-1.5 first:pt-0 flex items-center justify-between">
                                            <div>
                                                <span class="font-bold text-siakad-dark dark:text-white block" x-text="bill.name"></span>
                                                <span class="text-[10px] font-mono text-gray-500 dark:text-gray-400" x-text="bill.invoice"></span>
                                            </div>
                                            <div class="text-right">
                                                <span class="font-bold font-mono text-siakad-dark dark:text-white block" x-text="'Rp ' + formatRupiah(bill.amount)"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedBills.length === 0">
                            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-dashed border-gray-300 dark:border-gray-700 text-center text-xs text-gray-400">
                                Belum ada tagihan yang dipilih. Isi nominal pada daftar tagihan di sebelah kanan.
                            </div>
                        </template>

                        {{-- Hidden inputs to submit bills data to backend --}}
                        <template x-for="(bill, index) in bills" :key="bill.id">
                            <div>
                                <input type="hidden" :name="'bills[' + index + '][id]'" :value="bill.id">
                                <input type="hidden" :name="'bills[' + index + '][amount]'" :value="bill.amount">
                            </div>
                        </template>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                    TOTAL PEMBAYARAN TUNAI
                                </label>
                            </div>

                            <!-- Box Display Total Pembayaran Tunai -->
                            <div class="p-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50/70 dark:bg-gray-900/60 flex items-center justify-between">
                                <span class="text-base font-semibold text-gray-500 dark:text-gray-400 font-sans">
                                    Rp.
                                </span>
                                <span class="text-2xl font-black text-siakad-dark dark:text-white font-mono tracking-tight">
                                    <span x-text="formatRupiah(grandTotal)"></span>
                                </span>
                            </div>

                            <!-- Keterangan Kecil Dibawah Total: Cash / Tanpa Admin Fee -->
                            <div class="flex items-center justify-between px-1 mt-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                                <span>Pembayaran Tunai di Loket</span>
                            </div>
                        </div>

                        <!-- Tanggal Pembayaran & No Bukti Manual -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Tanggal Transaksi <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    name="payment_date"
                                    value="{{ now()->format('Y-m-d') }}"
                                    required
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-siakad-dark dark:text-white focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

                        <!-- Catatan Pembayaran -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                Catatan Kasir (Opsional)
                            </label>
                            <input
                                type="text"
                                name="notes"
                                placeholder="Contoh: Diterima tunai dari mahasiswa / wali"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-siakad-dark dark:text-white focus:ring-1 focus:ring-emerald-500"
                            />
                        </div>

                        <!-- Tombol Submit Konfirmasi Pembayaran Tunai -->
                        <button
                            type="submit"
                            :disabled="grandTotal <= 0"
                            class="w-full py-3 px-4 rounded-xl text-xs font-bold text-white shadow-sm flex items-center justify-center gap-2 transition cursor-pointer"
                            :class="grandTotal > 0 ? 'bg-emerald-600 hover:bg-emerald-700 hover:shadow' : 'bg-gray-300 dark:bg-gray-700 cursor-not-allowed text-gray-500 dark:text-gray-400'"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Konfirmasi Pembayaran Tunai</span>
                        </button>
                    </form>
                </div>

                <!-- ---------------------------------------------------- -->
                <!-- CARD 2: RIWAYAT PEMBAYARAN & CETAK KWITANSI          -->
                <!-- ---------------------------------------------------- -->
                <div class="card-saas p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-siakad-primary/10 dark:bg-blue-500/20 text-siakad-primary dark:text-blue-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-siakad-dark dark:text-white">
                                    RIWAYAT &amp; CETAK KWITANSI
                                </h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Semua transaksi berhak mendapat kwitansi</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto -mx-6 px-6">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                    <th class="pb-2.5 font-semibold w-8">NO</th>
                                    <th class="pb-2.5 font-semibold">KETERANGAN</th>
                                    <th class="pb-2.5 font-semibold">DIBAYAR</th>
                                    <th class="pb-2.5 font-semibold whitespace-nowrap">TGL</th>
                                    <th class="pb-2.5 font-semibold whitespace-nowrap">STATUS</th>
                                    <th class="pb-2.5 font-semibold text-right">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                @forelse($recentTransactions as $index => $trx)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition">
                                        <td class="py-3 text-gray-400 font-medium">{{ $index + 1 }}</td>
                                        <td class="py-3">
                                            <div class="font-semibold text-siakad-dark dark:text-white">
                                                {{ $trx->paymentType->name }}
                                            </div>
                                            <div class="text-[10px] font-mono text-gray-400">
                                                {{ $trx->invoice_number }}
                                            </div>
                                        </td>
                                        <td class="py-3 font-bold text-siakad-dark dark:text-white font-mono whitespace-nowrap">
                                            Rp {{ number_format($trx->paid_amount > 0 ? $trx->paid_amount : $trx->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 text-gray-500 dark:text-gray-400 font-mono text-[11px] whitespace-nowrap">
                                            {{ $trx->payment_date ? $trx->payment_date->format('d/m/Y') : ($trx->confirmed_at ? $trx->confirmed_at->format('d/m/Y') : $trx->updated_at->format('d/m/Y')) }}
                                        </td>
                                        <td class="py-3 whitespace-nowrap">
                                            @if($trx->isPaid())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300">
                                                    Lunas
                                                </span>
                                            @elseif($trx->status === 'pending')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">
                                                    Pending
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300">
                                                    Cicilan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-right whitespace-nowrap">
                                            {{-- Kwitansi button is accessible for any payment with paid_amount > 0 or status partial / paid --}}
                                            @if($trx->isPaid() || (float)$trx->paid_amount > 0 || $trx->status === 'partial')
                                                <a href="{{ route('admin.payments.receipt', $trx->id) }}" target="_blank" class="inline-flex items-center gap-1 rounded-md border border-emerald-500/20 bg-emerald-500/10 px-2 py-1 text-[11px] font-semibold text-emerald-700 dark:border-emerald-400/30 dark:bg-emerald-500/10 dark:text-emerald-300 transition hover:bg-emerald-500/20" title="Cetak Kwitansi">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                                    </svg>
                                                    <span>Kwitansi</span>
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.payments.show', $trx->id) }}" class="inline-flex items-center gap-1 rounded-md border border-gray-200 dark:border-gray-700 px-2 py-1 text-[11px] text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition ml-1" title="Rincian Transaksi">
                                                <span>Detail</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-xs text-gray-400 italic">
                                            Belum ada pembayaran yang disetor oleh mahasiswa ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- ======================================================== -->
            <!-- KOLOM KANAN (7 Kolom): Daftar Tagihan Berurutan         -->
            <!-- ======================================================== -->
            <div class="lg:col-span-7">
                <div class="card-saas p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pb-1">
                        <div>
                            <h2 class="text-base font-bold text-siakad-dark dark:text-white">
                                Daftar Tagihan Mahasiswa
                            </h2>
                        </div>
                    </div>

                    <!-- Daftar List Tagihan Mahasiswa (Stack) -->
                    <div class="space-y-2.5 mt-4">
                        @php
                            $unpaidCounter = 0;
                        @endphp

                        @foreach($payments as $p)
                            @php
                                $isPaid = $p->isPaid();
                                $unpaidIndex = null;
                                if (! $isPaid) {
                                    $unpaidIndex = $unpaidCounter;
                                    $unpaidCounter++;
                                }
                            @endphp

                            @if($isPaid)
                                <!-- Baris Tagihan: LUNAS -->
                                <div class="p-3.5 rounded-xl border border-emerald-200/80 dark:border-emerald-900/50 bg-emerald-50/40 dark:bg-emerald-950/20 flex items-center justify-between transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-xs font-bold text-emerald-800 dark:text-emerald-300 line-through decoration-emerald-500">
                                                    {{ $p->paymentType->name }}
                                                </h4>
                                                @if($p->paymentType->semester)
                                                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 font-semibold">Sem {{ $p->paymentType->semester }}</span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                LUNAS (Rp {{ number_format($p->amount, 0, ',', '.') }})
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.payments.receipt', $p->id) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-100/80 dark:bg-emerald-900/40 hover:bg-emerald-200 px-2.5 py-1 rounded-lg transition" title="Cetak Kwitansi">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span>Kwitansi</span>
                                        </a>
                                    </div>
                                </div>

                            @else
                                <!-- Baris Tagihan: AKTIF / TERBUKA jika isUnlocked -->
                                <div
                                    x-show="isUnlocked({{ $unpaidIndex }})"
                                    class="p-4 rounded-xl border-2 border-emerald-400 dark:border-emerald-600 bg-emerald-50/20 dark:bg-emerald-950/10 flex flex-col gap-3 shadow-sm transition"
                                >
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="flex items-start gap-3">
                                            <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h4 class="text-xs font-bold text-siakad-dark dark:text-white uppercase tracking-tight">
                                                        {{ $p->paymentType->name }}
                                                    </h4>
                                                    @if($p->paymentType->semester)
                                                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold">Sem {{ $p->paymentType->semester }}</span>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider block">
                                                    {{ $p->status === 'partial' ? 'Sedang Dicicil' : 'Siap Bayar' }} &bull; Inv: {{ $p->invoice_number }}
                                                </span>
                                                <span class="text-xs font-bold text-siakad-dark dark:text-white mt-1 block">
                                                    SISA <strong class="text-sm font-black">Rp {{ number_format($p->remaining_amount, 0, ',', '.') }}</strong>
                                                    @if($p->paid_amount > 0)
                                                        <span class="text-[11px] font-normal text-gray-500 dark:text-gray-400">(Telah dibayar: Rp {{ number_format($p->paid_amount, 0, ',', '.') }})</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>

                                        @if($p->paid_amount > 0)
                                            <div>
                                                <a href="{{ route('admin.payments.receipt', $p->id) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/40 hover:bg-emerald-200 px-2.5 py-1 rounded-lg transition" title="Cetak Kwitansi Setoran Sebagian">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                    <span>Kwitansi Cicilan</span>
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Bagian Input Nominal & Tombol PENUH -->
                                    <div class="pt-2 border-t border-emerald-200/60 dark:border-emerald-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div>
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                                Nominal Setoran Tunai:
                                            </span>
                                            <div class="inline-flex items-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 overflow-hidden shadow-sm">
                                                <span class="px-2.5 py-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700">
                                                    Rp
                                                </span>
                                                <input
                                                    type="number"
                                                    x-model.number="bills[{{ $unpaidIndex }}].amount"
                                                    @input="onAmountInput({{ $unpaidIndex }})"
                                                    min="0"
                                                    max="{{ (int) $p->remaining_amount }}"
                                                    placeholder="0"
                                                    class="w-28 sm:w-36 px-2.5 py-1.5 text-xs font-bold text-siakad-dark dark:text-white text-right border-0 focus:ring-0 focus:outline-none bg-transparent"
                                                />
                                                <button
                                                    type="button"
                                                    @click="setBillFull({{ $unpaidIndex }})"
                                                    class="px-2.5 py-1.5 text-[10px] font-bold uppercase bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 border-l border-gray-200 dark:border-gray-700 transition cursor-pointer"
                                                >
                                                    PENUH
                                                </button>
                                            </div>
                                            <template x-if="bills[{{ $unpaidIndex }}]?.amount > 0 && bills[{{ $unpaidIndex }}]?.amount < bills[{{ $unpaidIndex }}]?.remaining">
                                                <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1.5 font-medium">
                                                    Sisa tagihan nanti: Rp <span x-text="formatRupiah(bills[{{ $unpaidIndex }}].remaining - bills[{{ $unpaidIndex }}].amount)"></span> (Tagihan berikutnya tetap terkunci).
                                                </p>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Baris Tagihan: TERKUNCI jika !isUnlocked -->
                                <div
                                    x-show="!isUnlocked({{ $unpaidIndex }})"
                                    class="p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between opacity-60 cursor-not-allowed"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-gray-300 dark:bg-gray-600 text-white flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400">
                                                {{ $p->paymentType->name }}
                                            </h4>
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500">
                                                Terkunci
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 bg-gray-200 dark:bg-gray-700 px-2 py-0.5 rounded">
                                        Terkunci
                                    </span>
                                </div>
                            @endif

                        @endforeach
                    </div>
                </div>
            </div>

        </div>

    </div>

    @push('scripts')
    <script>
        function cashierApp() {
            return {
                bills: @json($billsData),

                isUnlocked(index) {
                    if (index === 0) return true;
                    const prevBill = this.bills[index - 1];
                    if (!prevBill) return false;
                    return prevBill.amount === prevBill.remaining;
                },

                get selectedBills() {
                    return this.bills.filter(b => b.amount > 0);
                },

                get grandTotal() {
                    return this.selectedBills.reduce((acc, b) => acc + (parseInt(b.amount) || 0), 0);
                },

                setBillFull(index) {
                    const bill = this.bills[index];
                    if (!bill) return;
                    bill.amount = bill.remaining;
                },

                onAmountInput(index) {
                    const bill = this.bills[index];
                    if (!bill) return;
                    let val = parseInt(bill.amount) || 0;
                    if (val < 0) val = 0;
                    if (val > bill.remaining) val = bill.remaining;
                    bill.amount = val;

                    // If not full, lock subsequent bills and reset them to 0
                    if (val < bill.remaining) {
                        for (let i = index + 1; i < this.bills.length; i++) {
                            this.bills[i].amount = 0;
                        }
                    }
                },

                formatRupiah(num) {
                    return new Intl.NumberFormat('id-ID').format(num || 0);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
