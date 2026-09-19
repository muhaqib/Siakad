<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">Pembayaran</span>
        <span class="hidden md:inline">Pembayaran / Transfer</span>
    </x-slot>

    @php
        $unpaidPayments = $payments->filter(fn($p) => !$p->isPaid())->values();
        $firstUnpaid = $unpaidPayments->first();
        $pendingPayment = $payments->firstWhere('status', 'pending');
        $activePayment = $pendingPayment ?? $firstUnpaid;
        $initialIsPaying = $pendingPayment ? 'true' : 'false';

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
                            PEMBAYARAN ONLINE
                        </h3>
                    </div>

                    {{-- Info Tagihan Yang Sedang Aktif Dipilih --}}
                    <template x-if="selectedBills.length > 0">
                        <div class="mb-4 p-3 rounded-xl bg-blue-50/60 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50 text-xs">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 block mb-1">
                                Tagihan Dipilih (<span x-text="selectedBills.length"></span>):
                            </span>
                            <div class="space-y-1.5 divide-y divide-blue-100/70 dark:divide-blue-900/40">
                                <template x-for="bill in selectedBills" :key="bill.id">
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

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                TOTAL PEMBAYARAN
                            </label>
                        </div>

                        <!-- Box Display Total Pembayaran -->
                        <div class="p-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50/70 dark:bg-gray-900/60 flex items-center justify-between">
                            <span class="text-base font-semibold text-gray-500 dark:text-gray-400 font-sans">
                                Rp.
                            </span>
                            <span class="text-2xl font-black text-siakad-dark dark:text-white font-mono tracking-tight">
                                <span x-text="formatRupiah(grandTotal)"></span>
                            </span>
                        </div>

                        <!-- Tuliskan kecil dibawah Total pembayaran: Biaya Admin Rp 4.000 -->
                        <div class="flex items-center justify-between px-1 mt-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                            <span>Termasuk biaya admin</span>
                            <span class="font-semibold font-mono">Rp 4.000</span>
                        </div>

                    </div>

                    <!-- Tombol Eksekusi Bayar Transfer melalui Midtrans -->
                    <button
                        type="button"
                        @click="proceedToPay()"
                        :disabled="isLoading || grandTotal <= 0"
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
                        <span x-text="isLoading ? 'Menyiapkan ...' : 'Bayar Sekarang'"></span>
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
                        Lihat Status Pembayaran
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
                                    Rp <span x-text="formatRupiah(grandTotal)"></span>
                                </h3>
                                <span class="text-[10px] text-gray-400 block mt-0.5">
                                    Termasuk biaya admin Rp 4.000
                                </span>
                            </div>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-2.5 pt-2 border-t border-gray-200/70 dark:border-gray-700 space-y-1">
                            <template x-for="bill in selectedBills" :key="bill.id">
                                <div class="flex items-center justify-between">
                                    <span x-text="bill.name"></span>
                                    <span class="font-mono" x-text="'Rp ' + formatRupiah(bill.amount)"></span>
                                </div>
                            </template>
                            <div class="flex items-center justify-between text-gray-400 pt-1 border-t border-dashed border-gray-200 dark:border-gray-700">
                                <span>Biaya Admin</span>
                                <span class="font-mono">Rp 4.000</span>
                            </div>
                        </div>
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
                            Tagihan dibayar berurutan. Kolom nominal tagihan berikutnya terbuka setelah tagihan sebelumnya <strong>LUNAS</strong> — atau saat Anda mengisinya nominal penuh di halaman ini.
                        </p>
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
                                    </div>

                                    <!-- Bagian Input Nominal & Tombol PENUH -->
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
                                                    x-model.number="bills[{{ $unpaidIndex }}].amount"
                                                    @input="onAmountInput({{ $unpaidIndex }})"
                                                    :disabled="isPaying"
                                                    min="0"
                                                    max="{{ (int) $p->remaining_amount }}"
                                                    placeholder="0"
                                                    class="w-28 sm:w-32 px-2.5 py-1.5 text-xs font-bold text-siakad-dark dark:text-white text-right border-0 focus:ring-0 focus:outline-none bg-transparent"
                                                />
                                                <button
                                                    type="button"
                                                    @click="setBillFull({{ $unpaidIndex }})"
                                                    :disabled="isPaying"
                                                    class="px-2.5 py-1.5 text-[10px] font-bold uppercase bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 border-l border-gray-200 dark:border-gray-700 transition cursor-pointer"
                                                >
                                                    PENUH
                                                </button>
                                            </div>

                                            <template x-if="bills[{{ $unpaidIndex }}]?.amount === bills[{{ $unpaidIndex }}]?.remaining">
                                                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1.5 font-medium">
                                                    ✓ Nominal penuh. Tagihan berikutnya terbuka jika ingin dibayar bersamaan.
                                                </p>
                                            </template>
                                            <template x-if="bills[{{ $unpaidIndex }}]?.amount > 0 && bills[{{ $unpaidIndex }}]?.amount < bills[{{ $unpaidIndex }}]?.remaining">
                                                <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1.5 font-medium">
                                                    Sisa tagihan nanti: Rp <span x-text="formatRupiah(bills[{{ $unpaidIndex }}].remaining - bills[{{ $unpaidIndex }}].amount)"></span> (Tagihan berikutnya tetap terkunci).
                                                </p>
                                            </template>
                                            <template x-if="bills[{{ $unpaidIndex }}]?.amount === 0 && {{ $unpaidIndex }} > 0">
                                                <p class="text-[11px] text-blue-600 dark:text-blue-400 mt-1.5 font-medium">
                                                    Tagihan ini terbuka. Isi nominal jika ingin membayar tagihan ini sekarang.
                                                </p>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Baris Tagihan: BELUM GILIRANNYA (Terkunci) -->
                                <div
                                    x-show="!isUnlocked({{ $unpaidIndex }})"
                                    class="p-3.5 rounded-xl border border-gray-200/70 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/30 flex items-center justify-between opacity-70 transition"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                                                {{ $p->paymentType->name }}
                                            </h4>
                                            <span class="text-[10px] text-gray-400 uppercase">
                                                TERKUNCI &bull; Selesaikan tagihan sebelumnya
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
                            <span class="font-semibold uppercase tracking-wider">SISA TOTAL TAGIHAN</span>
                            <span class="font-bold text-sm text-siakad-dark dark:text-white font-mono">
                                Rp {{ number_format($totalTunggakan, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <div>
                                <span class="text-xs font-bold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider block">
                                    TOTAL PEMBAYARAN
                                </span>
                                <span class="text-[10px] text-gray-400 block">
                                    Termasuk biaya admin Rp 4.000
                                </span>
                            </div>
                            <span class="text-2xl font-black text-siakad-dark dark:text-white font-mono">
                                Rp <span x-text="formatRupiah(grandTotal)"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

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
        bills: @json($billsData),
        adminFee: 4000,
        vaNumber: '{{ $activePayment && $activePayment->midtrans_order_id ? $activePayment->midtrans_order_id : $mahasiswa->nim }}',
        snapToken: '{{ $activePayment ? $activePayment->midtrans_token : "" }}',
        redirectUrl: '',

        init() {
            if (this.isPaying && this.snapToken) {
                console.info('Pembayaran aktif ditemukan.');
            }
        },

        formatRupiah(num) {
            return new Intl.NumberFormat('id-ID').format(Math.max(0, num || 0));
        },

        isUnlocked(index) {
            if (index === 0) return true;
            for (let i = 0; i < index; i++) {
                if (!this.bills[i] || this.bills[i].amount < this.bills[i].remaining) {
                    return false;
                }
            }
            return true;
        },

        onAmountInput(index) {
            const bill = this.bills[index];
            if (!bill) return;

            if (bill.amount > bill.remaining) {
                bill.amount = bill.remaining;
            }
            if (bill.amount < 0 || isNaN(bill.amount)) {
                bill.amount = 0;
            }

            // Jika tagihan ini belum nominal penuh, reset dan kunci tagihan berikutnya
            if (bill.amount < bill.remaining) {
                for (let i = index + 1; i < this.bills.length; i++) {
                    this.bills[i].amount = 0;
                }
            }
        },

        setBillFull(index) {
            const bill = this.bills[index];
            if (!bill) return;
            bill.amount = bill.remaining;
        },

        get selectedBills() {
            return this.bills.filter(b => b.amount > 0);
        },

        get subtotal() {
            return this.selectedBills.reduce((sum, b) => sum + (Number(b.amount) || 0), 0);
        },

        get grandTotal() {
            return this.subtotal > 0 ? this.subtotal + this.adminFee : 0;
        },

        isAllFull() {
            const selected = this.selectedBills;
            if (!selected.length) return false;
            return selected.every(b => b.amount >= b.remaining);
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
            const selected = this.selectedBills;
            if (!selected.length) {
                alert('Silakan isi nominal tagihan yang ingin dibayar.');
                return;
            }

            for (const b of selected) {
                if (b.amount < 10000) {
                    alert(`Nominal pembayaran untuk "${b.name}" minimal adalah Rp 10.000.`);
                    return;
                }
            }

            this.isLoading = true;
            try {
                try {
                    await this.ensureSnapLoaded();
                } catch (snapErr) {
                    console.warn('Peringatan modul Snap:', snapErr.message);
                }

                const primaryPaymentId = selected[0].id;
                const response = await fetch(`/mahasiswa/payments/${primaryPaymentId}/midtrans/token`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: this.subtotal,
                        bills: selected.map(b => ({
                            id: b.id,
                            amount: b.amount
                        })),
                        admin_fee: this.adminFee
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
                            window.location.href = `/mahasiswa/payments/${primaryPaymentId}/midtrans/finish`;
                        },
                        onPending: (result) => {
                            window.location.href = `/mahasiswa/payments/${primaryPaymentId}/midtrans/finish`;
                        },
                        onError: (result) => {
                            window.location.href = `/mahasiswa/payments/${primaryPaymentId}/midtrans/finish`;
                        },
                        onClose: () => {
                            console.info('Snap popup ditutup. Mahasiswa tetap berada di layar petunjuk kode bayar.');
                        }
                    });
                } else if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
            } catch (error) {
                this.isLoading = false;
                alert('⚠️ ' + error.message);
            }
        },

        async openSnapPopup() {
            const selected = this.selectedBills;
            const primaryPaymentId = selected.length > 0 ? selected[0].id : {{ $activePayment ? $activePayment->id : 'null' }};

            if (!this.snapToken && primaryPaymentId) {
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
                        window.location.href = `/mahasiswa/payments/${primaryPaymentId}/midtrans/finish`;
                    },
                    onPending: (result) => {
                        window.location.href = `/mahasiswa/payments/${primaryPaymentId}/midtrans/finish`;
                    },
                    onError: (result) => {
                        window.location.href = `/mahasiswa/payments/${primaryPaymentId}/midtrans/finish`;
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
</x-app-layout>
