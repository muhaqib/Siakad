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
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
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
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-siakad-light dark:divide-gray-700 text-siakad-dark dark:text-gray-200">
                        @foreach($paymentTypes as $pt)
                        <tr class="hover:bg-siakad-light/10 dark:hover:bg-gray-700/40 transition">
                            <td class="px-5 py-3.5 font-mono font-semibold text-siakad-primary dark:text-blue-400 text-xs">{{ $pt->code }}</td>
                            <td class="px-5 py-3.5 font-bold text-siakad-dark dark:text-white">{{ $pt->name }}</td>
                            <td class="px-5 py-3.5 capitalize text-siakad-secondary dark:text-gray-400">{{ $pt->category }}</td>
                            <td class="px-5 py-3.5 font-medium">{{ $pt->semester ? "Semester {$pt->semester}" : '-' }}</td>
                            <td class="px-5 py-3.5 font-bold text-siakad-dark dark:text-white">
                                Rp {{ number_format($pt->default_amount, 0, ',', '.') }}
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
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.payment-types.edit', $pt->id) }}" class="btn-ghost-saas px-3 py-1 text-xs font-semibold rounded inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Edit Tarif
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
