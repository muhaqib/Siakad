{{-- Kelas Card Partial - reusable card component for kelas list --}}
<div class="kelas-card card-saas dark:bg-gray-800 overflow-hidden hover:shadow-lg transition-shadow" data-search="{{ strtolower($kelas->mataKuliah->nama_mk . ' ' . $kelas->mataKuliah->kode_mk . ' ' . ($kelas->dosen->user->name ?? '')) }}">
    <div class="p-5">
        <div class="flex items-start gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-siakad-primary to-siakad-dark rounded-xl flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                {{ $kelas->nama_kelas }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="font-semibold text-siakad-dark dark:text-white truncate">{{ $kelas->mataKuliah->nama_mk }}</h3>
                    @if($kelas->is_archived ?? false)
                    <span class="flex-shrink-0 px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-full">
                        Arsip
                    </span>
                    @endif
                </div>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $kelas->mataKuliah->kode_mk }} • {{ $kelas->mataKuliah->sks }} SKS</p>
            </div>
        </div>
        
        <p class="text-xs text-siakad-secondary dark:text-gray-400 mb-4">
            Dosen: {{ $kelas->dosen->user->name ?? '-' }}
        </p>

        @if(($kelas->pending_tugas ?? 0) > 0 && !($kelas->is_archived ?? false))
        <div class="mb-4 px-3 py-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
            <p class="text-xs text-amber-700 dark:text-amber-400 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                {{ $kelas->pending_tugas }} tugas belum dikumpulkan
            </p>
        </div>
        @endif

        <div class="flex gap-2">
            <a href="{{ route('mahasiswa.materi.index', $kelas->id) }}" class="flex-1 text-center px-3 py-2 text-sm font-medium bg-siakad-light dark:bg-gray-700 text-siakad-dark dark:text-white rounded-lg hover:bg-siakad-light/80 dark:hover:bg-gray-600 transition">
                Materi
            </a>
            <a href="{{ route('mahasiswa.tugas.index', $kelas->id) }}" class="flex-1 text-center px-3 py-2 text-sm font-medium {{ $kelas->is_archived ?? false ? 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' : 'bg-siakad-primary text-white hover:bg-siakad-primary/90' }} rounded-lg transition">
                Tugas
            </a>
        </div>
    </div>
</div>
