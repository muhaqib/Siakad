<x-app-layout>
    <x-slot name="header">
        Pembayaran Gagal / Dibatalkan
    </x-slot>

    <div class="max-w-lg mx-auto py-12 px-4 text-center">
        {{-- Icon gagal --}}
        <div class="flex justify-center mb-6">
            <div class="w-24 h-24 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center shadow-lg">
                <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-siakad-dark dark:text-white mb-2">Pembayaran Gagal</h1>
        <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-6">
            Pembayaran <strong>{{ $payment->paymentType?->name }}</strong> gagal diproses atau dibatalkan.
            Tagihan Anda belum berkurang. Silakan coba lagi.
        </p>

        <div class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800/50 p-4 text-left mb-6 text-xs text-red-700 dark:text-red-300 space-y-1">
            <p class="font-semibold">Kemungkinan penyebab:</p>
            <ul class="list-disc ml-4 space-y-0.5">
                <li>Saldo rekening/e-wallet tidak mencukupi</li>
                <li>Transaksi melebihi batas waktu (kadaluarsa)</li>
                <li>Pembayaran dibatalkan oleh Anda</li>
                <li>Kartu kredit ditolak oleh bank penerbit</li>
            </ul>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('mahasiswa.payments.index') }}"
               class="btn-ghost-saas flex-1 py-2.5 rounded-xl text-sm font-semibold text-center">
                ← Kembali
            </a>
            <a href="{{ route('mahasiswa.payments.show', $payment->id) }}"
               class="btn-primary-saas flex-1 py-2.5 rounded-xl text-sm font-semibold text-center">
                Coba Bayar Lagi
            </a>
        </div>
    </div>
</x-app-layout>
