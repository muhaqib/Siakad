<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">Pembayaran</span>
        <span class="hidden md:inline">Pembayaran / Transfer</span>
    </x-slot>

    @php
        $firstUnpaid = $payments->first(fn($p) => !$p->isPaid());
        $pendingPayment = $payments->firstWhere('status', 'pending');
        $activePayment = $pendingPayment ?? $firstUnpaid;
        $initialAmount = $activePayment ? (int) $activePayment->remaining_amount : 0;
        $initialIsPaying = $pendingPayment ? 'true' : 'false';
    @endphp

    <div
        class="space-y-6"
        x-data="paymentApp()"
        x-init="init()"
    >
        <!-- Page Header -->
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-siakad-dark dark:text-white">
                    Pembayaran / Transfer
                </h1>
                <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-0.5">
                    Pembayaran tagihan mahasiswa
                </p>
            </div>
        </div>

        <!-- Status KRS & Semester Aktif (Compact Alert) -->
        @if(!$krsAccess['allowed'])
            <div class="rounded-xl p-3.5 bg-amber-50/90 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-200 text-xs flex items-start gap-3">
                <div class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex-shrink-0 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <span class="font-bold">KRS Semester {{ $activeSemester }} Belum Terbuka:</span>
                    <span class="ml-1 text-amber-800 dark:text-amber-300">{{ $krsAccess['reason'] ?? "Selesaikan pembayaran semester {$activeSemester} untuk mengaktifkan portal KRS." }}</span>
                </div>
            </div>
        @endif

        <!-- Main 2-Column Layout (Sesuai Referensi pembayaran.png & setelahklik.png) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- ======================================================== -->
            <!-- KOLOM KIRI (5 Kolom): Terminal Bayar & Riwayat          -->
            <!-- ======================================================== -->
            <div class="lg:col-span-5 space-y-6">

                <!-- ---------------------------------------------------- -->
                <!-- CARD 1A: TAMPILAN AWAL SEBELUM KLIK (pembayaran.png) -->
                <!-- ---------------------------------------------------- -->
                <div
                    x-show="!isPaying"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-98"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    class="card-saas p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700"
                >
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-siakad-secondary dark:text-gray-400">
                            BAYAR VIA TRANSFER & MIDTRANS
                        </h3>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200/70 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Midtrans Online
                        </span>
                    </div>

                    {{-- Info Tagihan Yang Sedang Aktif Dipilih --}}
                    <template x-if="activePaymentName">
                        <div class="mb-4 p-3 rounded-xl bg-blue-50/60 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50 text-xs">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 block mb-0.5">
                                Tagihan Dipilih:
                            </span>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-siakad-dark dark:text-white" x-text="activePaymentName"></span>
                                <span class="text-[11px] font-mono text-gray-500 dark:text-gray-400" x-text="activePaymentInvoice"></span>
                            </div>
                        </div>
                    </template>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                TOTAL PEMBAYARAN
                            </label>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded"
                                  :class="totalPayAmount < activePaymentRemaining ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200'"
                                  x-text="totalPayAmount < activePaymentRemaining ? 'Cicilan' : 'Pelunasan Penuh'">
                            </span>
                        </div>

                        <!-- Box Display Total Pembayaran -->
                        <div class="p-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50/70 dark:bg-gray-900/60 flex items-center justify-between">
                            <span class="text-base font-semibold text-gray-500 dark:text-gray-400 font-sans">
                                Rp.
                            </span>
                            <span class="text-2xl font-black text-siakad-dark dark:text-white font-mono tracking-tight">
                                <span x-text="formatRupiah(totalPayAmount)"></span>
                            </span>
                        </div>

                        <!-- Input Nominal Yang Dibayar (Bisa Cicil) -->
                        <div class="mt-3.5">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[11px] font-bold text-siakad-dark dark:text-gray-200">
                                    Nominal Yang Dibayar:
                                </span>
                                <span class="text-[10px] text-siakad-secondary dark:text-gray-400">
                                    Sisa Tagihan: Rp <strong x-text="formatRupiah(activePaymentRemaining)"></strong>
                                </span>
                            </div>
                            <div class="inline-flex items-center w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 overflow-hidden shadow-sm">
                                <span class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700">Rp</span>
                                <input
                                    type="number"
                                    x-model.number="totalPayAmount"
                                    @input="validateNominal()"
                                    :disabled="isPaying"
                                    min="10000"
                                    :max="activePaymentRemaining"
                                    placeholder="Masukkan nominal cicilan"
                                    class="w-full px-3 py-2 text-sm font-bold text-siakad-dark dark:text-white border-0 focus:ring-0 focus:outline-none bg-transparent"
                                />
                                <button
                                    type="button"
                                    @click="setPenuh(activePaymentRemaining)"
                                    :disabled="isPaying"
                                    class="px-3 py-2 text-[10px] font-bold uppercase bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 border-l border-gray-200 dark:border-gray-700 transition cursor-pointer"
                                >
                                    LUNAS
                                </button>
                            </div>
                            <template x-if="totalPayAmount > 0 && totalPayAmount < activePaymentRemaining">
                                <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1.5 flex items-center justify-between font-medium">
                                    <span>✓ Mode Pembayaran Cicilan</span>
                                    <span>Sisa tagihan nanti: Rp <span x-text="formatRupiah(activePaymentRemaining - totalPayAmount)"></span></span>
                                </p>
                            </template>
                        </div>
                    </div>

                    {{-- Saluran Pembayaran Yang Tersedia --}}
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/80">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                            Metode Transfer & Pembayaran:
                        </p>
                        <div class="flex flex-wrap items-center gap-1.5 text-[10px] font-bold">
                            <span class="px-2 py-1 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">BSI</span>
                            <span class="px-2 py-1 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">BCA</span>
                            <span class="px-2 py-1 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">Mandiri</span>
                            <span class="px-2 py-1 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">BRI</span>
                            <span class="px-2 py-1 rounded bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-800/60">BNI</span>
                            <span class="px-2 py-1 rounded bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60">QRIS</span>
                            <span class="px-2 py-1 rounded bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60">GoPay / E-Wallet</span>
                        </div>
                    </div>

                    <!-- Tombol Eksekusi Bayar Transfer melalui Midtrans -->
                    <button
                        type="button"
                        @click="proceedToPay()"
                        :disabled="isLoading || totalPayAmount <= 0"
                        class="w-full mt-5 py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-siakad-dark hover:bg-siakad-primary transition duration-150 shadow-sm hover:shadow flex items-center justify-center gap-2.5 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        <template x-if="isLoading">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </template>
                        <template x-if="!isLoading">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </template>
                        <span x-text="isLoading ? 'Menyiapkan Midtrans...' : (totalPayAmount < activePaymentRemaining ? 'Bayar Cicilan via Midtrans' : 'Bayar Transfer melalui Midtrans')"></span>
                    </button>
                </div>

                <!-- ---------------------------------------------------- -->
                <!-- CARD 1B: TAMPILAN SETELAH KLIK BAYAR (setelahklik.png)-->
                <!-- ---------------------------------------------------- -->
                <div
                    x-show="isPaying"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-98"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    class="card-saas p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700 space-y-4"
                >
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-siakad-secondary dark:text-gray-400">
                            VIRTUAL ACCOUNT & QRIS
                        </h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">
                            Midtrans & Bank BSI
                        </span>
                    </div>

                    <!-- Yellow Notice: Segera Lakukan Pembayaran -->
                    <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 flex items-center gap-2.5 text-xs font-medium">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Segera lakukan pembayaran sebelum batas waktu berakhir</span>
                    </div>

                    <!-- Tombol Cepat Buka Layar Midtrans Snap (Paling Menonjol) -->
                    <button
                        type="button"
                        @click="openSnapPopup()"
                        class="w-full py-3 px-4 rounded-xl text-xs font-bold text-white bg-siakad-primary hover:bg-siakad-dark transition shadow-md flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Buka Layar Pembayaran Transfer Midtrans (Snap Popup)
                    </button>

                    <!-- Card Total Pembayaran & Subtitle -->
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-9 rounded-lg bg-emerald-700 text-white flex items-center justify-center font-black text-xs tracking-tight shadow-sm">
                                BSI
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                    TOTAL PEMBAYARAN
                                </p>
                                <h3 class="text-2xl font-black text-siakad-dark dark:text-white font-mono mt-0.5">
                                    Rp <span x-text="formatRupiah(totalPayAmount)"></span>
                                </h3>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-2.5 pt-2 border-t border-gray-200/70 dark:border-gray-700">
                            <span x-text="activePaymentName"></span> (Rp <span x-text="formatRupiah(totalPayAmount)"></span>)
                        </p>
                    </div>

                    <!-- Cara Pembayaran Section -->
                    <div class="space-y-3 pt-1">
                        <h4 class="text-xs font-bold text-siakad-dark dark:text-white uppercase tracking-wider">
                            CARA PEMBAYARAN
                        </h4>

                        <!-- Tabs Navigasi Saluran Bayar -->
                        <div class="flex flex-wrap gap-1 p-1 bg-gray-100 dark:bg-gray-900/70 rounded-xl">
                            <button
                                type="button"
                                @click="paymentTab = 'bsi_mobile'"
                                :class="paymentTab === 'bsi_mobile' ? 'bg-siakad-dark text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-siakad-dark'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                            >
                                BSI Mobile
                            </button>
                            <button
                                type="button"
                                @click="paymentTab = 'atm_bsi'"
                                :class="paymentTab === 'atm_bsi' ? 'bg-siakad-dark text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-siakad-dark'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                            >
                                ATM BSI
                            </button>
                            <button
                                type="button"
                                @click="paymentTab = 'qris'"
                                :class="paymentTab === 'qris' ? 'bg-siakad-dark text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-siakad-dark'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                            >
                                QRIS / E-Wallet
                            </button>
                            <button
                                type="button"
                                @click="paymentTab = 'bank_lain'"
                                :class="paymentTab === 'bank_lain' ? 'bg-siakad-dark text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-siakad-dark'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                            >
                                Bank Lain
                            </button>
                        </div>

                        <!-- Instruksi Tab 1: BSI Mobile -->
                        <div x-show="paymentTab === 'bsi_mobile'" class="text-xs text-siakad-secondary dark:text-gray-300 space-y-2">
                            <p>1. Buka aplikasi <strong>BSI Mobile / BYOND</strong>, masuk ke menu <strong>Pembayaran</strong></p>
                            <p>2. Pilih <strong>Akademik</strong></p>
                            <p>3. Cari dan pilih <strong>STIT MAMBAUL HIKMAH</strong></p>
                            <div>
                                <p>4. Masukkan Nomor Pembayaran:</p>
                                <div class="mt-1.5 p-3 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-between font-mono text-lg font-bold text-siakad-dark dark:text-white">
                                    <span x-text="vaNumber"></span>
                                    <button
                                        type="button"
                                        @click="copyToClipboard(vaNumber)"
                                        class="p-1.5 px-2.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-siakad-primary transition text-xs flex items-center gap-1.5 font-sans font-medium"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-1">
                                    Cukup NIM saja (tanpa titik) — karena institusi STIT Mambaul Hikmah sudah dipilih.
                                </p>
                            </div>
                            <p>5. Periksa nama & nominal tagihan, lalu konfirmasi pembayaran.</p>
                        </div>

                        <!-- Instruksi Tab 2: ATM BSI -->
                        <div x-show="paymentTab === 'atm_bsi'" x-cloak class="text-xs text-siakad-secondary dark:text-gray-300 space-y-2">
                            <p>1. Masukkan kartu ATM BSI dan PIN Anda.</p>
                            <p>2. Pilih menu <strong>Pembayaran / Pembelian &gt; Akademik</strong>.</p>
                            <p>3. Masukkan kode institusi STIT Mambaul Hikmah dan Nomor Pembayaran:</p>
                            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-between font-mono text-lg font-bold text-siakad-dark dark:text-white">
                                <span x-text="vaNumber"></span>
                                <button type="button" @click="copyToClipboard(vaNumber)" class="text-xs font-sans font-medium text-siakad-primary dark:text-blue-400">Salin</button>
                            </div>
                            <p>4. Periksa detail tagihan pada layar ATM lalu tekan YA untuk membayar.</p>
                        </div>

                        <!-- Instruksi Tab 3: QRIS / E-Wallet -->
                        <div x-show="paymentTab === 'qris'" x-cloak class="text-xs text-siakad-secondary dark:text-gray-300 space-y-2.5">
                            <p>Bayar instan menggunakan QRIS dengan scan langsung lewat aplikasi e-wallet favorit Anda (GoPay, ShopeePay, DANA, OVO, LinkAja) atau m-Banking.</p>
                            <button
                                type="button"
                                @click="openSnapPopup()"
                                class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                Buka QR Code QRIS (Midtrans)
                            </button>
                        </div>

                        <!-- Instruksi Tab 4: Bank Lain / Virtual Account -->
                        <div x-show="paymentTab === 'bank_lain'" x-cloak class="text-xs text-siakad-secondary dark:text-gray-300 space-y-2">
                            <p>1. Buka m-Banking atau ATM bank Anda (BCA, Mandiri, BNI, BRI, Permata, dll).</p>
                            <p>2. Pilih menu <strong>Transfer Antar Bank / Virtual Account</strong>.</p>
                            <p>3. Klik tombol di bawah untuk menampilkan nomor Virtual Account resmi dari Midtrans:</p>
                            <button
                                type="button"
                                @click="openSnapPopup()"
                                class="w-full py-2.5 px-3 rounded-xl bg-siakad-primary hover:bg-siakad-dark text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Tampilkan Virtual Account Midtrans
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Cepat Buka Layar Midtrans Snap (QRIS, VA Bank, GoPay) -->
                    <div class="pt-2">
                        <button
                            type="button"
                            @click="openSnapPopup()"
                            class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-siakad-primary hover:bg-siakad-dark transition shadow-sm flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Buka Layar Pembayaran (Midtrans Snap)
                        </button>
                    </div>

                    <!-- Green Notice: Pembayaran Tercatat Otomatis -->
                    <div class="p-3.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 text-xs leading-relaxed">
                        Pembayaran tercatat <strong>otomatis</strong> dalam hitungan detik. Halaman ini akan memperbarui diri sendiri — tidak perlu mengirim bukti transfer.
                    </div>

                    <!-- Tombol Batalkan -->
                    <div class="text-center pt-2">
                        <button
                            type="button"
                            @click="cancelPayment()"
                            class="text-xs font-bold text-red-600 hover:text-red-700 dark:text-red-400 py-1 transition"
                        >
                            Batalkan
                        </button>
                    </div>
                </div>

                <!-- ---------------------------------------------------- -->
                <!-- CARD 2: RIWAYAT PEMBAYARAN (pembayaran.png)          -->
                <!-- ---------------------------------------------------- -->
                <div class="card-saas p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700 overflow-hidden">
                    <h3 class="text-sm font-bold text-siakad-dark dark:text-white mb-4">
                        Riwayat Pembayaran
                    </h3>

                    <div class="overflow-x-auto -mx-6 px-6">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                    <th class="pb-2.5 font-semibold w-8">NO</th>
                                    <th class="pb-2.5 font-semibold">KETERANGAN</th>
                                    <th class="pb-2.5 font-semibold">TOTAL</th>
                                    <th class="pb-2.5 font-semibold whitespace-nowrap">TGL TRANS.</th>
                                    <th class="pb-2.5 font-semibold whitespace-nowrap">STATUS</th>
                                    <th class="pb-2.5 font-semibold text-right">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                @forelse($recentTransactions as $index => $trx)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition">
                                        <td class="py-3 text-gray-400 font-medium">{{ $index + 1 }}</td>
                                        <td class="py-3 font-semibold text-siakad-dark dark:text-white">
                                            {{ $trx->paymentType->name }}
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
                                                    Terverifikasi
                                                </span>
                                            @elseif($trx->status === 'pending')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">
                                                    Menunggu Pembayaran
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300">
                                                    Cicilan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-right whitespace-nowrap">
                                            @if($trx->isPaid())
                                                <a href="{{ route('mahasiswa.payments.receipt', $trx->id) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-md border border-siakad-primary/20 bg-siakad-primary/5 px-2 py-1 text-[11px] font-semibold text-siakad-primary dark:border-blue-400/30 dark:bg-blue-500/10 dark:text-blue-300 transition hover:bg-siakad-primary/10 hover:border-siakad-primary/30" aria-label="Download kwitansi">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17.5V18a2 2 0 002 2h12a2 2 0 002-2v-.5"/>
                                                    </svg>
                                                </a>
                                            @else
                                                <a href="{{ route('mahasiswa.payments.show', $trx->id) }}" class="text-siakad-secondary dark:text-gray-400 hover:underline text-[11px]">
                                                    Rincian
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-xs text-gray-400 italic">
                                            Belum ada riwayat pembayaran yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- ======================================================== -->
            <!-- KOLOM KANAN (7 Kolom): Daftar Tagihan                   -->
            <!-- ======================================================== -->
            <div class="lg:col-span-7">
                <div class="card-saas p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pb-1">
                        <div>
                            <h2 class="text-base font-bold text-siakad-dark dark:text-white">
                                Daftar Tagihan
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Isi nominal pada tagihan yang ingin dibayar
                            </p>
                        </div>
                    </div>

                    <!-- Blue Info Box: Tagihan Dibayar Berurutan -->
                    <div class="mt-4 rounded-xl p-3.5 bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/50 text-blue-900 dark:text-blue-200 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="leading-relaxed">
                            Tagihan dibayar berurutan. Kolom nominal tagihan berikutnya terbuka setelah tagihan sebelumnya <strong>LUNAS</strong> — atau saat Anda mengisinya penuh di halaman ini.
                        </p>
                    </div>

                    <!-- Daftar List Tagihan Mahasiswa (Stack) -->
                    <div class="space-y-2.5 mt-4">
                        @php
                            $hasUnlockedUnpaid = false;
                        @endphp

                        @foreach($payments as $p)
                            @php
                                $isPaid = $p->isPaid();
                                $isActive = false;
                                if (! $isPaid && ! $hasUnlockedUnpaid) {
                                    $isActive = true;
                                    $hasUnlockedUnpaid = true;
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
                                            <h4 class="text-xs font-bold text-emerald-800 dark:text-emerald-300 line-through decoration-emerald-500">
                                                {{ $p->paymentType->name }}
                                            </h4>
                                            <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                LUNAS
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 tracking-wider">
                                            LUNAS
                                        </span>
                                    </div>
                                </div>

                            @elseif($isActive)
                                <!-- Baris Tagihan: AKTIF (Siap Dibayar via Transfer / Midtrans) -->
                                <div class="p-4 rounded-xl border-2 border-emerald-400 dark:border-emerald-600 bg-emerald-50/20 dark:bg-emerald-950/10 flex flex-col gap-3 shadow-sm transition">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="flex items-start gap-3">
                                            <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-bold text-siakad-dark dark:text-white uppercase tracking-tight">
                                                    {{ $p->paymentType->name }}
                                                </h4>
                                                <span class="text-[10px] font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider block">
                                                    {{ $p->status === 'pending' ? 'MENUNGGU PEMBAYARAN' : 'SIAP DIBAYAR' }}
                                                </span>
                                                <span class="text-xs font-bold text-siakad-dark dark:text-white mt-1 block">
                                                    SISA <strong class="text-sm font-black">Rp {{ number_format($p->remaining_amount, 0, ',', '.') }}</strong>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Rincian Link -->
                                        <a href="{{ route('mahasiswa.payments.show', $p->id) }}" class="text-[11px] text-siakad-primary dark:text-blue-400 hover:underline font-semibold flex items-center gap-1 self-start sm:self-auto">
                                            Lihat Rincian &rarr;
                                        </a>
                                    </div>

                                    <!-- Bagian Input Nominal & Tombol Eksekusi Bayar Transfer Midtrans -->
                                    <div class="pt-2 border-t border-emerald-200/60 dark:border-emerald-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div>
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                                Nominal Cicilan / Pelunasan:
                                            </span>
                                            <div class="inline-flex items-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 overflow-hidden shadow-sm">
                                                <span class="px-2.5 py-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700">
                                                    Rp
                                                </span>
                                                <input
                                                    type="number"
                                                    x-model.number="totalPayAmount"
                                                    @input="validateNominal()"
                                                    :disabled="isPaying"
                                                    min="10000"
                                                    max="{{ (int) $p->remaining_amount }}"
                                                    class="w-28 sm:w-32 px-2.5 py-1.5 text-xs font-bold text-siakad-dark dark:text-white text-right border-0 focus:ring-0 focus:outline-none bg-transparent"
                                                />
                                                <button
                                                    type="button"
                                                    @click="setPenuh({{ (int) $p->remaining_amount }})"
                                                    :disabled="isPaying"
                                                    class="px-2.5 py-1.5 text-[10px] font-bold uppercase bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 border-l border-gray-200 dark:border-gray-700 transition cursor-pointer"
                                                >
                                                    PENUH
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Tombol Langsung Bayar Transfer Melalui Midtrans -->
                                        <button
                                            type="button"
                                            @click="selectAndPay({{ $p->id }}, {{ (int) $p->remaining_amount }}, '{{ addslashes($p->paymentType->name) }}', '{{ $p->invoice_number }}')"
                                            :disabled="isLoading || isPaying || totalPayAmount <= 0"
                                            class="py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-siakad-primary hover:bg-siakad-dark transition shadow-sm hover:shadow flex items-center justify-center gap-1.5 disabled:opacity-50 cursor-pointer self-end sm:self-auto"
                                        >
                                            <svg class="w-3.5 h-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                            <span x-text="totalPayAmount < activePaymentRemaining ? 'Bayar Cicilan (Rp ' + formatRupiah(totalPayAmount) + ')' : 'Bayar Sekarang (Lunas)'"></span>
                                        </button>
                                    </div>
                                </div>

                            @else
                                <!-- Baris Tagihan: BELUM GILIRANNYA (Terkunci) -->
                                <div class="p-3.5 rounded-xl border border-gray-200/70 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/30 flex items-center justify-between opacity-70">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            &ndash;
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                                                {{ $p->paymentType->name }}
                                            </h4>
                                            <span class="text-[10px] text-gray-400 uppercase">
                                                BELUM BAYAR
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('mahasiswa.payments.show', $p->id) }}" class="text-[11px] text-gray-400 hover:text-siakad-primary dark:hover:text-gray-200 hover:underline">
                                            Rincian
                                        </a>
                                        <span class="text-gray-400 text-base font-bold pr-1">&mdash;</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Footer Summary: SISA TOTAL & AKAN DIBAYAR -->
                    <div class="pt-5 mt-6 border-t border-gray-100 dark:border-gray-700 space-y-2">
                        <div class="flex items-center justify-between text-xs text-siakad-secondary dark:text-gray-400">
                            <span class="font-semibold uppercase tracking-wider">SISA TOTAL</span>
                            <span class="font-bold text-sm text-siakad-dark dark:text-white font-mono">
                                Rp {{ number_format($totalTunggakan, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs font-bold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                                AKAN DIBAYAR
                            </span>
                            <span class="text-2xl font-black text-siakad-dark dark:text-white font-mono">
                                Rp <span x-text="formatRupiah(totalPayAmount)"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

{{-- ============================================================ --}}
{{-- Midtrans Snap.js & Alpine.js Logic                           --}}
{{-- ============================================================ --}}
@push('scripts')
<script src="{{ config('midtrans.base_url.snap_js') }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
function paymentApp() {
    return {
        isPaying: {{ $initialIsPaying }},
        isLoading: false,
        copied: false,
        paymentTab: 'bsi_mobile',
        selectedPaymentId: {{ $activePayment ? $activePayment->id : 'null' }},
        activePaymentName: '{{ $activePayment ? addslashes($activePayment->paymentType->name) : "" }}',
        activePaymentInvoice: '{{ $activePayment ? $activePayment->invoice_number : "" }}',
        activePaymentRemaining: {{ $activePayment ? (int) $activePayment->remaining_amount : 0 }},
        totalPayAmount: {{ $initialAmount }},
        vaNumber: '{{ $activePayment && $activePayment->midtrans_order_id ? $activePayment->midtrans_order_id : $mahasiswa->nim }}',
        snapToken: '{{ $activePayment ? $activePayment->midtrans_token : "" }}',

        init() {
            if (this.isPaying && this.snapToken) {
                console.info('Pembayaran aktif ditemukan.');
            }
        },

        formatRupiah(num) {
            return new Intl.NumberFormat('id-ID').format(Math.max(0, num || 0));
        },

        validateNominal() {
            if (this.totalPayAmount > this.activePaymentRemaining) {
                this.totalPayAmount = this.activePaymentRemaining;
            }
            if (this.totalPayAmount < 0) {
                this.totalPayAmount = 0;
            }
        },

        setPenuh(remaining) {
            this.totalPayAmount = remaining;
        },

        selectAndPay(paymentId, remaining, name, invoice) {
            this.selectedPaymentId = paymentId;
            this.activePaymentRemaining = remaining;
            this.activePaymentName = name;
            this.activePaymentInvoice = invoice;
            if (!this.totalPayAmount || this.totalPayAmount <= 0) {
                this.totalPayAmount = remaining;
            } else if (this.totalPayAmount > remaining) {
                this.totalPayAmount = remaining;
            }
            this.proceedToPay();
        },

        copyToClipboard(text) {
            if (!navigator.clipboard) {
                const temp = document.createElement('textarea');
                temp.value = text;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
            } else {
                navigator.clipboard.writeText(text);
            }
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        },

        async ensureSnapLoaded() {
            if (typeof snap !== 'undefined') {
                return true;
            }
            return new Promise((resolve, reject) => {
                const existing = document.querySelector('script[src*="snap.js"]');
                if (existing) {
                    if (typeof snap !== 'undefined') {
                        return resolve(true);
                    }
                    existing.addEventListener('load', () => resolve(true));
                    existing.addEventListener('error', () => reject(new Error('Gagal memuat modul pembayaran Midtrans.')));
                    setTimeout(() => {
                        if (typeof snap !== 'undefined') resolve(true);
                        else reject(new Error('Modul pembayaran Midtrans tidak merespons. Periksa koneksi internet Anda.'));
                    }, 3000);
                    return;
                }

                const script = document.createElement('script');
                script.src = '{{ config("midtrans.base_url.snap_js") }}';
                script.setAttribute('data-client-key', '{{ config("midtrans.client_key") }}');
                script.onload = () => resolve(true);
                script.onerror = () => reject(new Error('Gagal memuat modul pembayaran Midtrans.'));
                document.head.appendChild(script);
            });
        },

        async proceedToPay() {
            if (!this.selectedPaymentId) {
                alert('Silakan pilih tagihan yang ingin dibayar.');
                return;
            }

            if (!this.totalPayAmount || this.totalPayAmount < 10000) {
                alert('Nominal pembayaran minimal adalah Rp 10.000.');
                return;
            }

            if (this.totalPayAmount > this.activePaymentRemaining) {
                this.totalPayAmount = this.activePaymentRemaining;
            }

            this.isLoading = true;
            try {
                // Pastikan snap.js siap di-load
                try {
                    await this.ensureSnapLoaded();
                } catch (snapErr) {
                    console.warn('Peringatan modul Snap:', snapErr.message);
                }

                const response = await fetch(`/mahasiswa/payments/${this.selectedPaymentId}/midtrans/token`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: this.totalPayAmount
                    })
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal mendapatkan kode pembayaran.');
                }

                this.snapToken = data.snap_token;
                this.redirectUrl = data.redirect_url || '';
                if (data.invoice) {
                    this.vaNumber = data.invoice;
                }
                this.isPaying = true;
                this.isLoading = false;

                // Buka modal Snap Midtrans secara otomatis
                if (typeof snap !== 'undefined' && this.snapToken) {
                    snap.pay(this.snapToken, {
                        onSuccess: (result) => {
                            window.location.href = `/mahasiswa/payments/${this.selectedPaymentId}/midtrans/finish`;
                        },
                        onPending: (result) => {
                            window.location.href = `/mahasiswa/payments/${this.selectedPaymentId}/midtrans/finish`;
                        },
                        onError: (result) => {
                            window.location.href = `/mahasiswa/payments/${this.selectedPaymentId}/midtrans/finish`;
                        },
                        onClose: () => {
                            console.info('Snap popup ditutup. Mahasiswa tetap berada di layar petunjuk kode bayar.');
                        }
                    });
                } else if (data.redirect_url) {
                    // Fallback redirect sesuai standar dokumentasi Midtrans jika Snap popup diblokir browser
                    window.location.href = data.redirect_url;
                }
            } catch (error) {
                this.isLoading = false;
                alert('⚠️ ' + error.message);
            }
        },

        async openSnapPopup() {
            if (!this.snapToken && this.selectedPaymentId) {
                this.proceedToPay();
                return;
            }

            try {
                await this.ensureSnapLoaded();
            } catch (snapErr) {
                console.warn(snapErr);
            }

            if (typeof snap !== 'undefined' && this.snapToken) {
                snap.pay(this.snapToken, {
                    onSuccess: (result) => {
                        window.location.href = `/mahasiswa/payments/${this.selectedPaymentId}/midtrans/finish`;
                    },
                    onPending: (result) => {
                        window.location.href = `/mahasiswa/payments/${this.selectedPaymentId}/midtrans/finish`;
                    },
                    onError: (result) => {
                        window.location.href = `/mahasiswa/payments/${this.selectedPaymentId}/midtrans/finish`;
                    },
                    onClose: () => {
                        console.info('Snap popup ditutup.');
                    }
                });
            } else if (this.redirectUrl) {
                window.location.href = this.redirectUrl;
            } else {
                alert('Modul pembayaran Midtrans sedang dimuat. Silakan klik tombol Batalkan dan coba kembali.');
            }
        },

        cancelPayment() {
            this.isPaying = false;
            this.snapToken = '';
        }
    };
}
</script>
@endpush
