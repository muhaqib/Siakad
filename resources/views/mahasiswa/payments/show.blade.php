<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">Detail Tagihan</span>
        <span class="hidden md:inline">Detail & Rincian Pembayaran</span>
    </x-slot>

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('mahasiswa.payments.index') }}" class="btn-ghost-saas p-2 rounded-lg flex items-center justify-center" title="Kembali">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-xl font-semibold text-siakad-dark dark:text-white hidden md:block">
                        Detail Tagihan: <span class="font-mono text-siakad-primary dark:text-blue-400">{{ $payment->invoice_number }}</span>
                    </h1>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">
                        {{ $payment->paymentType->name }} &bull; STIT Mambaul Hikmah
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('mahasiswa.payments.index') }}" class="btn-ghost-saas px-3.5 py-2 text-xs font-semibold rounded-lg">
                    Kembali ke Daftar Tagihan
                </a>
            </div>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="card-saas p-6 dark:bg-gray-800 space-y-6">
                <!-- Header Info -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-siakad-light dark:border-gray-700 pb-4 gap-3">
                    <div>
                        <h3 class="text-base font-bold text-siakad-dark dark:text-white">{{ $payment->paymentType->name }}</h3>
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 font-mono mt-0.5">No. Invoice: {{ $payment->invoice_number }}</p>
                    </div>
                    <div>
                        @if($payment->isPaid())
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                ✓ LUNAS
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                                BELUM LUNAS
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Mahasiswa Data Grid -->
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">Nama Mahasiswa</span>
                        <span class="font-bold text-siakad-dark dark:text-white text-sm">{{ $mahasiswa->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">NIM</span>
                        <span class="font-mono font-bold text-siakad-dark dark:text-white text-sm">{{ $mahasiswa->nim }}</span>
                    </div>
                    <div>
                        <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">Program Studi</span>
                        <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $mahasiswa->prodi->nama }}</span>
                    </div>
                    <div>
                        <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">Angkatan</span>
                        <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $mahasiswa->angkatan }}</span>
                    </div>
                </div>

                <!-- Financial Calculation Card -->
                <div class="p-4 rounded-xl bg-siakad-light/20 dark:bg-gray-900/50 border border-siakad-light dark:border-gray-700 space-y-2.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Nominal Tagihan:</span>
                        <span class="font-bold text-sm text-siakad-dark dark:text-white">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Nominal Terbayar:</span>
                        <span class="font-bold text-sm text-emerald-600 dark:text-emerald-400">Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    @if($payment->isPaid())
                        <div class="flex justify-between border-t border-siakad-light dark:border-gray-700 pt-2.5">
                            <span class="text-siakad-secondary dark:text-gray-400">Tanggal Bayar:</span>
                            <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $payment->payment_date ? $payment->payment_date->format('d F Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-siakad-secondary dark:text-gray-400">Metode Pembayaran:</span>
                            <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $payment->payment_method ?? 'Tunai' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-siakad-secondary dark:text-gray-400">Dikonfirmasi Oleh:</span>
                            <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $payment->confirmedBy->name ?? 'Administrasi Keuangan' }}</span>
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('mahasiswa.payments.index') }}" class="btn-ghost-saas px-4 py-2 text-xs font-medium rounded-lg">
                        Kembali
                    </a>
                    @if($payment->isPaid())
                        <a href="{{ route('mahasiswa.payments.receipt', $payment->id) }}" target="_blank" class="btn-primary-saas px-4 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Cetak Kwitansi Resmi
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
