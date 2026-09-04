<x-app-layout>
    <x-slot name="header">
        Data Kelas
    </x-slot>

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm text-siakad-secondary dark:text-gray-400">Kelola data kelas dan jadwal kuliah dalam sistem</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <form method="GET" class="flex-1 md:flex-none">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kelas / MK / Dosen..." class="input-saas px-4 py-2.5 text-sm w-full md:w-64 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
            </form>
            <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah Kelas
            </button>
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
                        
                        <!-- Sortable: Kelas -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.kelas.index', array_merge(request()->all(), ['sort' => 'nama_kelas', 'order' => request('sort') == 'nama_kelas' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Kelas
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'nama_kelas' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'nama_kelas' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'nama_kelas' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Mata Kuliah -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.kelas.index', array_merge(request()->all(), ['sort' => 'mata_kuliah', 'order' => request('sort') == 'mata_kuliah' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Mata Kuliah
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'mata_kuliah' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'mata_kuliah' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'mata_kuliah' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Dosen -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.kelas.index', array_merge(request()->all(), ['sort' => 'dosen', 'order' => request('sort') == 'dosen' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Dosen
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'dosen' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'dosen' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'dosen' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Jadwal</th>
                        
                        <!-- Sortable: Kapasitas -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.kelas.index', array_merge(request()->all(), ['sort' => 'kapasitas', 'order' => request('sort') == 'kapasitas' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Kapasitas
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'kapasitas' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'kapasitas' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'kapasitas' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($kelas as $index => $k)
                    @php
                        $jadwal = $k->jadwal->first();
                    @endphp
                    <tr class="hover:bg-siakad-light/10 dark:hover:bg-gray-700/30 transition">
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $kelas->firstItem() + $index }}</td>
                        <td class="py-4 px-5">
                            <span class="inline-flex px-3 py-1.5 text-sm font-semibold bg-siakad-primary text-white dark:bg-blue-600 rounded-lg">{{ $k->nama_kelas }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <div>
                                <span class="text-sm font-medium text-siakad-dark dark:text-white">{{ $k->mataKuliah->nama_mk ?? '-' }}</span>
                                <span class="block text-xs text-siakad-secondary dark:text-gray-400 font-mono">{{ $k->mataKuliah->kode_mk ?? '' }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-5">
                            <span class="text-sm text-siakad-secondary dark:text-gray-400">{{ $k->dosen->user->name ?? '-' }}</span>
                        </td>
                        <td class="py-4 px-5">
                            @if($jadwal)
                            <div class="text-sm">
                                <span class="font-medium text-siakad-dark dark:text-white">{{ $jadwal->hari }}</span>
                                <span class="block text-xs text-siakad-secondary dark:text-gray-400">{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</span>
                                @if($jadwal->ruangan)
                                <span class="block text-xs text-siakad-primary dark:text-blue-400">{{ $jadwal->ruangan }}</span>
                                @endif
                            </div>
                            @else
                            <span class="text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/50 dark:text-amber-400 px-2 py-1 rounded">Belum diatur</span>
                            @endif
                        </td>
                        <td class="py-4 px-5">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-siakad-secondary/10 text-siakad-secondary dark:bg-gray-700 dark:text-gray-300 rounded-full">{{ $k->kapasitas }} mhs</span>
                        </td>
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="editKelas({{ json_encode([
                                    'id' => $k->id,
                                    'nama_kelas' => $k->nama_kelas,
                                    'mata_kuliah_id' => $k->mata_kuliah_id,
                                    'dosen_id' => $k->dosen_id,
                                    'kapasitas' => $k->kapasitas,
                                    'hari' => $jadwal?->hari,
                                    'jam_mulai' => $jadwal ? \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') : null,
                                    'jam_selesai' => $jadwal ? \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') : null,
                                    'ruangan' => $jadwal?->ruangan,
                                ]) }})" class="p-2 text-siakad-secondary hover:text-siakad-primary hover:bg-siakad-primary/10 rounded-lg transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form action="{{ route('admin.kelas.destroy', $k) }}" method="POST" onsubmit="return confirm('Hapus kelas ini?')">
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
                            <p class="mb-2">Tidak ada data kelas</p>
                            <a href="{{ route('admin.kelas.index') }}" class="text-sm text-siakad-primary hover:underline">Reset Filter</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 bg-white dark:bg-gray-800">
            {{ $kelas->links() }}
        </div>
    </div>

    <!-- Mobile Card List -->
    <div class="md:hidden space-y-4">
        @forelse($kelas as $k)
        @php
            $jadwal = $k->jadwal->first();
        @endphp
        <div class="card-saas p-4 dark:bg-gray-800">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold bg-siakad-primary text-white dark:bg-blue-600 rounded-md mb-2">Kelas {{ $k->nama_kelas }}</span>
                    <h4 class="font-bold text-siakad-dark dark:text-white">{{ $k->mataKuliah->nama_mk ?? '-' }}</h4>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400 font-mono">{{ $k->mataKuliah->kode_mk ?? '' }}</p>
                </div>
            </div>

            <div class="space-y-3 mb-4">
                <div>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400 mb-1">Dosen Pengampu</p>
                    <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $k->dosen->user->name ?? '-' }}</p>
                </div>
                
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 mb-1">Jadwal</p>
                        @if($jadwal)
                            <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $jadwal->hari }}</p>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</p>
                        @else
                            <span class="text-xs text-amber-600 dark:text-amber-400">Belum diatur</span>
                        @endif
                    </div>
                    @if($jadwal && $jadwal->ruangan)
                    <div class="flex-1">
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 mb-1">Ruangan</p>
                        <span class="text-sm font-medium text-siakad-primary dark:text-blue-400">{{ $jadwal->ruangan }}</span>
                    </div>
                    @endif
                </div>

                <div>
                    <div class="flex items-center justify-between text-xs text-siakad-secondary dark:text-gray-400 mb-1">
                        <span>Kapasitas</span>
                        <span>{{ $k->kapasitas }} Mahasiswa</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                        <div class="bg-siakad-primary dark:bg-blue-500 h-1.5 rounded-full" style="width: 100%"></div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-3 border-t border-siakad-light dark:border-gray-700">
                <button onclick="editKelas({{ json_encode([
                    'id' => $k->id,
                    'nama_kelas' => $k->nama_kelas,
                    'mata_kuliah_id' => $k->mata_kuliah_id,
                    'dosen_id' => $k->dosen_id,
                    'kapasitas' => $k->kapasitas,
                    'hari' => $jadwal?->hari,
                    'jam_mulai' => $jadwal ? \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') : null,
                    'jam_selesai' => $jadwal ? \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') : null,
                    'ruangan' => $jadwal?->ruangan,
                ]) }})" class="flex-1 py-2 text-sm font-medium text-siakad-secondary bg-siakad-light/50 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-siakad-light hover:text-siakad-primary dark:hover:bg-gray-600 transition text-center">
                    Edit
                </button>
                <form action="{{ route('admin.kelas.destroy', $k) }}" method="POST" onsubmit="return confirm('Hapus kelas ini?')" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 text-sm font-medium text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400 rounded-lg hover:bg-red-100 hover:text-red-700 dark:hover:bg-red-900/40 transition">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="card-saas p-8 text-center">
            <p class="text-siakad-secondary dark:text-gray-400 mb-2">Tidak ada data kelas</p>
            <a href="{{ route('admin.kelas.index') }}" class="text-sm text-siakad-primary hover:underline">Reset Filter</a>
        </div>
        @endforelse
    </div>

    @if($kelas->hasPages())
    <div class="md:hidden card-saas px-5 py-4 border-t border-siakad-light dark:border-gray-700 dark:bg-gray-800 mt-4 md:mt-0">
        {{ $kelas->links() }}
    </div>
    @endif
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg animate-fade-in max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Tambah Kelas</h3>
            </div>
            <form action="{{ route('admin.kelas.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Nama Kelas</label>
                            <input type="text" name="nama_kelas" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Contoh: A" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Kapasitas</label>
                            <input type="number" name="kapasitas" min="1" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="40" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Mata Kuliah</label>
                        <select name="mata_kuliah_id" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                            <option value="">Pilih Mata Kuliah</option>
                            @foreach($mataKuliah as $mk)
                            <option value="{{ $mk->id }}">{{ $mk->kode_mk }} - {{ $mk->nama_mk }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Dosen Pengampu</label>
                        <select name="dosen_id" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                            <option value="">Pilih Dosen</option>
                            @foreach($dosen as $d)
                            <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Jadwal Section -->
                    <div class="pt-4 border-t border-siakad-light dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-siakad-dark dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Jadwal Kuliah (Opsional)
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Hari</label>
                                <select name="hari" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                    <option value="">Pilih Hari</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Ruangan</label>
                                <input type="text" name="ruangan" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Contoh: LT-101">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Jam Mulai</label>
                                <input type="time" name="jam_mulai" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Jam Selesai</label>
                                <input type="time" name="jam_selesai" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            </div>
                        </div>
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
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Edit Kelas</h3>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Nama Kelas</label>
                            <input type="text" name="nama_kelas" id="editNama" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Kapasitas</label>
                            <input type="number" name="kapasitas" id="editKapasitas" min="1" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Mata Kuliah</label>
                        <select name="mata_kuliah_id" id="editMK" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                            @foreach($mataKuliah as $mk)
                            <option value="{{ $mk->id }}">{{ $mk->kode_mk }} - {{ $mk->nama_mk }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Dosen Pengampu</label>
                        <select name="dosen_id" id="editDosen" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                            @foreach($dosen as $d)
                            <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Jadwal Section -->
                    <div class="pt-4 border-t border-siakad-light dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-siakad-dark dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Jadwal Kuliah
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Hari</label>
                                <select name="hari" id="editHari" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                    <option value="">Pilih Hari</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Ruangan</label>
                                <input type="text" name="ruangan" id="editRuangan" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Contoh: LT-101">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Jam Mulai</label>
                                <input type="time" name="jam_mulai" id="editJamMulai" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Jam Selesai</label>
                                <input type="time" name="jam_selesai" id="editJamSelesai" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            </div>
                        </div>
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
        function editKelas(data) {
            document.getElementById('editForm').action = `/admin/kelas/${data.id}`;
            document.getElementById('editNama').value = data.nama_kelas;
            document.getElementById('editMK').value = data.mata_kuliah_id;
            document.getElementById('editDosen').value = data.dosen_id;
            document.getElementById('editKapasitas').value = data.kapasitas;
            document.getElementById('editHari').value = data.hari || '';
            document.getElementById('editJamMulai').value = data.jam_mulai || '';
            document.getElementById('editJamSelesai').value = data.jam_selesai || '';
            document.getElementById('editRuangan').value = data.ruangan || '';
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
