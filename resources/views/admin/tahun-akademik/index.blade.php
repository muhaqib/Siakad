<x-app-layout>
    <x-slot name="header">
        Tahun Akademik
    </x-slot>

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <p class="text-sm text-siakad-secondary dark:text-gray-400">Kelola data tahun akademik</p>
        @if(auth()->user()->isSuperAdmin())
        <button onclick="openModal('createModal')" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Tambah
        </button>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 text-green-700 dark:text-green-400 rounded-lg">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 text-red-700 dark:text-red-400 rounded-lg">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    @endif

    <div class="card-saas overflow-hidden dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Tahun</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Semester</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Periode Kuliah</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Periode KRS</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Status</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($tahunAkademik as $ta)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="py-4 px-4 text-sm font-medium text-siakad-dark dark:text-white">{{ $ta->tahun }}</td>
                            <td class="py-4 px-4 text-sm text-siakad-secondary">{{ ucfirst($ta->semester) }}</td>
                            <td class="py-4 px-4 text-sm text-siakad-secondary">
                                @if($ta->tanggal_mulai && $ta->tanggal_selesai)
                                    {{ $ta->tanggal_mulai->format('d M Y') }} - {{ $ta->tanggal_selesai->format('d M Y') }}
                                @else
                                    <span class="text-xs text-amber-500">Belum diset</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-sm text-siakad-secondary">
                                @if($ta->tanggal_krs_mulai && $ta->tanggal_krs_selesai)
                                    {{ $ta->tanggal_krs_mulai->format('d M Y') }} - {{ $ta->tanggal_krs_selesai->format('d M Y') }}
                                @else
                                    <span class="text-xs text-amber-500">Belum diset</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($ta->is_active)
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 rounded-full">Aktif</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400 rounded-full">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right">
                                @if(auth()->user()->isSuperAdmin())
                                <div class="flex items-center justify-end gap-1">
                                    @if(!$ta->is_active)
                                        <form action="{{ route('admin.tahun-akademik.activate', $ta) }}" method="POST" class="inline">@csrf
                                            <button type="submit" class="p-2 text-green-600 hover:bg-green-100 rounded-lg" title="Aktifkan"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></button>
                                        </form>
                                    @endif
                                    <button onclick="openEditModal({{ json_encode($ta) }})" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                                    @if(!$ta->is_active)
                                        <form action="{{ route('admin.tahun-akademik.destroy', $ta) }}" method="POST" class="inline" onsubmit="return confirm('Yakin?')">@csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                        </form>
                                    @endif
                                </div>
                                @else
                                <span class="text-xs text-siakad-secondary">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-siakad-secondary">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($tahunAkademik->hasPages())<div class="mt-4">{{ $tahunAkademik->links() }}</div>@endif

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-black/50" onclick="closeModal('createModal')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white mb-4">Tambah Tahun Akademik</h3>
                <form action="{{ route('admin.tahun-akademik.store') }}" method="POST" class="space-y-4">@csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Tahun</label><input type="text" name="tahun" placeholder="2024/2025" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                        <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Semester</label><select name="semester" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"><option value="ganjil">Ganjil</option><option value="genap">Genap</option></select></div>
                    </div>
                    <div class="border-t border-siakad-light dark:border-gray-700 pt-4">
                        <p class="text-xs font-medium text-siakad-secondary mb-3 uppercase">Periode Perkuliahan</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Tanggal Mulai</label><input type="date" name="tanggal_mulai" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                            <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Tanggal Selesai</label><input type="date" name="tanggal_selesai" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                        </div>
                    </div>
                    <div class="border-t border-siakad-light dark:border-gray-700 pt-4">
                        <p class="text-xs font-medium text-siakad-secondary mb-3 uppercase">Periode KRS</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-siakad-secondary mb-1">KRS Mulai</label><input type="date" name="tanggal_krs_mulai" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                            <div><label class="block text-sm font-medium text-siakad-secondary mb-1">KRS Selesai</label><input type="date" name="tanggal_krs_selesai" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2"><button type="button" onclick="closeModal('createModal')" class="px-4 py-2 text-sm text-siakad-secondary hover:bg-gray-100 rounded-lg">Batal</button><button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-black/50" onclick="closeModal('editModal')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white mb-4">Edit Tahun Akademik</h3>
                <form id="editForm" method="POST" class="space-y-4">@csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Tahun</label><input type="text" name="tahun" id="editTahun" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                        <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Semester</label><select name="semester" id="editSemester" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"><option value="ganjil">Ganjil</option><option value="genap">Genap</option></select></div>
                    </div>
                    <div class="border-t border-siakad-light dark:border-gray-700 pt-4">
                        <p class="text-xs font-medium text-siakad-secondary mb-3 uppercase">Periode Perkuliahan</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Tanggal Mulai</label><input type="date" name="tanggal_mulai" id="editTanggalMulai" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                            <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Tanggal Selesai</label><input type="date" name="tanggal_selesai" id="editTanggalSelesai" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                        </div>
                    </div>
                    <div class="border-t border-siakad-light dark:border-gray-700 pt-4">
                        <p class="text-xs font-medium text-siakad-secondary mb-3 uppercase">Periode KRS</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-siakad-secondary mb-1">KRS Mulai</label><input type="date" name="tanggal_krs_mulai" id="editKrsMulai" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                            <div><label class="block text-sm font-medium text-siakad-secondary mb-1">KRS Selesai</label><input type="date" name="tanggal_krs_selesai" id="editKrsSelesai" class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2"><button type="button" onclick="closeModal('editModal')" class="px-4 py-2 text-sm text-siakad-secondary hover:bg-gray-100 rounded-lg">Batal</button><button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function openEditModal(ta) {
            document.getElementById('editForm').action = `/admin/tahun-akademik/${ta.id}`;
            document.getElementById('editTahun').value = ta.tahun;
            document.getElementById('editSemester').value = ta.semester;
            document.getElementById('editTanggalMulai').value = ta.tanggal_mulai ? ta.tanggal_mulai.split('T')[0] : '';
            document.getElementById('editTanggalSelesai').value = ta.tanggal_selesai ? ta.tanggal_selesai.split('T')[0] : '';
            document.getElementById('editKrsMulai').value = ta.tanggal_krs_mulai ? ta.tanggal_krs_mulai.split('T')[0] : '';
            document.getElementById('editKrsSelesai').value = ta.tanggal_krs_selesai ? ta.tanggal_krs_selesai.split('T')[0] : '';
            openModal('editModal');
        }
    </script>
</x-app-layout>

