<x-app-layout>
    <x-slot name="header">
        Bimbingan Skripsi
    </x-slot>

    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-siakad-secondary">Daftar mahasiswa bimbingan skripsi Anda</p>
        @if($pendingBimbingan > 0)
        <span class="px-3 py-1 text-sm font-medium bg-siakad-primary/10 text-siakad-primary rounded-full">{{ $pendingBimbingan }} menunggu review</span>
        @endif
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="card-saas p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-siakad-light dark:bg-gray-700 flex items-center justify-center">
                <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $skripsiList->count() }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Total Bimbingan</p>
            </div>
        </div>
        <div class="card-saas p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-siakad-light dark:bg-gray-700 flex items-center justify-center">
                <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $skripsiList->where('status', 'bimbingan')->count() }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Aktif Bimbingan</p>
            </div>
        </div>
        <div class="card-saas p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-siakad-light dark:bg-gray-700 flex items-center justify-center">
                <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $pendingBimbingan }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Menunggu Review</p>
            </div>
        </div>
        <div class="card-saas p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-siakad-light dark:bg-gray-700 flex items-center justify-center">
                <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $skripsiList->where('status', 'selesai')->count() }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Selesai</p>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="mb-4">
        <div class="relative">
            <input type="text" id="searchInput" placeholder="Cari nama mahasiswa, NIM, atau judul..." class="input-saas w-full pl-10 pr-4 py-2.5 text-sm bg-white dark:bg-gray-900 border-siakad-light dark:border-gray-700 text-siakad-dark dark:text-white">
            <svg class="w-5 h-5 text-siakad-secondary dark:text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    <!-- Skripsi List -->
    <div class="card-saas overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full table-saas" id="skripsiTable">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase w-12">#</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase sortable-header cursor-pointer hover:bg-siakad-light/50 transition" data-sort="nama" onclick="sortTable('nama')">
                            <div class="flex items-center gap-1">
                                Mahasiswa
                                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                            </div>
                        </th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase sortable-header cursor-pointer hover:bg-siakad-light/50 transition" data-sort="judul" onclick="sortTable('judul')">
                            <div class="flex items-center gap-1">
                                Judul
                                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                            </div>
                        </th>
                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase sortable-header cursor-pointer hover:bg-siakad-light/50 transition" data-sort="progress" onclick="sortTable('progress')">
                            <div class="flex items-center justify-center gap-1">
                                Progress
                                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                            </div>
                        </th>
                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase sortable-header cursor-pointer hover:bg-siakad-light/50 transition" data-sort="status" onclick="sortTable('status')">
                            <div class="flex items-center justify-center gap-1">
                                Status
                                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                            </div>
                        </th>
                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($skripsiList as $index => $skripsi)
                    <tr class="skripsi-row border-b border-siakad-light/50 dark:border-gray-700 hover:bg-siakad-light/30 dark:hover:bg-gray-800 transition-colors" 
                        data-nama="{{ strtolower($skripsi->mahasiswa->user->name) }}"
                        data-nim="{{ strtolower($skripsi->mahasiswa->nim) }}"
                        data-judul="{{ strtolower($skripsi->judul) }}"
                        data-progress="{{ $skripsi->progress_percent }}"
                        data-status="{{ $skripsi->status }}">
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $index + 1 }}</td>
                        <td class="py-4 px-5">
                            <p class="font-medium text-siakad-dark dark:text-white">{{ $skripsi->mahasiswa->user->name }}</p>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $skripsi->mahasiswa->nim }}</p>
                        </td>
                        <td class="py-4 px-5">
                            <p class="text-sm text-siakad-dark dark:text-gray-300" title="{{ $skripsi->judul }}">{{ Str::limit($skripsi->judul, 60) }}</p>
                        </td>
                        <td class="py-4 px-5">
                            <div class="w-24 mx-auto">
                                <div class="h-2 bg-siakad-light dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-siakad-primary rounded-full" style="width: {{ $skripsi->progress_percent }}%"></div>
                                </div>
                                <p class="text-xs text-siakad-secondary dark:text-gray-400 text-center mt-1">{{ $skripsi->progress_percent }}%</p>
                            </div>
                        </td>
                        <td class="py-4 px-5 text-center">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $skripsi->status_color }}-100 text-{{ $skripsi->status_color }}-700 dark:bg-gray-800 dark:text-{{ $skripsi->status_color }}-400 border dark:border-{{ $skripsi->status_color }}-400/20">{{ $skripsi->status_label }}</span>
                        </td>
                        <td class="py-4 px-5 text-right">
                            <a href="{{ route('dosen.skripsi.show', $skripsi) }}" class="inline-flex items-center gap-1 text-sm text-siakad-primary hover:underline">
                                Detail
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-siakad-secondary">Belum ada mahasiswa bimbingan skripsi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        let currentSort = '';
        let sortDir = 'asc';

        function sortTable(column) {
            const tbody = document.getElementById('tableBody');
            const rows = Array.from(tbody.querySelectorAll('.skripsi-row'));
            
            if (currentSort === column) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort = column;
                sortDir = 'asc';
            }

            rows.sort((a, b) => {
                let aVal = a.dataset[column];
                let bVal = b.dataset[column];
                
                if (column === 'progress') {
                    aVal = parseInt(aVal) || 0;
                    bVal = parseInt(bVal) || 0;
                    return sortDir === 'asc' ? aVal - bVal : bVal - aVal;
                }
                
                if (sortDir === 'asc') {
                    return aVal.localeCompare(bVal);
                } else {
                    return bVal.localeCompare(aVal);
                }
            });

            rows.forEach((row, index) => {
                row.querySelector('td:first-child').textContent = index + 1;
                tbody.appendChild(row);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const rows = document.querySelectorAll('.skripsi-row');

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();

                rows.forEach(row => {
                    const nama = row.dataset.nama || '';
                    const nim = row.dataset.nim || '';
                    const judul = row.dataset.judul || '';
                    
                    if (nama.includes(query) || nim.includes(query) || judul.includes(query)) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            });
        });
    </script>
</x-app-layout>
