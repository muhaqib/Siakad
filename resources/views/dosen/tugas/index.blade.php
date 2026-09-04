<x-app-layout>
    <x-slot name="header">
        Tugas - {{ $kelas->mataKuliah->nama_mk }}
    </x-slot>

    <!-- Class Info -->
    <div class="card-saas p-4 mb-6 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-siakad-primary to-siakad-dark rounded-xl flex items-center justify-center text-white font-bold text-lg">
                    {{ $kelas->nama_kelas }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-siakad-dark dark:text-white">{{ $kelas->mataKuliah->nama_mk }}</h2>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">{{ $kelas->mataKuliah->kode_mk }} • {{ $kelas->mataKuliah->sks }} SKS</p>
                </div>
            </div>
            <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Tugas
            </button>
        </div>
    </div>

    <!-- Tugas List -->
    <div class="space-y-4">
        @forelse($kelas->tugas as $tugas)
        <div class="card-saas dark:bg-gray-800 overflow-hidden">
            <div class="p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="font-semibold text-siakad-dark dark:text-white">{{ $tugas->judul }}</h3>
                            @if(!$tugas->is_active)
                            <span class="px-2 py-0.5 text-xs bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">Nonaktif</span>
                            @elseif($tugas->isOverdue())
                            <span class="px-2 py-0.5 text-xs bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded">Deadline Lewat</span>
                            @else
                            <span class="px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded">Aktif</span>
                            @endif
                        </div>
                        @if($tugas->deskripsi)
                        <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-3 line-clamp-2">{{ $tugas->deskripsi }}</p>
                        @endif
                        <div class="flex items-center gap-4 text-xs text-siakad-secondary dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Deadline: {{ $tugas->deadline->format('d M Y, H:i') }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                {{ $tugas->submission_count }} dikumpulkan
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $tugas->graded_count }} dinilai
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('dosen.tugas.show', [$kelas->id, $tugas->id]) }}" class="px-3 py-1.5 text-xs font-medium bg-siakad-primary text-white rounded-lg hover:bg-siakad-primary/90 transition">
                            Lihat Submissions
                        </a>
                        <form action="{{ route('dosen.tugas.toggle', [$kelas->id, $tugas->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-2 text-siakad-secondary hover:text-siakad-primary hover:bg-siakad-primary/10 rounded-lg transition" title="{{ $tugas->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                @if($tugas->is_active)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card-saas p-8 text-center dark:bg-gray-800">
            <svg class="w-12 h-12 mx-auto mb-3 text-siakad-secondary/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <p class="text-siakad-secondary dark:text-gray-400">Belum ada tugas untuk kelas ini.</p>
            <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="mt-3 text-sm text-siakad-primary hover:underline">+ Buat tugas pertama</button>
        </div>
        @endforelse
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg animate-fade-in">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Buat Tugas Baru</h3>
            </div>
            <form action="{{ route('dosen.tugas.store', $kelas->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Judul Tugas</label>
                        <input type="text" name="judul" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Contoh: Tugas 1 - Analisis Data" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Instruksi tugas..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Deadline</label>
                        <input type="datetime-local" name="deadline" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">File Soal (Opsional)</label>
                        <input type="file" name="file_tugas" class="input-saas w-full px-4 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-siakad-primary/10 file:text-siakad-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Ekstensi File yang Diizinkan</label>
                        <input type="text" name="allowed_extensions" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" value="pdf,doc,docx,zip,rar" placeholder="pdf,doc,docx">
                        <p class="text-xs text-siakad-secondary mt-1">Pisahkan dengan koma, tanpa spasi</p>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
