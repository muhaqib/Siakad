<x-app-layout>
    <x-slot name="header">
        Manajemen Presensi
    </x-slot>

    @if($kelasList->isEmpty())
    <div class="card-saas p-12 text-center">
        <div class="w-20 h-20 rounded-full bg-siakad-light dark:bg-gray-700 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
        </div>
        <h3 class="text-xl font-bold text-siakad-dark dark:text-white mb-2">Belum Ada Kelas</h3>
        <p class="text-siakad-secondary dark:text-gray-400">Anda belum memiliki kelas yang diampu untuk semester ini.</p>
    </div>
    @else
    
    <!-- Search Bar -->
    <!-- Search Bar -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <p class="text-sm text-siakad-secondary">Total <span class="font-semibold text-[#234C6A]">{{ $kelasList->count() }}</span> kelas yang diampu</p>
        <div class="relative w-full sm:w-auto">
            <input type="text" id="searchInput" placeholder="Cari mata kuliah..." class="input-saas pl-9 pr-4 py-2 text-sm w-full sm:w-56">
            <svg class="w-4 h-4 text-siakad-secondary absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    <!-- Semester Sections -->
    <div id="semesterContainer">
    @forelse($kelasGrouped->sortKeys() as $semester => $kelasSemester)
    <div class="mb-4 semester-section" data-semester="{{ $semester }}">
        <!-- Semester Header -->
        <button type="button" onclick="toggleSemester('semester-{{ $semester }}')" id="btn-semester-{{ $semester }}" class="semester-btn w-full flex items-center justify-between px-4 py-2.5 bg-siakad-dark border border-siakad-dark rounded-lg hover:bg-siakad-primary transition-all duration-300 group shadow-sm z-10 relative" data-expanded="true">
            <div class="flex items-center gap-2.5">
                <div id="badge-semester-{{ $semester }}" class="w-7 h-7 rounded-md bg-siakad-secondary flex items-center justify-center transition-all duration-300">
                    <span id="badge-text-semester-{{ $semester }}" class="font-semibold text-xs text-white transition-all duration-300">{{ $semester }}</span>
                </div>
                <div class="text-left">
                    <h3 id="title-semester-{{ $semester }}" class="text-sm font-semibold text-white transition-all duration-300">Semester {{ $semester }}</h3>
                    <p id="subtitle-semester-{{ $semester }}" class="text-[11px] text-gray-300 transition-all duration-300">{{ $kelasSemester->count() }} Mata Kuliah</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span id="count-semester-{{ $semester }}" class="text-[10px] font-medium text-white bg-siakad-secondary px-2 py-0.5 rounded-full transition-all duration-300">{{ $kelasSemester->sum('jumlah_mahasiswa') }} Mahasiswa</span>
                <svg id="icon-semester-{{ $semester }}" class="w-4 h-4 text-white transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </button>
        
        <!-- Semester Content (Table) -->
        <div id="semester-{{ $semester }}" class="semester-content overflow-hidden transition-all duration-300" style="max-height: 2000px; opacity: 1; margin-top: 0.75rem;">
            <div class="card-saas overflow-hidden mt-3">
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full table-saas">
                        <thead>
                            <tr class="bg-siakad-light/30 dark:bg-gray-900">
                                <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase tracking-wider w-12">#</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">Kode MK</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">Mata Kuliah</th>
                                <th class="text-center py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase tracking-wider w-16">Kelas</th>
                                <th class="text-center py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">Mahasiswa</th>
                                <th class="text-center py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">Hari</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase tracking-wider w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelasSemester as $index => $kelas)
                            <tr class="kelas-row border-b border-siakad-light hover:bg-siakad-light/50 transition-colors" 
                                data-nama="{{ strtolower($kelas->mataKuliah->nama_mk ?? '') }}"
                                data-kode="{{ strtolower($kelas->mataKuliah->kode_mk ?? '') }}">
                                <td class="py-4 px-4 text-sm text-siakad-secondary">{{ $index + 1 }}</td>
                                <td class="py-4 px-4">
                                    <span class="text-sm font-medium text-siakad-primary">{{ $kelas->mataKuliah->kode_mk ?? '-' }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="text-sm font-medium text-siakad-dark">{{ $kelas->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-siakad-light text-xs font-bold text-siakad-dark">{{ $kelas->nama_kelas }}</span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="text-sm text-siakad-secondary">{{ $kelas->jumlah_mahasiswa ?? 0 }}</span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($kelas->jadwal && $kelas->jadwal->isNotEmpty())
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">{{ $kelas->jadwal->first()->hari }}</span>
                                    @else
                                    <span class="text-xs text-siakad-secondary">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <a href="{{ route('dosen.presensi.kelas', $kelas) }}" class="inline-flex items-center gap-0.5 text-xs font-semibold text-siakad-primary hover:text-siakad-dark transition">
                                        Kelola
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="md:hidden grid grid-cols-1 gap-4 p-4">
                    @foreach($kelasSemester as $index => $kelas)
                    <div class="kelas-row bg-white dark:bg-gray-800 border border-siakad-light rounded-xl p-4 shadow-sm"
                         data-nama="{{ strtolower($kelas->mataKuliah->nama_mk ?? '') }}"
                         data-kode="{{ strtolower($kelas->mataKuliah->kode_mk ?? '') }}">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <span class="text-xs font-bold text-siakad-primary bg-siakad-primary/10 px-2 py-1 rounded mb-1 inline-block">{{ $kelas->mataKuliah->kode_mk ?? '-' }}</span>
                                <h4 class="font-bold text-siakad-dark text-lg">{{ $kelas->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</h4>
                            </div>
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-siakad-light text-sm font-bold text-siakad-dark">{{ $kelas->nama_kelas }}</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                            <div class="flex items-center gap-2 text-siakad-secondary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <span>{{ $kelas->jumlah_mahasiswa ?? 0 }} Mhs</span>
                            </div>
                            <div class="flex items-center gap-2 text-siakad-secondary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                @if($kelas->jadwal && $kelas->jadwal->isNotEmpty())
                                <span>{{ $kelas->jadwal->first()->hari }}</span>
                                @else
                                <span>-</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('dosen.presensi.kelas', $kelas) }}" class="flex items-center justify-center gap-2 w-full py-3 bg-siakad-primary text-white font-medium rounded-lg hover:bg-siakad-primary/90 transition">
                            Kelola Presensi
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card-saas p-12 text-center">
        <h3 class="text-lg font-bold text-[#1B3C53] mb-2">Tidak Ada Hasil</h3>
        <p class="text-[#456882] text-sm">Tidak ditemukan kelas yang sesuai dengan pencarian.</p>
    </div>
    @endforelse
    </div>

    <script>
        // Toggle semester expand/collapse with color change
        function toggleSemester(id) {
            const content = document.getElementById(id);
            const btn = document.getElementById('btn-' + id);
            const icon = document.getElementById('icon-' + id);
            const badge = document.getElementById('badge-' + id);
            const badgeText = document.getElementById('badge-text-' + id);
            const title = document.getElementById('title-' + id);
            const subtitle = document.getElementById('subtitle-' + id);
            const count = document.getElementById('count-' + id);
            
            const isExpanded = content.style.maxHeight !== '0px';
            
            if (!isExpanded) {
                // Expand - change to active theme (Navy)
                content.style.maxHeight = '2000px';
                content.style.opacity = '1';
                content.style.marginTop = '0.75rem';
                icon.classList.remove('-rotate-90');
                
                // Remove default/dark classes
                btn.classList.remove('bg-white', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700', 'hover:bg-gray-50', 'dark:hover:bg-gray-700');
                // Add active classes (Navy)
                btn.classList.add('bg-siakad-dark', 'border-siakad-dark', 'hover:bg-siakad-primary');
                
                // Badge
                badge.classList.remove('bg-siakad-light', 'dark:bg-gray-700');
                badge.classList.add('bg-siakad-secondary');
                
                // Text Colors
                badgeText.classList.remove('text-siakad-dark', 'dark:text-white');
                badgeText.classList.add('text-white');
                
                title.classList.remove('text-siakad-dark', 'dark:text-white');
                title.classList.add('text-white');
                
                subtitle.classList.remove('text-siakad-secondary', 'dark:text-gray-400');
                subtitle.classList.add('text-gray-300');
                
                count.classList.remove('text-siakad-secondary', 'dark:text-gray-300', 'bg-siakad-light', 'dark:bg-gray-700');
                count.classList.add('text-white', 'bg-siakad-secondary');
                
                icon.classList.remove('text-siakad-secondary', 'dark:text-gray-400');
                icon.classList.add('text-white');
            } else {
                // Collapse - revert to default theme (Light/Dark auto)
                content.style.maxHeight = '0px';
                content.style.opacity = '0';
                content.style.marginTop = '0';
                icon.classList.add('-rotate-90');
                
                // Restore default/dark classes
                btn.classList.add('bg-white', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700', 'hover:bg-gray-50', 'dark:hover:bg-gray-700');
                btn.classList.remove('bg-siakad-dark', 'border-siakad-dark', 'hover:bg-siakad-primary');
                
                // Badge
                badge.classList.add('bg-siakad-light', 'dark:bg-gray-700');
                badge.classList.remove('bg-siakad-secondary');
                
                // Text Colors
                badgeText.classList.add('text-siakad-dark', 'dark:text-white');
                badgeText.classList.remove('text-white');
                
                title.classList.add('text-siakad-dark', 'dark:text-white');
                title.classList.remove('text-white');
                
                subtitle.classList.add('text-siakad-secondary', 'dark:text-gray-400');
                subtitle.classList.remove('text-gray-300');
                
                count.classList.add('text-siakad-secondary', 'dark:text-gray-300', 'bg-siakad-light', 'dark:bg-gray-700');
                count.classList.remove('text-white', 'bg-siakad-secondary');
                
                icon.classList.add('text-siakad-secondary', 'dark:text-gray-400');
                icon.classList.remove('text-white');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                
                document.querySelectorAll('.kelas-row').forEach(row => {
                    const nama = row.dataset.nama || '';
                    const kode = row.dataset.kode || '';
                    
                    if (nama.includes(query) || kode.includes(query)) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            });
        });
    </script>

    <style>
        .semester-content {
            transition: max-height 0.3s ease-in-out, opacity 0.2s ease-in-out, margin-top 0.2s ease-in-out;
        }
    </style>
    @endif
</x-app-layout>
