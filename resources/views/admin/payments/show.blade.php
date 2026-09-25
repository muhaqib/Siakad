<x-app-layout>
    <x-slot name="header">
        Detail Pembayaran Mahasiswa
    </x-slot>

    @php
        $remaining = (float) $payment->remaining_amount;
        $totalAmount = (float) $payment->amount;
        $paidAmount = (float) $payment->paid_amount;
        $percentage = $payment->paid_percentage;
        $isOutdatedOrder = isset($unpaidPrereq) && $unpaidPrereq;
    @endphp

    <div class="space-y-6" x-data="{ 
        openCancel: false, 
        actionType: 'delete',
        remainingAmount: {{ $remaining }}, 
        totalAmount: {{ $totalAmount }},
        payAmount: 0,
        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(Math.max(0, num));
        },
        setAmount(val) {
            this.payAmount = Math.min(this.remainingAmount, Math.max(0, val));
        }
    }">
        <!-- Header bar -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.payments.student', $payment->mahasiswa_id) }}" class="btn-ghost-saas p-2 rounded-xl inline-flex items-center justify-center bg-white dark:bg-gray-800 shadow-sm" title="Kembali ke Rincian Tagihan Mahasiswa">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-bold text-siakad-dark dark:text-white font-mono tracking-tight">
                            {{ $payment->invoice_number }}
                        </h1>
                        @if($payment->isPaid())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Lunas
                            </span>
                        @elseif($payment->isPartial())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Cicilan (Sisa Rp {{ number_format($remaining, 0, ',', '.') }})
                            </span>
                        @elseif($payment->status === 'cancelled')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Dibatalkan
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300">
                                Belum Ada Pembayaran
                            </span>
                        @endif
                    </div>
                    <!-- Breadcrumbs -->
                    <div class="flex items-center gap-1.5 text-[11px] text-siakad-secondary dark:text-gray-400 mt-1">
                        <a href="{{ route('admin.payments.index') }}" class="hover:underline">Daftar Mahasiswa</a>
                        <span>&rsaquo;</span>
                        <a href="{{ route('admin.payments.student', $payment->mahasiswa_id) }}" class="hover:underline">{{ $payment->mahasiswa->user->name ?? $payment->mahasiswa->nim }}</a>
                        <span>&rsaquo;</span>
                        <span class="text-siakad-dark dark:text-white font-medium">{{ $payment->paymentType->name }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($paidAmount > 0)
                    <a href="{{ route('admin.payments.receipt', $payment->id) }}" target="_blank"
                       class="btn-primary-saas px-3.5 py-2 text-xs font-semibold rounded-xl inline-flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Cetak Kuitansi Resmi</span>
                    </a>
                @endif
                @if(in_array($payment->status, ['paid', 'partial']))
                    <button type="button" @click="openCancel = true" 
                            class="px-3.5 py-2 text-xs font-semibold rounded-xl text-red-600 hover:text-red-700 dark:text-red-400 inline-flex items-center gap-1.5 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/60 hover:bg-red-100 dark:hover:bg-red-900/50 transition shadow-xs cursor-pointer"
                            title="Hapus atau Batalkan Transaksi Pembayaran Ini">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Hapus</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Session alerts -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 text-sm flex items-center gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h5 class="font-bold text-xs uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Transaksi Berhasil</h5>
                    <p class="text-xs mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-900 dark:text-red-200 text-sm flex items-center gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/60 text-red-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h5 class="font-bold text-xs uppercase tracking-wider text-red-800 dark:text-red-300">Gagal Memproses</h5>
                    <p class="text-xs mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Visual Progress Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="card-saas p-5 bg-white dark:bg-gray-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-siakad-secondary dark:text-gray-400">Total Biaya Kewajiban</p>
                    <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path></svg>
                    </span>
                </div>
                <p class="text-xl font-bold text-siakad-dark dark:text-white mt-2">Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
                <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-1">Tarif resmi sesuai ketentuan prodi</p>
            </div>

            <div class="card-saas p-5 bg-white dark:bg-gray-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Total Telah Disetor</p>
                    <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                </div>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">Rp {{ number_format($paidAmount, 0, ',', '.') }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <div class="flex-1 bg-siakad-light/60 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                    </div>
                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">{{ $percentage }}%</span>
                </div>
            </div>

            <div class="card-saas p-5 bg-white dark:bg-gray-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-amber-600 dark:text-amber-400">Sisa Tagihan (Tunggakan)</p>
                    <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                </div>
                <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-2">Rp {{ number_format($remaining, 0, ',', '.') }}</p>
                <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-1">
                    {{ $remaining > 0 ? 'Wajib dilunasi untuk aktivasi penuh' : '✓ Tagihan lunas sepenuhnya' }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Side: Kasir Pembayaran & Riwayat Setoran (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">
                
                @if(!$payment->isPaid())
                    <!-- Kasir Eksekusi Pembayaran -->
                    <div class="card-saas overflow-hidden bg-white dark:bg-gray-800 border-2 border-siakad-primary/20 dark:border-blue-500/20 shadow-sm">
                        <div class="p-5 bg-gradient-to-r from-siakad-primary/5 via-blue-50/50 to-transparent dark:from-blue-950/20 dark:via-gray-800 border-b border-siakad-light dark:border-gray-700 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-siakad-primary text-white flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-siakad-dark dark:text-white">Terminal Kasir Pembayaran</h3>
                                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Pencatatan setoran cicilan atau pelunasan tagihan mahasiswa</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-siakad-light/70 dark:bg-gray-700 text-siakad-dark dark:text-gray-200">
                                Sisa: Rp {{ number_format($remaining, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="p-6 space-y-5">
                            <!-- Out of order warning if applicable -->
                            @if($isOutdatedOrder)
                                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200 space-y-1.5">
                                    <div class="flex items-center gap-2 font-bold text-amber-950 dark:text-amber-100 text-sm">
                                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <span>Perhatian: Pembayaran Melompati Urutan</span>
                                    </div>
                                    <p class="leading-relaxed">
                                        Mahasiswa ini tercatat belum menyelesaikan tagihan sebelumnya: <strong>{{ $unpaidPrereq->paymentType->name }}</strong>.
                                    </p>
                                    <p class="font-semibold text-amber-800 dark:text-amber-300">
                                        Untuk memproses transaksi loncat ini, Anda <u>WAJIB</u> mencantumkan <strong>Catatan / Keterangan Dispensasi</strong> pada kolom catatan di bawah.
                                    </p>
                                </div>
                            @endif

                            @if($payment->hasPendingMidtrans())
                                <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-300 dark:border-blue-800 text-xs text-blue-900 dark:text-blue-200 space-y-1">
                                    <div class="flex items-center gap-2 font-bold text-blue-950 dark:text-blue-100 text-sm">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Status Midtrans: Menunggu Pembayaran Mahasiswa</span>
                                    </div>
                                    <p class="leading-relaxed">
                                        Mahasiswa telah membuat transaksi online Midtrans dengan Order ID: <span class="font-mono font-bold">{{ $payment->midtrans_order_id }}</span>. Jika mahasiswa membayar via Virtual Account / QRIS, sistem akan otomatis melunasi tagihan ini via Webhook.
                                    </p>
                                    <p class="text-blue-700 dark:text-blue-300 pt-0.5">
                                        * Jika mahasiswa akhirnya memilih membayar langsung secara tunai/manual di loket kasir, Anda tetap dapat memprosesnya melalui form di bawah ini.
                                    </p>
                                </div>
                            @endif

                            <form action="{{ route('admin.payments.confirm', $payment->id) }}" method="POST" class="space-y-5">
                                @csrf

                                <!-- Langkah 1: Input Nominal Setoran -->
                                <div>
                                    <label class="block text-xs font-bold text-siakad-dark dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                        Nominal Setoran Saat Ini (Rp) <span class="text-red-500">*</span>
                                    </label>
                                    
                                    <!-- Quick Preset Buttons -->
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <button type="button" @click="setAmount(remainingAmount)" 
                                                :class="payAmount === remainingAmount ? 'bg-siakad-primary text-white border-siakad-primary' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600'"
                                                class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition">
                                            ✓ Bayar Lunas Sisa (Rp {{ number_format($remaining, 0, ',', '.') }})
                                        </button>
                                        @if($remaining > 500000)
                                            <button type="button" @click="setAmount(Math.round(remainingAmount / 2))" 
                                                    class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition">
                                                50% Sisa (Rp {{ number_format(round($remaining / 2), 0, ',', '.') }})
                                            </button>
                                        @endif
                                        @if($remaining >= 500000)
                                            <button type="button" @click="setAmount(500000)" 
                                                    class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition">
                                                Rp 500.000
                                            </button>
                                        @endif
                                        @if($remaining >= 250000)
                                            <button type="button" @click="setAmount(250000)" 
                                                    class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition">
                                                Rp 250.000
                                            </button>
                                        @endif
                                    </div>

                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-siakad-secondary font-bold text-sm">
                                            Rp
                                        </div>
                                        <input type="number" name="pay_amount" x-model.number="payAmount" required min="1" :max="remainingAmount"
                                               class="input-saas w-full pl-10 pr-4 py-2.5 text-base font-bold text-siakad-dark dark:text-white"
                                               placeholder="Masukkan nominal setoran...">
                                    </div>
                                    <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-1">
                                        Maksimal nominal: Rp {{ number_format($remaining, 0, ',', '.') }}. Jika nominal kurang dari sisa tagihan, sisa tagihan akan otomatis berkurang dan berstatus cicilan.
                                    </p>
                                </div>

                                <!-- Langkah 2: Metode & Tanggal Transaksi -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-300 mb-1">
                                            Metode Pembayaran <span class="text-red-500">*</span>
                                        </label>
                                        <select name="payment_method" required class="input-saas w-full px-3 py-2 text-sm">
                                            <option value="Tunai">Tunai (Kasir / Bagian Keuangan)</option>
                                            <option value="Transfer">Transfer Bank (BSI / Mandiri / Rekening Kampus)</option>
                                            <option value="QRIS">QRIS Statis / Dinamis</option>
                                            <option value="Virtual Account">Virtual Account</option>
                                            <option value="Lainnya">Beasiswa / Keringanan / Lainnya</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-300 mb-1">
                                            Tanggal Transaksi <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                               class="input-saas w-full px-3 py-2 text-sm">
                                    </div>
                                </div>

                                <!-- Nomor Bukti / Ref Bank & Catatan -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-300 mb-1">
                                            Nomor Referensi Bank / No. Bukti Setor
                                        </label>
                                        <input type="text" name="reference_number" placeholder="Contoh: BSI-98721382 / Slip 019"
                                               class="input-saas w-full px-3 py-2 text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-300 mb-1">
                                            Catatan / Dispensasi
                                            @if($isOutdatedOrder)
                                                <span class="text-red-500 font-bold">* (Wajib Diisi Karena Loncat)</span>
                                            @else
                                                <span class="text-gray-400 font-normal">(Opsional)</span>
                                            @endif
                                        </label>
                                        <input type="text" name="notes" {{ $isOutdatedOrder ? 'required' : '' }}
                                               placeholder="{{ $isOutdatedOrder ? 'Wajib: Alasan dispensasi izin loncat dari pimpinan...' : 'Keterangan tambahan setoran...' }}"
                                               class="input-saas w-full px-3 py-2 text-sm">
                                    </div>
                                </div>

                                <!-- Langkah 3: Live Preview & Kalkulasi Otomatis -->
                                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-siakad-light dark:border-gray-700 space-y-2.5">
                                    <div class="flex items-center justify-between text-xs text-siakad-secondary dark:text-gray-400">
                                        <span>Sisa Tagihan Saat Ini:</span>
                                        <span class="font-semibold text-siakad-dark dark:text-white">Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-emerald-600 dark:text-emerald-400">
                                        <span>Setoran yang Diproses Sekarang:</span>
                                        <span class="font-bold">+ Rp <span x-text="formatNumber(payAmount)"></span></span>
                                    </div>
                                    <div class="pt-2 border-t border-siakad-light/70 dark:border-gray-700/80 flex items-center justify-between text-xs">
                                        <span class="font-bold text-siakad-dark dark:text-white">Sisa Tagihan Setelah Transaksi Ini:</span>
                                        <span class="font-mono font-bold text-sm" 
                                              :class="(remainingAmount - payAmount) <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                                            Rp <span x-text="formatNumber(remainingAmount - payAmount)"></span>
                                        </span>
                                    </div>

                                    <div class="pt-1 flex items-center justify-between text-xs">
                                        <span class="text-siakad-secondary">Status Hasil Transaksi:</span>
                                        <template x-if="(remainingAmount - payAmount) <= 0">
                                            <span class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                LUNAS (Full Settlement)
                                            </span>
                                        </template>
                                        <template x-if="(remainingAmount - payAmount) > 0">
                                            <span class="inline-flex items-center gap-1 font-bold text-amber-600 dark:text-amber-400">
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                Cicilan / Pembayaran Parsial (Tagihan Tetap Aktif)
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Tombol Eksekusi -->
                                <div class="pt-2">
                                    <button type="submit" 
                                            :disabled="payAmount <= 0 || payAmount > remainingAmount"
                                            :class="(remainingAmount - payAmount) <= 0 ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-siakad-primary hover:bg-siakad-dark'"
                                            class="w-full py-3 px-5 text-sm font-bold text-white rounded-xl shadow-md transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <template x-if="(remainingAmount - payAmount) <= 0">
                                            <span>Proses Pelunasan Tagihan (Lunas Penuh)</span>
                                        </template>
                                        <template x-if="(remainingAmount - payAmount) > 0">
                                            <span>Proses Pembayaran Cicilan (Rp <span x-text="formatNumber(payAmount)"></span>)</span>
                                        </template>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Tagihan Sudah Lunas Card -->
                    <div class="card-saas p-6 bg-emerald-50/50 dark:bg-emerald-950/20 border-2 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-emerald-950 dark:text-emerald-100">Kewajiban Tagihan Telah Lunas</h3>
                                <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">
                                    Seluruh nominal kewajiban tagihan ini sebesar Rp {{ number_format($totalAmount, 0, ',', '.') }} telah terverifikasi lunas.
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-emerald-200 dark:border-emerald-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                @if($payment->isPaidViaMidtrans())
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                            Midtrans Payment Gateway
                                        </span>
                                        <span class="text-xs text-emerald-800 dark:text-emerald-300 font-medium">
                                            Terkonfirmasi Otomatis ({{ $payment->confirmed_at ? $payment->confirmed_at->format('d/m/Y H:i') : '-' }})
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-emerald-900 dark:text-emerald-300 font-mono">
                                        Order ID: <span class="font-bold">{{ $payment->midtrans_order_id }}</span> &bull; Saluran: <span class="uppercase font-bold">{{ $payment->midtrans_payment_type ?? $payment->payment_method }}</span>
                                    </p>
                                @else
                                    <span class="text-xs text-emerald-800 dark:text-emerald-300">
                                        Dikonfirmasi oleh Admin/Kasir: <strong>{{ $payment->confirmedBy->name ?? 'Staf Keuangan' }}</strong> ({{ $payment->confirmed_at ? $payment->confirmed_at->format('d/m/Y H:i') : '-' }}) &bull; Metode: <strong>{{ $payment->payment_method ?? 'Tunai' }}</strong>
                                    </span>
                                @endif
                            </div>
                            <a href="{{ route('admin.payments.receipt', $payment->id) }}" target="_blank"
                               class="btn-primary-saas px-3.5 py-1.5 text-xs font-semibold rounded-lg inline-flex items-center gap-1.5 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                <span>Cetak Kuitansi</span>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Riwayat & Audit Trail Pembayaran -->
                <div class="card-saas p-6 dark:bg-gray-800 shadow-sm">
                    <div class="flex items-center justify-between border-b border-siakad-light dark:border-gray-700 pb-3 mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-siakad-light dark:bg-gray-700 text-siakad-primary dark:text-blue-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-sm font-bold text-siakad-dark dark:text-white">
                                Riwayat Setoran & Transaksi Cicilan
                            </h3>
                        </div>
                        <span class="text-xs text-siakad-secondary dark:text-gray-400">
                            {{ $payment->histories->count() }} Transaksi
                        </span>
                    </div>

                    @if($payment->histories->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead>
                                    <tr class="border-b border-siakad-light/70 dark:border-gray-700 text-siakad-secondary dark:text-gray-400">
                                        <th class="py-2.5 px-3 font-semibold">Waktu</th>
                                        <th class="py-2.5 px-3 font-semibold">Aksi</th>
                                        <th class="py-2.5 px-3 font-semibold">Nominal Disetor</th>
                                        <th class="py-2.5 px-3 font-semibold">Petugas Kasir</th>
                                        <th class="py-2.5 px-3 font-semibold">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-siakad-light/50 dark:divide-gray-700/60 text-siakad-dark dark:text-gray-300">
                                    @foreach($payment->histories as $h)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition">
                                            <td class="py-2.5 px-3 whitespace-nowrap text-siakad-secondary font-mono text-[11px]">
                                                {{ $h->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="py-2.5 px-3 whitespace-nowrap">
                                                @if($h->action === 'confirmed')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                                        Pelunasan
                                                    </span>
                                                @elseif($h->action === 'partial_payment')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                                                        Cicilan
                                                    </span>
                                                @elseif($h->action === 'cancelled')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">
                                                        Dibatalkan
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 capitalize">
                                                        {{ str_replace('_', ' ', $h->action) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3 whitespace-nowrap font-bold text-siakad-dark dark:text-white">
                                                @if($h->amount > 0)
                                                    Rp {{ number_format($h->amount, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3 whitespace-nowrap">
                                                {{ $h->performer->name ?? 'Sistem' }}
                                            </td>
                                            <td class="py-2.5 px-3 text-[11px] text-siakad-secondary dark:text-gray-400">
                                                {{ $h->notes ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 italic py-4 text-center">
                            Belum ada transaksi pembayaran untuk tagihan ini.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Right Side: Informasi Mahasiswa & Tagihan (5 Cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Info Mahasiswa Card -->
                <div class="card-saas p-6 dark:bg-gray-800 shadow-sm">
                    <h3 class="text-sm font-semibold text-siakad-dark dark:text-white border-b border-siakad-light dark:border-gray-700 pb-3 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Informasi Mahasiswa & Akademik
                    </h3>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="col-span-2">
                            <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">Nama Lengkap</span>
                            <span class="font-bold text-siakad-dark dark:text-white text-sm">{{ $payment->mahasiswa->user->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">NIM</span>
                            <span class="font-mono font-bold text-siakad-dark dark:text-white text-sm">{{ $payment->mahasiswa->nim }}</span>
                        </div>
                        <div>
                            <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">Angkatan</span>
                            <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $payment->mahasiswa->angkatan ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">Program Studi</span>
                            <span class="font-medium text-siakad-dark dark:text-gray-200">{{ $payment->mahasiswa->prodi->nama ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">Fakultas</span>
                            <span class="font-medium text-siakad-dark dark:text-gray-200">{{ $payment->mahasiswa->prodi->fakultas->nama ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Info Rincian Tagihan -->
                <div class="card-saas p-6 dark:bg-gray-800 shadow-sm">
                    <h3 class="text-sm font-semibold text-siakad-dark dark:text-white border-b border-siakad-light dark:border-gray-700 pb-3 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path></svg>
                        Rincian Tagihan & Pelunasan
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-1.5 border-b border-siakad-light/40 dark:border-gray-700/50">
                            <span class="text-siakad-secondary dark:text-gray-400">Jenis Pembayaran</span>
                            <span class="font-semibold text-siakad-dark dark:text-white">{{ $payment->paymentType->name }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-siakad-light/40 dark:border-gray-700/50">
                            <span class="text-siakad-secondary dark:text-gray-400">Kategori & Semester</span>
                            <span class="font-semibold text-siakad-dark dark:text-white">
                                {{ ucfirst($payment->paymentType->category) }} 
                                @if($payment->paymentType->semester) (Semester {{ $payment->paymentType->semester }}) @endif
                            </span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-siakad-light/40 dark:border-gray-700/50">
                            <span class="text-siakad-secondary dark:text-gray-400">Tahun Akademik</span>
                            <span class="font-semibold text-siakad-dark dark:text-white">{{ $payment->tahunAkademik->nama ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-siakad-light/40 dark:border-gray-700/50">
                            <span class="text-siakad-secondary dark:text-gray-400">Total Nominal Kewajiban</span>
                            <span class="font-bold text-sm text-siakad-dark dark:text-white">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-siakad-light/40 dark:border-gray-700/50">
                            <span class="text-siakad-secondary dark:text-gray-400">Total Sudah Dibayar</span>
                            <span class="font-bold text-sm text-emerald-600 dark:text-emerald-400">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-siakad-light/40 dark:border-gray-700/50">
                            <span class="text-siakad-secondary dark:text-gray-400">Sisa Tagihan</span>
                            <span class="font-bold text-sm text-amber-600 dark:text-amber-400">Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Academic Impact Card -->
                <div class="card-saas p-5 dark:bg-gray-800 bg-blue-50/40 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-800/60 text-xs shadow-sm">
                    <h4 class="font-bold text-siakad-dark dark:text-blue-300 mb-1.5 flex items-center gap-2">
                        <svg class="w-4 h-4 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Dampak Akademik & Pengisian KRS
                    </h4>
                    <p class="text-siakad-secondary dark:text-gray-300 leading-relaxed text-[11px]">
                        @if($payment->paymentType->category === 'semester')
                            Pelunasan penuh tagihan semester ini akan <strong>otomatis membuka akses pengisian KRS Semester {{ $payment->paymentType->semester }}</strong> bagi mahasiswa yang bersangkutan. Pembayaran bertahap/cicilan tidak membuka akses KRS kecuali ada dispensasi dari program studi.
                        @else
                            Pembayaran pendaftaran / heregistrasi adalah kewajiban prasyarat pembukaan seluruh akses rencana studi akademik.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Hapus / Batalkan Transaksi Confirmation Modal -->
        <div x-show="openCancel" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div x-show="openCancel" @click="openCancel = false" class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                <div x-show="openCancel" class="inline-block align-bottom card-saas bg-white dark:bg-gray-800 text-left overflow-hidden shadow-saas-lg transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full p-6">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-siakad-dark dark:text-white">Hapus / Batalkan Transaksi</h3>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">Pilih tindakan untuk transaksi pembayaran tagihan ini.</p>
                        </div>
                    </div>

                    <!-- Ringkasan Info Transaksi -->
                    <div class="mb-4 p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">No. Invoice:</span>
                            <span class="font-mono font-bold text-siakad-dark dark:text-white">{{ $payment->invoice_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Jenis Tagihan:</span>
                            <span class="font-semibold text-siakad-dark dark:text-white">{{ $payment->paymentType->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Total Yang Telah Disetor:</span>
                            <span class="font-mono font-bold text-red-600 dark:text-red-400">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <form action="{{ route('admin.payments.cancel', $payment->id) }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Opsi Tindakan: Hapus Transaksi vs Batalkan Transaksi -->
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                Pilih Tindakan:
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition select-none"
                                       :class="actionType === 'delete' ? 'border-red-500 bg-red-50/50 dark:bg-red-950/30 ring-1 ring-red-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                                    <input type="radio" name="action_type" value="delete" x-model="actionType" class="mt-0.5 text-red-600 focus:ring-red-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-siakad-dark dark:text-white block">Hapus Transaksi (Reset Penuh)</span>
                                        <span class="text-gray-500 dark:text-gray-400 text-[11px] block mt-0.5">
                                            Menghapus seluruh setoran, mereset tagihan kembali menjadi belum dibayar (Rp 0), serta membersihkan riwayat setoran tagihan ini.
                                        </span>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition select-none"
                                       :class="actionType === 'cancel' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 ring-1 ring-amber-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                                    <input type="radio" name="action_type" value="cancel" x-model="actionType" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-siakad-dark dark:text-white block">Batalkan Transaksi (Catat di Riwayat)</span>
                                        <span class="text-gray-500 dark:text-gray-400 text-[11px] block mt-0.5">
                                            Mengembalikan status tagihan menjadi belum dibayar dan mencatat tindakan pembatalan ini ke riwayat audit.
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Alasan Pembatalan / Penghapusan -->
                        <div>
                            <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-300 mb-1">
                                Alasan / Catatan (Opsional)
                            </label>
                            <textarea name="reason" rows="2" placeholder="Contoh: Salah input transaksi / koreksi kasir / transaksi dibatalkan..."
                                      class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-siakad-dark dark:text-white focus:ring-1 focus:ring-red-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="openCancel = false" class="btn-ghost-saas px-4 py-2 rounded-xl text-xs font-medium cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-xs font-bold rounded-xl text-white transition shadow-sm inline-flex items-center gap-1.5 cursor-pointer"
                                    :class="actionType === 'delete' ? 'bg-red-600 hover:bg-red-700' : 'bg-amber-600 hover:bg-amber-700'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span x-text="actionType === 'delete' ? 'Konfirmasi Hapus Transaksi' : 'Konfirmasi Batalkan Transaksi'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
