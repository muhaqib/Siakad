<x-app-layout>
    <x-slot name="header">
        Materi Kuliah - {{ $kelas->mataKuliah->nama_mk }}
    </x-slot>

    <!-- Class Info Card -->
    <div class="card-saas p-4 mb-6 dark:bg-gray-800">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-siakad-primary to-siakad-dark rounded-xl flex items-center justify-center text-white font-bold text-lg">
                {{ $kelas->nama_kelas }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-siakad-dark dark:text-white">{{ $kelas->mataKuliah->nama_mk }}</h2>
                <p class="text-sm text-siakad-secondary dark:text-gray-400">{{ $kelas->mataKuliah->kode_mk }} • {{ $kelas->mataKuliah->sks }} SKS</p>
            </div>
        </div>
    </div>

    <!-- Pertemuan List with Materi -->
    <div class="space-y-4">
        @forelse($pertemuanList as $pertemuan)
        <div class="card-saas dark:bg-gray-800 overflow-hidden">
            <!-- Pertemuan Header -->
            <div class="px-5 py-4 border-b border-siakad-light dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-siakad-primary/10 dark:bg-blue-500/20 rounded-full flex items-center justify-center">
                        <span class="text-sm font-bold text-siakad-primary dark:text-blue-400">{{ $pertemuan->pertemuan_ke }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-siakad-dark dark:text-white">Pertemuan {{ $pertemuan->pertemuan_ke }}</h3>
                        <p class="text-xs text-siakad-secondary dark:text-gray-400">
                            {{ $pertemuan->tanggal?->format('d M Y') ?? 'Belum dijadwalkan' }}
                            @if($pertemuan->materi)
                            <span class="mx-1">•</span>
                            {{ $pertemuan->materi }}
                            @endif
                        </p>
                    </div>
                </div>
                <button onclick="toggleUploadModal({{ $pertemuan->id }})" class="btn-primary-saas px-3 py-1.5 rounded-lg text-xs font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Materi
                </button>
            </div>

            <!-- Materi List -->
            <div class="p-4">
                @if($pertemuan->materiList->count() > 0)
                <div class="space-y-2">
                    @foreach($pertemuan->materiList as $materi)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg group hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <div class="flex items-center gap-3">
                            @if($materi->isFile())
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            @else
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            </div>
                            @endif
                            <div>
                                <h4 class="font-medium text-siakad-dark dark:text-white text-sm">{{ $materi->judul }}</h4>
                                @if($materi->deskripsi)
                                <p class="text-xs text-siakad-secondary dark:text-gray-400 line-clamp-1">{{ $materi->deskripsi }}</p>
                                @endif
                                @if($materi->isFile())
                                <p class="text-xs text-siakad-secondary dark:text-gray-500">{{ $materi->file_name }} • {{ $materi->formatted_file_size }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
                            @if($materi->isFile())
                            <a href="{{ route('dosen.materi.download', [$kelas->id, $materi->id]) }}" class="p-2 text-siakad-secondary hover:text-siakad-primary hover:bg-siakad-primary/10 rounded-lg transition" title="Download">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                            @elseif($materi->isExternalLink())
                            <a href="{{ $materi->link_external }}" target="_blank" class="p-2 text-siakad-secondary hover:text-siakad-primary hover:bg-siakad-primary/10 rounded-lg transition" title="Buka Link">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                            @endif
                            <form action="{{ route('dosen.materi.destroy', [$kelas->id, $materi->id]) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-siakad-secondary hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-siakad-secondary dark:text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="text-sm">Belum ada materi untuk pertemuan ini</p>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="card-saas p-8 text-center dark:bg-gray-800">
            <p class="text-siakad-secondary dark:text-gray-400">Belum ada pertemuan terjadwal.</p>
        </div>
        @endforelse
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md animate-fade-in">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Tambah Materi</h3>
            </div>
            <form action="{{ route('dosen.materi.store', $kelas->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pertemuan_id" id="uploadPertemuanId">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Judul Materi</label>
                        <input type="text" name="judul" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Contoh: Slide Minggu 1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" rows="2" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Deskripsi singkat..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Upload File</label>
                        <input type="file" name="file" class="input-saas w-full px-4 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-siakad-primary/10 file:text-siakad-primary">
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">Maksimal 20MB. PDF, DOC, PPT, dll.</p>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-siakad-light dark:border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-gray-800 text-siakad-secondary">atau</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Link External</label>
                        <input type="url" name="link_external" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="https://youtube.com/... atau https://drive.google.com/...">
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeUploadModal()" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleUploadModal(pertemuanId) {
            document.getElementById('uploadPertemuanId').value = pertemuanId;
            document.getElementById('uploadModal').classList.remove('hidden');
        }
        
        function closeUploadModal() {
            document.getElementById('uploadModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
