<x-app-layout>
    <x-slot name="header">Bimbingan KP</x-slot>

    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-siakad-secondary">Mahasiswa Kerja Praktek bimbingan Anda</p>
        @if($pendingLogbook > 0)
        <span class="px-3 py-1 text-sm font-medium bg-siakad-primary/10 text-siakad-primary rounded-full">{{ $pendingLogbook }} logbook pending</span>
        @endif
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card-saas p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-siakad-light dark:bg-gray-700 flex items-center justify-center">
                <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $kpList->count() }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Total</p>
            </div>
        </div>
        <div class="card-saas p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-siakad-light dark:bg-gray-700 flex items-center justify-center">
                <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $kpList->where('status', 'berlangsung')->count() }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Berlangsung</p>
            </div>
        </div>
        <div class="card-saas p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-siakad-light dark:bg-gray-700 flex items-center justify-center">
                <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $kpList->where('status', 'selesai')->count() }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Selesai</p>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="mb-4">
        <div class="relative">
            <input type="text" id="searchInput" placeholder="Cari nama mahasiswa, NIM, atau perusahaan..." class="input-saas w-full pl-10 pr-4 py-2.5 text-sm bg-white dark:bg-gray-900 border-siakad-light dark:border-gray-700 text-siakad-dark dark:text-white">
            <svg class="w-5 h-5 text-siakad-secondary dark:text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    <!-- KP List -->
    <div class="card-saas overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full table-saas" id="kpTable">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase w-12">#</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase sortable-header cursor-pointer hover:bg-siakad-light/50 transition" data-sort="nama" onclick="sortTable('nama')">
                            <div class="flex items-center gap-1">
                                Mahasiswa
                                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                            </div>
                        </th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase sortable-header cursor-pointer hover:bg-siakad-light/50 transition" data-sort="perusahaan" onclick="sortTable('perusahaan')">
                            <div class="flex items-center gap-1">
                                Perusahaan
                                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                            </div>
                        </th>
                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Periode</th>
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
                    @forelse($kpList as $index => $kp)
                    <tr class="kp-row border-b border-siakad-light/50 dark:border-gray-700 hover:bg-siakad-light/30 dark:hover:bg-gray-800 transition-colors" 
                        data-nama="{{ strtolower($kp->mahasiswa->user->name) }}"
                        data-nim="{{ strtolower($kp->mahasiswa->nim) }}"
                        data-perusahaan="{{ strtolower($kp->nama_perusahaan) }}"
                        data-status="{{ $kp->status }}">
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $index + 1 }}</td>
                        <td class="py-4 px-5">
                            <p class="font-medium text-siakad-dark dark:text-white">{{ $kp->mahasiswa->user->name }}</p>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $kp->mahasiswa->nim }}</p>
                        </td>
                        <td class="py-4 px-5 text-sm text-siakad-dark dark:text-gray-300">{{ $kp->nama_perusahaan }}</td>
                        <td class="py-4 px-5 text-center text-xs text-siakad-secondary dark:text-gray-400">{{ $kp->tanggal_mulai->format('d/m') }} - {{ $kp->tanggal_selesai->format('d/m/Y') }}</td>
                        <td class="py-4 px-5 text-center">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $kp->status_color }}-100 text-{{ $kp->status_color }}-700 dark:bg-gray-800 dark:text-{{ $kp->status_color }}-400 border dark:border-{{ $kp->status_color }}-400/20">{{ $kp->status_label }}</span>
                        </td>
                        <td class="py-4 px-5 text-right">
                            <a href="{{ route('dosen.kp.show', $kp) }}" class="inline-flex items-center gap-1 text-sm text-siakad-primary hover:underline">
                                Detail
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-siakad-secondary">Belum ada mahasiswa KP</td>
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
            const rows = Array.from(tbody.querySelectorAll('.kp-row'));
            
            if (currentSort === column) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort = column;
                sortDir = 'asc';
            }

            rows.sort((a, b) => {
                let aVal = a.dataset[column];
                let bVal = b.dataset[column];
                
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
            const rows = document.querySelectorAll('.kp-row');

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();

                rows.forEach(row => {
                    const nama = row.dataset.nama || '';
                    const nim = row.dataset.nim || '';
                    const perusahaan = row.dataset.perusahaan || '';
                    
                    if (nama.includes(query) || nim.includes(query) || perusahaan.includes(query)) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            });
        });
    </script>
</x-app-layout>
