<x-app-layout>
    <x-slot name="header">
        E-Learning
    </x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-bold text-siakad-dark dark:text-white">Kelas Saya</h2>
        <p class="text-sm text-siakad-secondary dark:text-gray-400">Akses materi dan tugas dari kelas yang Anda ambil</p>
    </div>

    @if($tahunAktif !== null)
    <!-- Semester Filter -->
    <div class="mb-6 flex flex-wrap items-center gap-2">
        <a href="{{ route('mahasiswa.lms.index', ['semester' => 'aktif']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $semesterFilter === 'aktif' ? 'bg-siakad-primary text-white' : 'bg-gray-100 dark:bg-gray-700 text-siakad-dark dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Semester {{ $currentSemesterNumber ?? '' }} (Aktif)
            </span>
        </a>
        
        @if($availableSemesters->where('id', '!=', $tahunAktif->id)->count() > 0)
        <div class="relative">
            <select id="semesterDropdown" onchange="if(this.value) window.location.href=this.value" 
                    class="appearance-none pl-4 pr-10 py-2 rounded-lg text-sm font-medium transition cursor-pointer {{ $semesterFilter !== 'aktif' ? 'bg-siakad-primary text-white' : 'bg-gray-100 dark:bg-gray-700 text-siakad-dark dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                <option value="">📁 Semester Lalu</option>
                @foreach($availableSemesters->where('id', '!=', $tahunAktif->id) as $semester)
                <option value="{{ route('mahasiswa.lms.index', ['semester' => $semester->id]) }}" {{ $semesterFilter == $semester->id ? 'selected' : '' }}>
                    {{ $semester->semester_label }} ({{ $semester->display_name }})
                </option>
                @endforeach
            </select>
            <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none {{ $semesterFilter !== 'aktif' ? 'text-white' : 'text-siakad-dark dark:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
        @endif
    </div>
    @else
    <!-- Libur Semester - Show dropdown for all semesters -->
    <div class="mb-6">
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg mb-4">
            <div class="flex items-center gap-2 text-blue-700 dark:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium">Saat ini sedang libur semester</span>
            </div>
            <p class="text-sm text-blue-600 dark:text-blue-300 mt-1">Semua kelas bersifat read-only.</p>
        </div>
        
        @if($availableSemesters->count() > 0)
        <div class="flex items-center gap-3">
            <label class="text-sm text-siakad-secondary dark:text-gray-400">Pilih Semester:</label>
            <div class="relative">
                <select id="semesterDropdown" onchange="if(this.value) window.location.href=this.value" 
                        class="appearance-none pl-4 pr-10 py-2 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-siakad-dark dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer">
                    <option value="{{ route('mahasiswa.lms.index') }}">Semua Semester</option>
                    @foreach($availableSemesters as $semester)
                    <option value="{{ route('mahasiswa.lms.index', ['semester' => $semester->id]) }}" {{ $semesterFilter == $semester->id ? 'selected' : '' }}>
                        {{ $semester->semester_label }} ({{ $semester->display_name }})
                    </option>
                    @endforeach
                </select>
                <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-siakad-dark dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Search Filter -->
    <div class="mb-6">
        <div class="relative">
            <input type="text" id="searchKelas" placeholder="Cari mata kuliah atau dosen..." class="input-saas w-full md:w-80 pl-10 pr-4 py-2.5 text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white" onkeyup="filterKelas()">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    @if($kelasList->isEmpty())
    <div class="card-saas p-8 text-center dark:bg-gray-800">
        <svg class="w-16 h-16 mx-auto mb-4 text-siakad-secondary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
        @if($semesterFilter !== 'aktif')
        <p class="text-siakad-secondary dark:text-gray-400">Tidak ada kelas pada semester ini.</p>
        <a href="{{ route('mahasiswa.lms.index', ['semester' => 'aktif']) }}" class="inline-block mt-3 text-siakad-primary hover:underline text-sm">← Kembali ke Semester Aktif</a>
        @else
        <p class="text-siakad-secondary dark:text-gray-400">Anda belum terdaftar di kelas apapun.</p>
        <a href="{{ route('mahasiswa.krs.index') }}" class="inline-block mt-3 text-siakad-primary hover:underline text-sm">Isi KRS →</a>
        @endif
    </div>
    @else
    
    @if($semesterFilter === 'semua')
    <!-- Grouped by Semester (only for 'semua' mode during libur semester) -->
    @foreach($kelasGrouped as $semesterName => $kelasInSemester)
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
            {{ $semesterName }}
        </h3>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 kelas-container">
            @foreach($kelasInSemester as $kelas)
            @include('mahasiswa.lms._kelas-card', ['kelas' => $kelas])
            @endforeach
        </div>
    </div>
    @endforeach
    @else
    <!-- Flat grid for active semester or specific semester selection -->
    <div id="kelasGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($kelasList as $kelas)
        @include('mahasiswa.lms._kelas-card', ['kelas' => $kelas])
        @endforeach
    </div>
    @endif
    
    <!-- No Results Message -->
    <div id="noResults" class="hidden card-saas p-8 text-center dark:bg-gray-800">
        <svg class="w-12 h-12 mx-auto mb-3 text-siakad-secondary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        <p class="text-siakad-secondary dark:text-gray-400">Tidak ada kelas yang ditemukan.</p>
    </div>
    @endif

    <script>
        function filterKelas() {
            const query = document.getElementById('searchKelas').value.toLowerCase();
            const cards = document.querySelectorAll('.kelas-card');
            const noResults = document.getElementById('noResults');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const searchText = card.dataset.search;
                if (searchText.includes(query)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            if (noResults) {
                noResults.classList.toggle('hidden', visibleCount > 0);
            }
        }
    </script>
</x-app-layout>

