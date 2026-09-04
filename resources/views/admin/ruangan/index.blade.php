<x-app-layout>
    <x-slot name="header">
        Master Data Ruangan
    </x-slot>

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm text-siakad-secondary dark:text-gray-400">Kelola data ruangan kelas dalam sistem</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <form method="GET" class="flex-1 md:flex-none">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Ruangan / Gedung..." class="input-saas px-4 py-2.5 text-sm w-full md:w-64 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
            </form>
            <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah Ruangan
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['total'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Total Ruangan</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['active'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Ruangan Aktif</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['capacity'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Total Kapasitas</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['gedung_count'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Gedung</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <!-- Table Card (Desktop) -->
    <div class="hidden md:block card-saas overflow-hidden dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-16">#</th>
                        
                        <!-- Sortable: Kode -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.ruangan.index', array_merge(request()->all(), ['sort' => 'kode_ruangan', 'order' => request('sort') == 'kode_ruangan' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Kode
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'kode_ruangan' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'kode_ruangan' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'kode_ruangan' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Nama Ruangan -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.ruangan.index', array_merge(request()->all(), ['sort' => 'nama_ruangan', 'order' => request('sort') == 'nama_ruangan' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Nama Ruangan
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'nama_ruangan' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'nama_ruangan' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'nama_ruangan' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Lokasi (Gedung) -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.ruangan.index', array_merge(request()->all(), ['sort' => 'gedung', 'order' => request('sort') == 'gedung' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Lokasi
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'gedung' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'gedung' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'gedung' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Kapasitas -->
                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.ruangan.index', array_merge(request()->all(), ['sort' => 'kapasitas', 'order' => request('sort') == 'kapasitas' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition justify-center">
                                Kapasitas
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'kapasitas' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'kapasitas' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'kapasitas' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($ruanganList as $index => $ruangan)
                    <tr class="hover:bg-siakad-light/10 dark:hover:bg-gray-700/30 transition">
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $ruanganList->firstItem() + $index }}</td>
                        <td class="py-4 px-5">
                            <span class="inline-flex px-3 py-1.5 text-sm font-semibold bg-siakad-primary text-white dark:bg-blue-600 rounded-lg">{{ $ruangan->kode_ruangan }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <p class="font-medium text-siakad-dark dark:text-white">{{ $ruangan->nama_ruangan }}</p>
                            @if($ruangan->fasilitas)
                            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">{{ Str::limit($ruangan->fasilitas, 50) }}</p>
                            @endif
                        </td>
                        <td class="py-4 px-5">
                            @if($ruangan->gedung)
                            <span class="text-sm text-siakad-dark dark:text-white">{{ $ruangan->gedung }}</span>
                            @if($ruangan->lantai)
                            <span class="text-xs text-siakad-secondary dark:text-gray-400"> • Lt. {{ $ruangan->lantai }}</span>
                            @endif
                            @else
                            <span class="text-sm text-siakad-secondary dark:text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-5 text-center">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-siakad-secondary/10 text-siakad-secondary dark:bg-gray-700 dark:text-gray-300 rounded-full">{{ $ruangan->kapasitas }} orang</span>
                        </td>
                        <td class="py-4 px-5 text-center">
                            @if($ruangan->is_active)
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400 rounded-full">Aktif</span>
                            @else
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400 rounded-full">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="editRuangan({{ json_encode($ruangan) }})" class="p-2 text-siakad-secondary hover:text-siakad-primary hover:bg-siakad-primary/10 rounded-lg transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form action="{{ route('admin.ruangan.destroy', $ruangan) }}" method="POST" onsubmit="return confirm('Hapus ruangan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-siakad-secondary hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-siakad-secondary">
                            <p class="mb-2">Tidak ada data ruangan</p>
                            <a href="{{ route('admin.ruangan.index') }}" class="text-sm text-siakad-primary hover:underline">Reset Filter</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 bg-white dark:bg-gray-800">
            {{ $ruanganList->links() }}
        </div>
    </div>

    <!-- Mobile Card List -->
    <div class="md:hidden space-y-4">
        @forelse($ruanganList as $ruangan)
        <div class="card-saas p-4 dark:bg-gray-800">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold bg-siakad-primary text-white dark:bg-blue-600 rounded-md mb-2">{{ $ruangan->kode_ruangan }}</span>
                    <h4 class="font-bold text-siakad-dark dark:text-white">{{ $ruangan->nama_ruangan }}</h4>
                    @if($ruangan->fasilitas)
                    <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1 line-clamp-1">{{ $ruangan->fasilitas }}</p>
                    @endif
                </div>
                <div>
                    @if($ruangan->is_active)
                    <span class="inline-flex px-2 py-1 text-[10px] font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400 rounded-full">Aktif</span>
                    @else
                    <span class="inline-flex px-2 py-1 text-[10px] font-medium bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400 rounded-full">Nonaktif</span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg">
                    <span class="block text-[10px] text-siakad-secondary dark:text-gray-400 uppercase tracking-wider mb-1">Lokasi</span>
                    <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $ruangan->gedung ?? '-' }}</p>
                    @if($ruangan->lantai)
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">Lantai {{ $ruangan->lantai }}</p>
                    @endif
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg">
                    <span class="block text-[10px] text-siakad-secondary dark:text-gray-400 uppercase tracking-wider mb-1">Kapasitas</span>
                    <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $ruangan->kapasitas }} Orang</p>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-3 border-t border-siakad-light dark:border-gray-700">
                <button onclick="editRuangan({{ json_encode($ruangan) }})" class="flex-1 py-2 text-sm font-medium text-siakad-secondary bg-siakad-light/50 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-siakad-light hover:text-siakad-primary dark:hover:bg-gray-600 transition text-center">
                    Edit
                </button>
                <form action="{{ route('admin.ruangan.destroy', $ruangan) }}" method="POST" onsubmit="return confirm('Hapus ruangan ini?')" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 text-sm font-medium text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400 rounded-lg hover:bg-red-100 hover:text-red-700 dark:hover:bg-red-900/40 transition">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="card-saas p-8 text-center">
            <p class="text-siakad-secondary dark:text-gray-400 mb-2">Tidak ada data ruangan</p>
            <a href="{{ route('admin.ruangan.index') }}" class="text-sm text-siakad-primary hover:underline">Reset Filter</a>
        </div>
        @endforelse
    </div>

    @if($ruanganList->hasPages())
    <div class="md:hidden card-saas px-5 py-4 border-t border-siakad-light dark:border-gray-700 dark:bg-gray-800 mt-4 md:mt-0">
        {{ $ruanganList->links() }}
    </div>
    @endif
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg animate-fade-in max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Tambah Ruangan</h3>
            </div>
            <form action="{{ route('admin.ruangan.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Kode Ruangan *</label>
                            <input type="text" name="kode_ruangan" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="LT-101" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Kapasitas *</label>
                            <input type="number" name="kapasitas" min="1" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="40" value="40" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Nama Ruangan *</label>
                        <input type="text" name="nama_ruangan" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Lab Komputer 1" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Gedung</label>
                            <input type="text" name="gedung" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Gedung A">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Lantai</label>
                            <input type="number" name="lantai" min="1" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="1">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Fasilitas</label>
                        <textarea name="fasilitas" rows="2" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="AC, Proyektor, Whiteboard"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="createIsActive" checked class="rounded border-siakad-light text-siakad-primary focus:ring-siakad-primary dark:border-gray-700 dark:bg-gray-900">
                        <label for="createIsActive" class="text-sm text-siakad-dark dark:text-gray-300">Ruangan aktif</label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg animate-fade-in max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Edit Ruangan</h3>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Kode Ruangan *</label>
                            <input type="text" name="kode_ruangan" id="editKode" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Kapasitas *</label>
                            <input type="number" name="kapasitas" id="editKapasitas" min="1" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Nama Ruangan *</label>
                        <input type="text" name="nama_ruangan" id="editNama" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Gedung</label>
                            <input type="text" name="gedung" id="editGedung" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Lantai</label>
                            <input type="number" name="lantai" id="editLantai" min="1" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Fasilitas</label>
                        <textarea name="fasilitas" id="editFasilitas" rows="2" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="editIsActive" class="rounded border-siakad-light text-siakad-primary focus:ring-siakad-primary dark:border-gray-700 dark:bg-gray-900">
                        <label for="editIsActive" class="text-sm text-siakad-dark dark:text-gray-300">Ruangan aktif</label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editRuangan(data) {
            document.getElementById('editForm').action = `/admin/ruangan/${data.id}`;
            document.getElementById('editKode').value = data.kode_ruangan;
            document.getElementById('editNama').value = data.nama_ruangan;
            document.getElementById('editKapasitas').value = data.kapasitas;
            document.getElementById('editGedung').value = data.gedung || '';
            document.getElementById('editLantai').value = data.lantai || '';
            document.getElementById('editFasilitas').value = data.fasilitas || '';
            document.getElementById('editIsActive').checked = data.is_active;
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
