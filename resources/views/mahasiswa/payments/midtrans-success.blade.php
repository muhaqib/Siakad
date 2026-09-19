<x-app-layout>
    <x-slot name="header">
        {{ $payment->isPartial() ? 'Pembayaran Cicilan Diterima' : 'Pembayaran Berhasil' }}
    </x-slot>

    <div class="max-w-lg mx-auto py-12 px-4 text-center">
        {{-- Icon sukses animasi --}}
        <div class="flex justify-center mb-6">
            <div class="w-24 h-24 rounded-full {{ $payment->isPartial() ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-500' : 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-500' }} flex items-center justify-center shadow-lg">
                <svg class="w-12 h-12 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-siakad-dark dark:text-white mb-2">
            {{ $payment->isPartial() ? 'Pembayaran Cicilan Berhasil!' : 'Pembayaran Berhasil!' }}
        </h1>
        <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-6">
            Pembayaran <strong>{{ $payment->paymentType?->name }}</strong> sebesar
            <strong>Rp {{ number_format((float) $payment->paid_amount, 0, ',', '.') }}</strong>
            telah berhasil dikonfirmasi secara otomatis oleh sistem.
            @if($payment->isPartial())
                <br><span class="text-amber-600 dark:text-amber-400 font-semibold">Sisa tagihan yang belum dibayar: Rp {{ number_format((float) $payment->remaining_amount, 0, ',', '.') }}</span>
            @endif
        </p>

        {{-- Detail transaksi --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 text-left mb-6 space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">No. Invoice</span>
                <span class="font-mono font-semibold text-siakad-dark dark:text-white">{{ $payment->invoice_number }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">Status Pembayaran</span>
                @if($payment->isPaid())
                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">LUNAS</span>
                @else
                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">CICILAN</span>
                @endif
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">Total Biaya Tagihan</span>
                <span class="font-semibold text-siakad-dark dark:text-white">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">Total Telah Dibayar</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format((float) $payment->paid_amount, 0, ',', '.') }}</span>
            </div>
            @if($payment->isPartial())
            <div class="flex justify-between text-sm">
                <span class="text-siakad-secondary dark:text-gray-400">Sisa Tagihan</span>
                <span class="font-bold text-red-600 dark:text-red-400">Rp {{ number_format((float) $payment->remaining_amount, 0, ',', '.') }}</span>
            </div>
            @endif
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
