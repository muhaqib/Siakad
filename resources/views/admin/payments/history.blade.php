<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">Riwayat Pembayaran</span>
        <span class="hidden md:inline">Riwayat Transaksi & Audit Log Pembayaran</span>
    </x-slot>

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn-ghost-saas p-2 rounded-lg flex items-center justify-center" title="Kembali">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-xl font-semibold text-siakad-dark dark:text-white hidden md:block">
                        Riwayat Pembayaran: <span class="font-mono text-siakad-primary dark:text-blue-400">{{ $payment->invoice_number }}</span>
                    </h1>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">
                        Mahasiswa: <strong class="text-siakad-dark dark:text-gray-200">{{ $payment->mahasiswa->user->name }}</strong> ({{ $payment->mahasiswa->nim }}) &bull; {{ $payment->paymentType->name }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn-ghost-saas px-3.5 py-2 text-xs font-semibold rounded-lg">
                    Kembali ke Detail Tagihan
                </a>
            </div>
        </div>

        <!-- History Card & Table -->
        <div class="card-saas overflow-hidden dark:bg-gray-800">
            <div class="p-4 border-b border-siakad-light dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-siakad-dark dark:text-white">Kronologi & Log Perubahan Status</h3>
                <span class="text-xs text-siakad-secondary dark:text-gray-400">{{ $payment->histories->count() }} Peristiwa Tercatat</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-saas text-left text-xs">
                    <thead class="bg-siakad-light/30 dark:bg-gray-900/60 text-siakad-secondary dark:text-gray-400 font-semibold border-b border-siakad-light dark:border-gray-700">
                        <tr>
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3">Aksi</th>
                            <th class="px-5 py-3">Status Lama</th>
                            <th class="px-5 py-3">Status Baru</th>
                            <th class="px-5 py-3">Nominal</th>
                            <th class="px-5 py-3">Eksekutor</th>
                            <th class="px-5 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-siakad-light dark:divide-gray-700 text-siakad-dark dark:text-gray-200">
                        @forelse($payment->histories as $h)
                        <tr class="hover:bg-siakad-light/10 dark:hover:bg-gray-700/40 transition">
                            <td class="px-5 py-3.5 whitespace-nowrap text-siakad-secondary dark:text-gray-400 font-mono text-[11px]">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3.5 font-semibold capitalize">{{ $h->action }}</td>
                            <td class="px-5 py-3.5 text-siakad-secondary dark:text-gray-400">{{ $h->old_status ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                @if($h->new_status === 'paid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200 uppercase">
                                        {{ $h->new_status }}
                                    </span>
                                @elseif($h->new_status === 'cancelled')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200 uppercase">
                                        {{ $h->new_status }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200 uppercase">
                                        {{ $h->new_status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-semibold">{{ $h->amount ? 'Rp ' . number_format($h->amount, 0, ',', '.') : '-' }}</td>
                            <td class="px-5 py-3.5">{{ $h->performer->name ?? 'Sistem Otomatis' }}</td>
                            <td class="px-5 py-3.5 text-siakad-secondary dark:text-gray-400 max-w-xs truncate">{{ $h->notes ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-siakad-secondary dark:text-gray-400">Belum ada riwayat transaksi tercatat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
