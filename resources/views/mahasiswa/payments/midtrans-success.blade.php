<x-app-layout>
    <x-slot name="header">
        Pembayaran Berhasil
    </x-slot>

    <div class="max-w-lg mx-auto py-12 px-4 text-center">
        {{-- Icon sukses animasi --}}
        <div class="flex justify-center mb-6">
            <div class="w-24 h-24 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center shadow-lg">
                <svg class="w-12 h-12 text-emerald-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-siakad-dark dark:text-white mb-2">Pembayaran Berhasil!</h1>
        <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-6">
            Pembayaran <strong>{{ $payment->paymentType?->name }}</strong> sebesar
            <strong>Rp {{ number_format((float) $payment->paid_amount, 0, ',', '.') }}</strong>
            telah dikonfirmasi secara otomatis oleh sistem.
        </p>

        {{-- Detail transaksi --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 text-left mb-6 space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">No. Invoice</span>
                <span class="font-mono font-semibold text-siakad-dark dark:text-white">{{ $payment->invoice_number }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">Metode Pembayaran</span>
                <span class="font-medium text-siakad-dark dark:text-white">{{ $payment->payment_method ?? 'Midtrans' }}</span>
            </div>
            @if($payment->midtrans_order_id)
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">Order ID</span>
                <span class="font-mono text-xs text-siakad-dark dark:text-white">{{ $payment->midtrans_order_id }}</span>
            </div>
            @endif
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">Tanggal</span>
                <span class="font-medium text-siakad-dark dark:text-white">
                    {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : now()->format('d M Y') }}
                </span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('mahasiswa.payments.index') }}"
               class="btn-ghost-saas flex-1 py-2.5 rounded-xl text-sm font-semibold text-center">
                ← Kembali ke Pembayaran
            </a>
            <a href="{{ route('mahasiswa.payments.receipt', $payment->id) }}"
               class="btn-primary-saas flex-1 py-2.5 rounded-xl text-sm font-semibold text-center">
                🖨️ Cetak Kwitansi
            </a>
        </div>
    </div>
</x-app-layout>
