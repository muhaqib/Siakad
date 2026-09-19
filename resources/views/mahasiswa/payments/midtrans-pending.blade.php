<x-app-layout>
    <x-slot name="header">
        Menunggu Konfirmasi Pembayaran
    </x-slot>

    <div class="max-w-lg mx-auto py-12 px-4 text-center">
        {{-- Icon pending animasi --}}
        <div class="flex justify-center mb-6">
            <div class="w-24 h-24 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center shadow-lg">
                <svg class="w-12 h-12 text-amber-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-siakad-dark dark:text-white mb-2">Menunggu Konfirmasi</h1>
        <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-3">
            Pembayaran <strong>{{ $payment->paymentType?->name }}</strong> sedang dalam proses verifikasi.
            Status akan diperbarui secara otomatis setelah bank mengkonfirmasi transaksi.
        </p>
        <p class="text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 rounded-xl px-4 py-3 mb-6">
            ⏳ Jika Anda membayar via transfer bank / Virtual Account, proses konfirmasi biasanya membutuhkan waktu
            <strong>beberapa menit hingga 1 jam</strong>. Halaman status akan diperbarui otomatis.
        </p>

        {{-- Detail transaksi --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 text-left mb-6 space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">No. Invoice</span>
                <span class="font-mono font-semibold text-siakad-dark dark:text-white">{{ $payment->invoice_number }}</span>
            </div>
            @if($payment->midtrans_order_id)
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">Order ID</span>
                <span class="font-mono text-xs text-siakad-dark dark:text-white">{{ $payment->midtrans_order_id }}</span>
            </div>
            @endif
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">Status</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Menunggu Konfirmasi Bank
                </span>
            </div>
        </div>

        <a href="{{ route('mahasiswa.payments.index') }}"
           class="btn-primary-saas w-full py-2.5 rounded-xl text-sm font-semibold text-center block">
            Lihat Status Pembayaran
        </a>
    </div>
</x-app-layout>
