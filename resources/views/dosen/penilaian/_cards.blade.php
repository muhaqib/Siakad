@forelse($kelasGrouped as $semester => $kelasList)
<div class="mb-3">
    <!-- Semester Header - Default collapsed -->
    <button type="button" onclick="toggleSemester('semester-{{ $semester }}')" id="btn-semester-{{ $semester }}" class="semester-btn w-full flex items-center justify-between px-4 py-2.5 bg-siakad-dark border border-siakad-dark rounded-lg hover:bg-siakad-primary transition-all duration-300 group shadow-sm z-10 relative" data-expanded="true">
        <div class="flex items-center gap-2.5">
            <div id="badge-semester-{{ $semester }}" class="w-7 h-7 rounded-md bg-siakad-secondary flex items-center justify-center transition-all duration-300">
                <span id="badge-text-semester-{{ $semester }}" class="font-semibold text-xs text-white transition-all duration-300">{{ $semester }}</span>
            </div>
            <div class="text-left">
                <h3 id="title-semester-{{ $semester }}" class="text-sm font-semibold text-white transition-all duration-300">Semester {{ $semester }}</h3>
                <p id="subtitle-semester-{{ $semester }}" class="text-[11px] text-gray-300 transition-all duration-300">{{ $kelasList->count() }} Mata Kuliah</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span id="count-semester-{{ $semester }}" class="text-[10px] font-medium text-white bg-siakad-secondary px-2 py-0.5 rounded-full transition-all duration-300">{{ $kelasList->sum(fn($k) => $k->krsDetail->count()) }} Mahasiswa</span>
            <svg id="icon-semester-{{ $semester }}" class="w-4 h-4 text-white transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
    </button>
    
    <!-- Semester Content (Collapsible) - Default expanded -->
    <div id="semester-{{ $semester }}" class="semester-content overflow-hidden transition-all duration-300" style="max-height: 2000px; opacity: 1; margin-top: 0.75rem;">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 pt-3">
            @foreach($kelasList as $kelas)
            <a href="{{ route('dosen.penilaian.show', $kelas->id) }}" class="card-saas group hover:ring-2 hover:ring-siakad-primary transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-siakad-light dark:bg-gray-700 flex items-center justify-center group-hover:bg-siakad-dark group-hover:scale-105 transition-all duration-300">
                            <span class="font-bold text-base text-siakad-dark dark:text-white group-hover:text-white transition duration-300">{{ $kelas->nama_kelas }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-siakad-light dark:bg-gray-700 text-siakad-secondary dark:text-gray-300 group-hover:bg-siakad-primary/10 group-hover:text-siakad-primary transition">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                {{ $kelas->krsDetail->count() }}
                            </span>
                        </div>
                    </div>
                    
                    <h4 class="text-base font-semibold text-siakad-dark dark:text-white mb-1 group-hover:text-siakad-primary transition line-clamp-2">{{ $kelas->mataKuliah->nama_mk ?? 'Nama Mata Kuliah' }}</h4>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-3">{{ $kelas->mataKuliah->kode_mk ?? '-' }} • {{ $kelas->mataKuliah->sks ?? 0 }} SKS</p>
                    
                    <div class="flex items-center justify-between pt-3 border-t border-siakad-light dark:border-gray-700">
                        <div class="flex items-center gap-1.5 text-sm text-siakad-secondary dark:text-gray-400 group-hover:text-siakad-primary transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <span class="font-medium">Input Nilai</span>
                        </div>
                        <svg class="w-5 h-5 text-siakad-secondary dark:text-gray-400 group-hover:text-siakad-primary group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@empty
<div class="card-saas p-12 text-center col-span-2">
    <div class="w-16 h-16 rounded-full bg-siakad-light dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </div>
    <h3 class="text-lg font-bold text-siakad-dark dark:text-white mb-2">Tidak Ada Hasil</h3>
    <p class="text-siakad-secondary dark:text-gray-400 text-sm">Tidak ditemukan kelas yang sesuai dengan pencarian Anda.</p>
</div>
@endforelse
