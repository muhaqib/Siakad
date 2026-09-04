<x-app-layout>
    <x-slot name="header">
        Manajemen Skripsi
    </x-slot>

    <div class="mb-6">
        <p class="text-sm text-siakad-secondary dark:text-gray-400">Kelola semua pengajuan skripsi mahasiswa</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="card-saas p-4 flex items-center gap-3 dark:bg-gray-800">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['total'] }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Total Skripsi</p>
            </div>
        </div>
        <div class="card-saas p-4 flex items-center gap-3 dark:bg-gray-800">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['aktif'] }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Aktif</p>
            </div>
        </div>
        <div class="card-saas p-4 flex items-center gap-3 dark:bg-gray-800">
            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['menunggu_pembimbing'] }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Perlu Pembimbing</p>
            </div>
        </div>
        <div class="card-saas p-4 flex items-center gap-3 dark:bg-gray-800">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['selesai'] }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Selesai</p>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card-saas p-4 mb-6 dark:bg-gray-800">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-siakad-dark dark:text-gray-300 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Nama, NIM, atau judul...">
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-siakad-dark dark:text-gray-300 mb-1">Status</label>
                <select name="status" class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    <option value="">Semua Status</option>
                    @foreach($statusList as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Table Card (Desktop) -->
    <div class="hidden md:block card-saas overflow-hidden dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase w-16">#</th>
                        
                        <!-- Sortable: Mahasiswa -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'mahasiswa_name', 'order' => request('sort') == 'mahasiswa_name' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Mahasiswa
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'mahasiswa_name' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'mahasiswa_name' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'mahasiswa_name' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Judul -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'judul', 'order' => request('sort') == 'judul' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Judul
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'judul' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'judul' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'judul' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Pembimbing -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'pembimbing_name', 'order' => request('sort') == 'pembimbing_name' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Pembimbing
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'pembimbing_name' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'pembimbing_name' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'pembimbing_name' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Status -->
                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'order' => request('sort') == 'status' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition justify-center">
                                Status
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'status' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'status' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'status' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($skripsiList as $index => $skripsi)
                    <tr class="border-b border-siakad-light/50 dark:border-gray-700/50">
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $skripsiList->firstItem() + $index }}</td>
                        <td class="py-4 px-5">
                            <p class="font-medium text-siakad-dark dark:text-white">{{ $skripsi->mahasiswa->user->name }}</p>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $skripsi->mahasiswa->nim }}</p>
                        </td>
                        <td class="py-4 px-5">
                            <p class="text-sm text-siakad-dark dark:text-white" title="{{ $skripsi->judul }}">{{ Str::limit($skripsi->judul, 50) }}</p>
                            @if($skripsi->bidang_kajian)
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $skripsi->bidang_kajian }}</p>
                            @endif
                        </td>
                        <td class="py-4 px-5 text-sm">
                            @if($skripsi->pembimbing1)
                            <p class="text-siakad-dark dark:text-white">{{ $skripsi->pembimbing1->user->name }}</p>
                            @if($skripsi->pembimbing2)
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $skripsi->pembimbing2->user->name }}</p>
                            @endif
                            @else
                            <span class="text-amber-600 dark:text-amber-400 text-xs">Belum ditentukan</span>
                            @endif
                        </td>
                        <td class="py-4 px-5 text-center">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $skripsi->status_color }}-100 text-{{ $skripsi->status_color }}-700 dark:bg-{{ $skripsi->status_color }}-900/50 dark:text-{{ $skripsi->status_color }}-400">{{ $skripsi->status_label }}</span>
                        </td>
                        <td class="py-4 px-5 text-right">
                            <a href="{{ route('admin.skripsi.show', $skripsi) }}" class="inline-flex items-center gap-1 text-sm text-siakad-primary dark:text-blue-400 hover:underline">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr class="dark:bg-gray-800">
                        <td colspan="6" class="py-12 text-center text-siakad-secondary dark:text-gray-400">Tidak ada data skripsi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card List -->
    <div class="md:hidden space-y-4">
        @forelse($skripsiList as $skripsi)
        <div class="card-saas p-4 dark:bg-gray-800">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-siakad-primary dark:bg-blue-600 flex items-center justify-center text-white text-sm font-semibold">
                        {{ strtoupper(substr($skripsi->mahasiswa->user->name ?? '-', 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-siakad-dark dark:text-white">{{ $skripsi->mahasiswa->user->name }}</h4>
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 font-mono">{{ $skripsi->mahasiswa->nim }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-{{ $skripsi->status_color }}-100 text-{{ $skripsi->status_color }}-700 dark:bg-{{ $skripsi->status_color }}-900/50 dark:text-{{ $skripsi->status_color }}-400">{{ $skripsi->status_label }}</span>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-siakad-dark dark:text-white line-clamp-2 mb-1">{{ $skripsi->judul }}</p>
                @if($skripsi->bidang_kajian)
                <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $skripsi->bidang_kajian }}</p>
                @endif
            </div>

            <div class="bg-siakad-light/30 dark:bg-gray-700/30 rounded-lg p-3 mb-4">
                <p class="text-xs text-siakad-secondary dark:text-gray-400 mb-1">Pembimbing</p>
                @if($skripsi->pembimbing1)
                <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $skripsi->pembimbing1->user->name }}</p>
                @if($skripsi->pembimbing2)
                <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $skripsi->pembimbing2->user->name }}</p>
                @endif
                @else
                <span class="text-amber-600 dark:text-amber-400 text-xs">Belum ditentukan</span>
                @endif
            </div>

            <a href="{{ route('admin.skripsi.show', $skripsi) }}" class="flex items-center justify-center w-full py-2 bg-siakad-light dark:bg-gray-700 text-siakad-dark dark:text-white font-medium rounded-lg hover:bg-gray-200 transition text-sm">
                Lihat Detail
            </a>
        </div>
        @empty
        <div class="card-saas p-8 text-center">
            <p class="text-siakad-secondary dark:text-gray-400">Tidak ada data skripsi</p>
        </div>
        @endforelse
    </div>

    @if($skripsiList->hasPages())
    <div class="card-saas px-5 py-4 border-t border-siakad-light dark:border-gray-700 dark:bg-gray-800 mt-4 md:mt-0">
        {{ $skripsiList->links() }}
    </div>
    @endif
</x-app-layout>
