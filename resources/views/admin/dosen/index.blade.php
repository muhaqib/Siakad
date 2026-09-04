<x-app-layout>
    <x-slot name="header">
        Data Dosen
    </x-slot>

    @if(session('success'))<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm text-siakad-secondary dark:text-gray-400">Kelola data dosen dalam sistem</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIDN..." class="input-saas px-4 py-2 text-sm w-64 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                <select name="prodi" class="input-saas px-4 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                    <option value="">Semua Prodi</option>
                    @foreach($prodiList as $p)
                    <option value="{{ $p->id }}" {{ request('prodi') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
            </form>
            <button onclick="openModal('createModal')" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                Tambah
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
                        
                        <!-- Sortable: Dosen (Name) -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.dosen.index', array_merge(request()->all(), ['sort' => 'name', 'order' => request('sort') == 'name' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Dosen
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'name' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'name' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'name' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: NIDN -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.dosen.index', array_merge(request()->all(), ['sort' => 'nidn', 'order' => request('sort') == 'nidn' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                NIDN
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'nidn' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'nidn' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'nidn' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Prodi -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.dosen.index', array_merge(request()->all(), ['sort' => 'prodi', 'order' => request('sort') == 'prodi' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Prodi
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'prodi' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'prodi' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'prodi' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Kelas Diampu</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Mhs Bimbingan</th>
                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($dosen as $index => $d)
                    <tr class="border-b border-siakad-light/50 dark:border-gray-700/50">
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $dosen->firstItem() + $index }}</td>
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-siakad-secondary dark:bg-gray-700 flex items-center justify-center text-white text-sm font-semibold">
                                    {{ strtoupper(substr($d->user->name ?? '-', 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-siakad-dark dark:text-white">{{ $d->user->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-5">
                            <span class="text-sm font-mono text-siakad-secondary dark:text-gray-400">{{ $d->nidn }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <span class="text-sm text-siakad-secondary dark:text-gray-400">{{ $d->prodi->nama ?? '-' }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-siakad-primary/10 text-siakad-primary dark:bg-blue-500/10 dark:text-blue-400 rounded-full">{{ $d->kelas_count ?? $d->kelas->count() }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-siakad-secondary/10 text-siakad-secondary dark:bg-gray-700 dark:text-gray-300 rounded-full">{{ $d->mahasiswa_bimbingan_count ?? $d->mahasiswaBimbingan->count() }}</span>
                        </td>
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.dosen.show', $d) }}" class="p-2 text-siakad-secondary hover:text-siakad-primary hover:bg-siakad-primary/10 rounded-lg transition" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <button onclick="openEditModal({{ json_encode(['id'=>$d->id,'name'=>$d->user->name,'email'=>$d->user->email,'nidn'=>$d->nidn,'prodi_id'=>$d->prodi_id]) }})" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <form action="{{ route('admin.dosen.destroy', $d) }}" method="POST" class="inline" onsubmit="return confirm('Yakin?')">@csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-siakad-light/50 dark:bg-gray-700/50 rounded-xl flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <p class="text-siakad-secondary dark:text-gray-400 text-sm">Tidak ada data dosen</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card List -->
    <div class="md:hidden space-y-4">
        @forelse($dosen as $d)
        <div class="card-saas p-4 dark:bg-gray-800">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-siakad-secondary dark:bg-gray-700 flex items-center justify-center text-white text-sm font-semibold">
                        {{ strtoupper(substr($d->user->name ?? '-', 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-siakad-dark dark:text-white">{{ $d->user->name ?? '-' }}</h4>
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 font-mono">{{ $d->nidn }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm text-siakad-secondary dark:text-gray-400 mb-4">
                <div class="col-span-2">
                    <span class="block text-xs text-gray-400">Prodi</span>
                    <span class="font-medium text-siakad-dark dark:text-gray-200">{{ $d->prodi->nama ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400">Kelas Diampu</span>
                    <span class="font-medium text-siakad-dark dark:text-gray-200">{{ $d->kelas_count ?? $d->kelas->count() }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400">Mhs Bimbingan</span>
                    <span class="font-medium text-siakad-dark dark:text-gray-200">{{ $d->mahasiswa_bimbingan_count ?? $d->mahasiswaBimbingan->count() }}</span>
                </div>
            </div>

            <a href="{{ route('admin.dosen.show', $d) }}" class="flex items-center justify-center w-full py-2 bg-siakad-light dark:bg-gray-700 text-siakad-dark dark:text-white font-medium rounded-lg hover:bg-gray-200 transition text-sm">
                Detail Dosen
            </a>
        </div>
        @empty
        <div class="card-saas p-8 text-center">
            <p class="text-siakad-secondary dark:text-gray-400">Tidak ada data dosen</p>
        </div>
        @endforelse
    </div>

    @if($dosen->hasPages())
    <div class="card-saas px-5 py-4 border-t border-siakad-light dark:border-gray-700 dark:bg-gray-800 mt-4 md:mt-0">
        {{ $dosen->links() }}
    </div>
    @endif

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeModal('createModal')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white mb-4">Tambah Dosen</h3>
                <form action="{{ route('admin.dosen.store') }}" method="POST" class="space-y-4">@csrf
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Nama</label><input type="text" name="name" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Email</label><input type="email" name="email" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Password</label><input type="password" name="password" required minlength="8" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">NIDN</label><input type="text" name="nidn" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Prodi</label>
                        <select name="prodi_id" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">@foreach($prodiList as $p)<option value="{{ $p->id }}">{{ $p->nama }}</option>@endforeach</select>
                    </div>
                    <div class="flex justify-end gap-3"><button type="button" onclick="closeModal('createModal')" class="px-4 py-2 text-sm text-siakad-secondary hover:bg-gray-100 rounded-lg">Batal</button><button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeModal('editModal')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white mb-4">Edit Dosen</h3>
                <form id="editForm" method="POST" class="space-y-4">@csrf @method('PUT')
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Nama</label><input type="text" name="name" id="editName" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Email</label><input type="email" name="email" id="editEmail" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Password (kosongkan jika tidak diubah)</label><input type="password" name="password" minlength="8" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">NIDN</label><input type="text" name="nidn" id="editNidn" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Prodi</label>
                        <select name="prodi_id" id="editProdiId" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">@foreach($prodiList as $p)<option value="{{ $p->id }}">{{ $p->nama }}</option>@endforeach</select>
                    </div>
                    <div class="flex justify-end gap-3"><button type="button" onclick="closeModal('editModal')" class="px-4 py-2 text-sm text-siakad-secondary hover:bg-gray-100 rounded-lg">Batal</button><button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function openEditModal(d) {
            document.getElementById('editForm').action = `/admin/dosen/${d.id}`;
            document.getElementById('editName').value = d.name;
            document.getElementById('editEmail').value = d.email;
            document.getElementById('editNidn').value = d.nidn;
            document.getElementById('editProdiId').value = d.prodi_id;
            openModal('editModal');
        }
    </script>
</x-app-layout>

