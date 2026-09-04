<x-app-layout>
    <x-slot name="header">
        Data Mata Kuliah
    </x-slot>

    <!-- Toolbar: Filter, Search, Action -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <form action="{{ route('admin.mata-kuliah.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
            @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
            @if(request('order')) <input type="hidden" name="order" value="{{ request('order') }}"> @endif

            <!-- Filter Kategori -->
            <div class="relative min-w-[200px]">
                <select name="category" onchange="this.form.submit()" class="input-saas w-full pl-4 pr-10 py-2.5 appearance-none cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @php
                        $categories = [
                            'TI' => 'Teknik Informatika',
                            'SI' => 'Sistem Informasi',
                            'TE' => 'Teknik Elektro',
                            'MN' => 'Manajemen',
                            'AK' => 'Akuntansi',
                            'MT' => 'Matematika',
                            'MK' => 'Mata Kuliah Umum',
                            'UN' => 'Mata Kuliah Universitas'
                        ];
                    @endphp
                    @foreach($categories as $code => $name)
                    <option value="{{ $code }}" {{ request('category') == $code ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-siakad-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>

            <!-- Search -->
            <div class="relative w-full md:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / Kode..." class="input-saas w-full pl-10 pr-4 py-2.5">
                <svg class="w-5 h-5 text-siakad-secondary absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </form>

        <div class="flex items-center gap-2">
            <!-- Export Button (Read Only) -->
            <a href="{{ route('admin.mata-kuliah.export', request()->all()) }}" target="_blank" class="btn-ghost-saas px-4 py-2.5 dark:text-white rounded-lg text-sm font-medium flex items-center gap-2 border border-siakad-light dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </a>

            <!-- Create Button -->
            <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                <span class="hidden sm:inline">Tambah Data</span>
            </button>
        </div>
    </div>

    <!-- Data Table -->
    <!-- Table Card (Desktop) -->
    <div class="hidden md:block card-saas overflow-hidden dark:bg-gray-800 mb-6">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-16">#</th>
                        
                        <!-- Sortable: Kode -->
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-32">
                            <a href="{{ route('admin.mata-kuliah.index', array_merge(request()->all(), ['sort' => 'kode_mk', 'order' => request('sort') == 'kode_mk' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Kode
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'kode_mk' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'kode_mk' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'kode_mk' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Nama -->
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.mata-kuliah.index', array_merge(request()->all(), ['sort' => 'nama_mk', 'order' => request('sort') == 'nama_mk' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Nama Mata Kuliah
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'nama_mk' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'nama_mk' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'nama_mk' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: SKS -->
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-20 text-center">
                            <a href="{{ route('admin.mata-kuliah.index', array_merge(request()->all(), ['sort' => 'sks', 'order' => request('sort') == 'sks' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group inline-flex items-center gap-1 hover:text-siakad-primary transition justify-center">
                                SKS
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'sks' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'sks' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'sks' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Semester -->
                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-28 text-center">
                            <a href="{{ route('admin.mata-kuliah.index', array_merge(request()->all(), ['sort' => 'semester', 'order' => request('sort') == 'semester' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group inline-flex items-center gap-1 hover:text-siakad-primary transition justify-center">
                                Semester
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'semester' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'semester' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'semester' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($mataKuliah as $idx => $mk)
                    <tr class="hover:bg-siakad-light/10 dark:hover:bg-gray-700/30 transition">
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $mataKuliah->firstItem() + $idx }}</td>
                        <td class="py-4 px-5">
                            <span class="text-sm font-mono text-siakad-primary dark:text-blue-400 font-medium bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">{{ $mk->kode_mk }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <span class="text-sm font-medium text-siakad-dark dark:text-white">{{ $mk->nama_mk }}</span>
                            <!-- Show full category/prodi name based on prefix, implicitly -->
                            @php $prefix = substr($mk->kode_mk, 0, 2); @endphp
                            <div class="text-[10px] text-siakad-secondary mt-0.5">{{ $categories[$prefix] ?? '' }}</div>
                        </td>
                        <td class="py-4 px-5 text-center">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-siakad-primary/10 text-siakad-primary dark:bg-blue-500/10 dark:text-blue-400 rounded-full">{{ $mk->sks }}</span>
                        </td>
                        <td class="py-4 px-5 text-center">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-siakad-secondary/10 text-siakad-secondary dark:bg-gray-700 dark:text-gray-300 rounded-full">Sem {{ $mk->semester }}</span>
                        </td>
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                            <button onclick="editMK({{ $mk->id }}, '{{ $mk->kode_mk }}', '{{ addslashes($mk->nama_mk) }}', {{ $mk->sks }}, {{ $mk->semester }}, {{ $mk->prodi_id ?? 'null' }}, {{ $mk->prodi?->fakultas_id ?? 'null' }})" class="p-2 text-siakad-secondary hover:text-siakad-primary hover:bg-siakad-primary/10 rounded-lg transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form action="{{ route('admin.mata-kuliah.destroy', $mk) }}" method="POST" onsubmit="return confirm('Hapus mata kuliah ini?')">
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
                        <td colspan="6" class="py-12 text-center text-siakad-secondary">
                            <p class="mb-2">Tidak ada data ditemukan</p>
                            <a href="{{ route('admin.mata-kuliah.index') }}" class="text-sm text-siakad-primary hover:underline">Reset Filter</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 bg-white dark:bg-gray-800">
            {{ $mataKuliah->links() }}
        </div>
    </div>

    <!-- Mobile Card List -->
    <div class="md:hidden space-y-4 mb-6">
        @forelse($mataKuliah as $mk)
        <div class="card-saas p-4 dark:bg-gray-800">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold bg-blue-50 text-siakad-primary dark:bg-blue-900/20 dark:text-blue-400 rounded-md font-mono mb-2">{{ $mk->kode_mk }}</span>
                    <h4 class="font-bold text-siakad-dark dark:text-white">{{ $mk->nama_mk }}</h4>
                    @php $prefix = substr($mk->kode_mk, 0, 2); @endphp
                    <p class="text-[10px] text-siakad-secondary mt-0.5">{{ $categories[$prefix] ?? '' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg text-center">
                    <span class="block text-[10px] text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">SKS</span>
                    <span class="font-bold text-siakad-primary dark:text-blue-400">{{ $mk->sks }}</span>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg text-center">
                    <span class="block text-[10px] text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Semester</span>
                    <span class="font-bold text-siakad-dark dark:text-white">{{ $mk->semester }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-3 border-t border-siakad-light dark:border-gray-700">
                <button onclick="editMK({{ $mk->id }}, '{{ $mk->kode_mk }}', '{{ addslashes($mk->nama_mk) }}', {{ $mk->sks }}, {{ $mk->semester }}, {{ $mk->prodi_id ?? 'null' }}, {{ $mk->prodi?->fakultas_id ?? 'null' }})" class="flex-1 py-2 text-sm font-medium text-siakad-secondary bg-siakad-light/50 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-siakad-light hover:text-siakad-primary dark:hover:bg-gray-600 transition text-center">
                    Edit
                </button>
                <form action="{{ route('admin.mata-kuliah.destroy', $mk) }}" method="POST" onsubmit="return confirm('Hapus mata kuliah ini?')" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 text-sm font-medium text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400 rounded-lg hover:bg-red-100 hover:text-red-700 dark:hover:bg-red-900/40 transition">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="card-saas p-8 text-center">
            <p class="text-siakad-secondary dark:text-gray-400 mb-2">Tidak ada data ditemukan</p>
            <a href="{{ route('admin.mata-kuliah.index') }}" class="text-sm text-siakad-primary hover:underline">Reset Filter</a>
        </div>
        @endforelse
    </div>

    @if($mataKuliah->hasPages())
    <div class="md:hidden card-saas px-5 py-4 border-t border-siakad-light dark:border-gray-700 dark:bg-gray-800 mb-6">
        {{ $mataKuliah->links() }}
    </div>
    @endif
    </div>
    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md animate-fade-in">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Tambah Mata Kuliah</h3>
            </div>
            <form action="{{ route('admin.mata-kuliah.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    @if($isSuperAdmin)
                    <!-- Fakultas dropdown for superadmin -->
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Fakultas</label>
                        <select id="createFakultasSelect" onchange="filterProdiCreate()" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="">Pilih Fakultas</option>
                            @foreach($fakultasList as $fakultas)
                            <option value="{{ $fakultas->id }}">{{ $fakultas->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <!-- Prodi dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Program Studi</label>
                        <select name="prodi_id" id="createProdiSelect" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                            <option value="">Pilih Program Studi</option>
                            @foreach($prodiList as $prodi)
                            <option value="{{ $prodi->id }}" data-fakultas="{{ $prodi->fakultas_id }}">{{ $prodi->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Kode MK</label>
                        <input type="text" name="kode_mk" class="input-saas w-full px-4 py-2.5 text-sm font-mono dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Contoh: TI101, SI201, MK001" required>
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">Prefix: TI=Teknik Informatika, SI=Sistem Informasi, MK=Umum, dll</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Nama Mata Kuliah</label>
                        <input type="text" name="nama_mk" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Masukkan nama mata kuliah" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">SKS</label>
                            <input type="number" name="sks" min="1" max="6" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="3" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Semester</label>
                            <input type="number" name="semester" min="1" max="8" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="1" required>
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
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md animate-fade-in">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Edit Mata Kuliah</h3>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="p-6 space-y-4">
                    @if($isSuperAdmin)
                    <!-- Fakultas dropdown for superadmin -->
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Fakultas</label>
                        <select id="editFakultasSelect" onchange="filterProdiEdit()" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="">Pilih Fakultas</option>
                            @foreach($fakultasList as $fakultas)
                            <option value="{{ $fakultas->id }}">{{ $fakultas->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <!-- Prodi dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Program Studi</label>
                        <select name="prodi_id" id="editProdiSelect" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                            <option value="">Pilih Program Studi</option>
                            @foreach($prodiList as $prodi)
                            <option value="{{ $prodi->id }}" data-fakultas="{{ $prodi->fakultas_id }}">{{ $prodi->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Kode MK</label>
                        <input type="text" name="kode_mk" id="editKode" class="input-saas w-full px-4 py-2.5 text-sm font-mono dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Nama Mata Kuliah</label>
                        <input type="text" name="nama_mk" id="editNama" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">SKS</label>
                            <input type="number" name="sks" id="editSks" min="1" max="6" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Semester</label>
                            <input type="number" name="semester" id="editSemester" min="1" max="8" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
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
        // Filter prodi based on fakultas for Create modal
        function filterProdiCreate() {
            const fakultasId = document.getElementById('createFakultasSelect')?.value || '';
            const prodiSelect = document.getElementById('createProdiSelect');
            const options = prodiSelect.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') return; // Keep placeholder
                const optFakultasId = option.getAttribute('data-fakultas');
                option.style.display = (fakultasId === '' || optFakultasId === fakultasId) ? '' : 'none';
            });
            
            // Reset selection
            prodiSelect.value = '';
        }
        
        // Filter prodi based on fakultas for Edit modal
        function filterProdiEdit() {
            const fakultasId = document.getElementById('editFakultasSelect')?.value || '';
            const prodiSelect = document.getElementById('editProdiSelect');
            const options = prodiSelect.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') return;
                const optFakultasId = option.getAttribute('data-fakultas');
                option.style.display = (fakultasId === '' || optFakultasId === fakultasId) ? '' : 'none';
            });
            
            prodiSelect.value = '';
        }
        
        function editMK(id, kode, nama, sks, semester, prodiId, fakultasId) {
            document.getElementById('editForm').action = `/admin/mata-kuliah/${id}`;
            document.getElementById('editKode').value = kode;
            document.getElementById('editNama').value = nama;
            document.getElementById('editSks').value = sks;
            document.getElementById('editSemester').value = semester;
            
            // Set fakultas and prodi
            const fakultasSelect = document.getElementById('editFakultasSelect');
            if (fakultasSelect) {
                fakultasSelect.value = fakultasId || '';
                filterProdiEdit();
            }
            document.getElementById('editProdiSelect').value = prodiId || '';
            
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>

