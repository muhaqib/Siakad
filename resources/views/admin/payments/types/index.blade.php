<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">Tarif Pembayaran</span>
        <span class="hidden md:inline">Pengaturan Jenis & Tarif Pembayaran</span>
    </x-slot>

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-siakad-dark dark:text-white hidden md:block">
                    Pengaturan Jenis & Tarif Pembayaran
                </h1>
                <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-1">
                    Konfigurasi master jenis pembayaran dan tarif perkuliahan mahasiswa STIT Mambaul Hikmah
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.payments.index') }}" class="btn-ghost-saas px-3.5 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Daftar Tagihan
                </a>
                <a href="{{ route('admin.payment-types.create') }}" class="btn-primary-saas px-4 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Biaya
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="card-saas overflow-hidden dark:bg-gray-800">
            <div class="p-4 border-b border-siakad-light dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-siakad-dark dark:text-white">Master Daftar Tarif Pembayaran</h3>
                <span class="text-xs text-siakad-secondary dark:text-gray-400">{{ count($paymentTypes) }} Jenis Tarif</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-saas text-left text-xs">
                    <thead class="bg-siakad-light/30 dark:bg-gray-900/60 text-siakad-secondary dark:text-gray-400 font-semibold border-b border-siakad-light dark:border-gray-700">
                        <tr>
                            <th class="px-5 py-3.5">Kode</th>
                            <th class="px-5 py-3.5">Nama Pembayaran</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5">Semester</th>
                            <th class="px-5 py-3.5">Tarif Default</th>
                            <th class="px-5 py-3.5">Total Tagihan</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-siakad-light dark:divide-gray-700 text-siakad-dark dark:text-gray-200">
                        @forelse($paymentTypes as $pt)
                        <tr class="hover:bg-siakad-light/10 dark:hover:bg-gray-700/40 transition">
                            <td class="px-5 py-3.5 font-mono font-semibold text-siakad-primary dark:text-blue-400 text-xs">{{ $pt->code }}</td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-siakad-dark dark:text-white">{{ $pt->name }}</div>
                                @if($pt->description)
                                    <div class="text-[11px] text-siakad-secondary dark:text-gray-400 line-clamp-1 mt-0.5">{{ $pt->description }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 capitalize text-siakad-secondary dark:text-gray-400">
                                @if($pt->category === 'semester')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">Semester</span>
                                @elseif($pt->category === 'registration')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300">Pendaftaran</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">Lainnya</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-medium">{{ $pt->semester ? "Semester {$pt->semester}" : '-' }}</td>
                            <td class="px-5 py-3.5 font-bold text-siakad-dark dark:text-white">
                                Rp {{ number_format($pt->default_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($pt->payments_count > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        {{ $pt->payments_count }} Mahasiswa
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 text-[11px]">0 Tagihan</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($pt->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('admin.payment-types.edit', $pt->id) }}" class="btn-ghost-saas px-3 py-1 text-xs font-semibold rounded inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Edit
                                </a>
                                @if($pt->payments_count == 0)
                                    <form action="{{ route('admin.payment-types.destroy', $pt->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komponen biaya &quot;{{ $pt->name }}&quot;? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost-saas px-2.5 py-1 text-xs font-semibold rounded inline-flex items-center gap-1 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-siakad-secondary dark:text-gray-400">
                                Belum ada komponen tarif pembayaran yang terdaftar. Silakan klik tombol "Tambah Biaya" di atas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
