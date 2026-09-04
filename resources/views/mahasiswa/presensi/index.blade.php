<x-app-layout>
    <x-slot name="header">
        Rekap Presensi
    </x-slot>

    @php
        // Prepare data grouped by semester for Alpine.js
        $semesterData = [];
        foreach ($rekapBySemester as $semester => $items) {
            $coursesInSemester = $items->map(function($item) {
                $kelas = $item['kelas'];
                $rekap = $item['rekap'];
                $jadwal = $kelas->jadwal->first();
                
                $percentage = $rekap['persentase'];
                $color = $percentage >= 80 ? '#10B981' : ($percentage >= 75 ? '#F59E0B' : '#EF4444');
                $bgBar = $percentage >= 80 ? 'bg-emerald-500' : ($percentage >= 75 ? 'bg-amber-500' : 'bg-red-500');
                
                $today = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd');
                $isToday = $jadwal && $jadwal->hari == $today;

                return [
                    'id' => $kelas->id,
                    'name' => $kelas->mataKuliah->nama_mk,
                    'code' => $kelas->mataKuliah->kode_mk,
                    'lecturer' => $kelas->dosen->user->name ?? 'Dosen Belum Diplot',
                    'sks' => $kelas->mataKuliah->sks,
                    'percentage' => $percentage,
                    'h' => $rekap['hadir'],
                    's' => $rekap['sakit'],
                    'i' => $rekap['izin'],
                    'a' => $rekap['alpa'],
                    'total_meetings' => $rekap['total_pertemuan'],
                    'done_meetings' => $rekap['hadir'] + $rekap['sakit'] + $rekap['izin'] + $rekap['alpa'],
                    'time' => $jadwal ? substr($jadwal->jam_mulai, 0, 5) . ' - ' . substr($jadwal->jam_selesai, 0, 5) : 'Jadwal belum ada',
                    'room' => $jadwal->ruangan ?? '-',
                    'day' => $jadwal->hari ?? '-',
                    'is_today' => $isToday,
                    'url' => route('mahasiswa.presensi.show', $kelas->id),
                    'color' => $color,
                    'bgBar' => $bgBar,
                ];
            })->values();
            
            $semesterData[$semester] = $coursesInSemester;
        }
        
        // Also prepare flat list for "today" filter
        $coursesData = collect($semesterData)->flatten(1)->values();
    @endphp

    <div x-data="{
        search: '',
        filter: 'all',
        viewMode: 'card',
        sortOrder: 'asc',
        currentPage: 1,
        perPage: 9,
        courses: {{ $coursesData->toJson() }},
        
        get sortedCourses() {
            let sorted = [...this.courses];
            sorted.sort((a, b) => {
                if (this.sortOrder === 'asc') {
                    return a.name.localeCompare(b.name);
                } else {
                    return b.name.localeCompare(a.name);
                }
            });
            return sorted;
        },
        
        get filteredCourses() {
            return this.sortedCourses.filter(course => {
                const matchesSearch = course.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                     course.code.toLowerCase().includes(this.search.toLowerCase()) ||
                                     course.lecturer.toLowerCase().includes(this.search.toLowerCase());
                
                let matchesFilter = true;
                if (this.filter === 'today') {
                    matchesFilter = course.is_today;
                }
                
                return matchesSearch && matchesFilter;
            });
        },
        
        get paginatedCourses() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredCourses.slice(start, end);
        },
        
        get totalPages() {
            return Math.ceil(this.filteredCourses.length / this.perPage);
        },
        
        get todayCourses() {
            return this.courses.filter(c => c.is_today);
        },
        
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        
        resetPage() {
            this.currentPage = 1;
        }
    }" class="pb-20" x-init="$watch('search', () => resetPage()); $watch('filter', () => resetPage()); $watch('sortOrder', () => resetPage())">

        <!-- Search & Filter Section (Full Width) -->
        <div class="py-4 mb-8 transition-all duration-200">
            <div class="card-saas p-2 flex flex-col md:flex-row gap-2">
                <!-- Search (Full Width) -->
                <div class="relative flex-1">
                    <svg class="absolute left-4 top-3.5 w-5 h-5 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input 
                        x-model="search"
                        type="text" 
                        placeholder="Cari mata kuliah, kode, atau dosen..." 
                        class="w-full pl-12 pr-4 py-3 bg-transparent border-none text-siakad-dark placeholder-siakad-secondary focus:ring-0 focus:outline-none text-sm font-medium"
                    >
                </div>
                
                <!-- Filters, Sort & View Toggle -->
                <div class="flex flex-wrap md:flex-nowrap items-center gap-2 p-1 bg-siakad-light dark:bg-slate-700 rounded-xl flex-shrink-0 w-full md:w-auto">
                    <!-- Filter Buttons -->
                    <div class="flex w-full md:w-auto gap-1">
                        <button @click="filter = 'all'" 
                            :class="filter === 'all' ? 'bg-[#234C6A] text-white shadow-md' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 md:flex-none text-center px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 whitespace-nowrap">
                            Semua
                        </button>
                        <button @click="filter = 'today'" 
                            :class="filter === 'today' ? 'bg-[#234C6A] text-white shadow-md' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 md:flex-none text-center px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 whitespace-nowrap">
                            Hari Ini
                        </button>
                    </div>
                    
                    <div class="hidden md:block w-px h-6 bg-slate-300 mx-1"></div>
                    
                    <!-- Controls (Sort & View) -->
                    <div class="flex flex-1 md:flex-none gap-2 items-center justify-between md:justify-start w-full md:w-auto pt-2 md:pt-0 border-t border-slate-200 dark:border-slate-600 md:border-0 hidden md:flex">
                        <!-- Sort Toggle -->
                        <button @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'" 
                            class="flex-1 md:flex-none p-2 rounded-lg transition-colors text-slate-500 hover:text-[#234C6A] hover:bg-white flex justify-center hidden md:block" 
                            :title="sortOrder === 'asc' ? 'A-Z' : 'Z-A'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="sortOrder === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                                <path x-show="sortOrder === 'desc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"></path>
                            </svg>
                        </button>
                        
                        <div class="w-px h-6 bg-slate-300 mx-1"></div>
                        
                        <div class="flex flex-1 md:flex-none gap-1">
                            <button @click="viewMode = 'card'" class="flex-1 md:flex-none p-2 rounded-lg transition-colors flex justify-center" :class="viewMode === 'card' ? 'bg-white shadow text-[#234C6A]' : 'text-slate-400 hover:text-slate-600'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </button>
                            <button @click="viewMode = 'list'" class="flex-1 md:flex-none p-2 rounded-lg transition-colors flex justify-center" :class="viewMode === 'list' ? 'bg-white shadow text-[#234C6A]' : 'text-slate-400 hover:text-slate-600'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access (Today's Classes) - No Gradient -->
        <div x-show="filter === 'all' && todayCourses.length > 0" class="mb-10" style="display: none;">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-6 bg-[#234C6A] rounded-full"></div>
                <h2 class="text-lg font-bold text-siakad-dark">Mata Kuliah Hari Ini</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="course in todayCourses" :key="'today-'+course.id">
                    <div class="bg-[#1B3C53] rounded-2xl p-5 text-white shadow-lg relative overflow-hidden group">
                        <div class="relative z-10 flex flex-col h-full justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-white/20 text-white/90" x-text="course.code"></span>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-400/20 text-emerald-100 text-[10px] font-bold border border-emerald-400/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Aktif
                                    </span>
                                </div>
                                <h3 class="font-bold text-base leading-tight mb-1" x-text="course.name"></h3>
                                <p class="text-white/60 text-xs flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span x-text="course.time"></span> • <span x-text="course.room"></span>
                                </p>
                            </div>
                            
                            <a :href="course.url" class="mt-4 w-full py-2 bg-white text-[#1B3C53] text-center text-xs font-bold rounded-lg hover:bg-slate-100 transition shadow-sm">
                                Buka Presensi
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Main Content -->
        <div>
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-6 bg-siakad-secondary rounded-full"></div>
                <h2 class="text-lg font-bold text-siakad-dark">Semua Mata Kuliah</h2>
                <span class="text-xs font-semibold px-2 py-1 bg-siakad-light dark:bg-slate-700 text-siakad-secondary rounded-full" x-text="filteredCourses.length"></span>
            </div>

            <!-- Empty State -->
            <div x-show="filteredCourses.length === 0" class="text-center py-20 card-saas" style="display: none;">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-700">Tidak ada mata kuliah ditemukan</h3>
                <p class="text-slate-400 mt-1 max-w-xs mx-auto">Coba ubah kata kunci pencarian atau filter yang Anda gunakan.</p>
                <button @click="search = ''; filter = 'all'" class="mt-6 text-[#234C6A] font-semibold text-sm hover:underline">
                    Reset Filter
                </button>
            </div>

            <!-- Semester Grouped View -->
            <div x-show="viewMode === 'card'" class="space-y-6">
                @foreach($semesterData as $semester => $courses)
                <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="card-saas overflow-hidden">
                    <button @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between hover:bg-siakad-light/30 dark:hover:bg-slate-700/30 transition cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-siakad-primary flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="text-left">
                                <h3 class="font-bold text-siakad-dark">{{ $semester }}</h3>
                                <p class="text-xs text-siakad-secondary">{{ count($courses) }} mata kuliah</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-siakad-secondary transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="border-t border-siakad-light dark:border-slate-700">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 p-4">
                            @foreach($courses as $course)
                            <div class="bg-siakad-light/30 dark:bg-slate-700/30 rounded-xl p-5 hover:shadow-md transition-all duration-300 group relative">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <span class="inline-block px-2.5 py-1 rounded-md bg-white dark:bg-slate-800 text-siakad-secondary text-[10px] font-bold tracking-wide uppercase">{{ $course['code'] }}</span>
                                        <h3 class="font-bold text-siakad-dark text-sm mt-2 group-hover:text-[#234C6A] transition-colors line-clamp-1">{{ $course['name'] }}</h3>
                                        <p class="text-siakad-secondary text-xs mt-0.5 line-clamp-1">{{ $course['lecturer'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xl font-extrabold" style="color: {{ $course['color'] }}">{{ $course['percentage'] }}%</span>
                                        <p class="text-[10px] font-medium text-slate-400 uppercase tracking-wide">Kehadiran</p>
                                    </div>
                                </div>

                                <div class="w-full h-1.5 bg-white dark:bg-slate-800 rounded-full mb-4 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-1000 {{ $course['bgBar'] }}" style="width: {{ $course['percentage'] }}%"></div>
                                </div>

                                <div class="flex items-center justify-between pt-3 border-t border-white/50 dark:border-slate-600">
                                    <div class="text-xs text-slate-400 font-medium">
                                        {{ $course['done_meetings'] }} dari {{ $course['total_meetings'] }} pertemuan
                                    </div>
                                    <a href="{{ $course['url'] }}" class="flex items-center gap-1 text-xs font-bold text-[#234C6A] dark:text-white hover:text-[#1B3C53] dark:hover:text-slate-200 transition-colors group/link">
                                        Detail
                                        <svg class="w-3.5 h-3.5 transform group-hover/link:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- LIST View -->
            <div x-show="viewMode === 'list'" class="card-saas overflow-hidden" style="display: none;">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#234C6A] text-white text-xs uppercase tracking-wider">
                                <th class="p-4 font-semibold">Mata Kuliah</th>
                                <th class="p-4 font-semibold w-32">Dosen</th>
                                <th class="p-4 font-semibold text-center w-24">SKS</th>
                                <th class="p-4 font-semibold text-center w-32">Kehadiran</th>
                                <th class="p-4 font-semibold w-40">Detail</th>
                                <th class="p-4 font-semibold text-right w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="course in paginatedCourses" :key="'list-'+course.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-[#234C6A] dark:text-white font-bold text-xs" x-text="course.code.substring(0,3)"></div>
                                            <div>
                                                <div class="font-bold text-slate-800 dark:text-slate-100 text-sm" x-text="course.name"></div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400" x-text="course.code"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-sm font-medium text-slate-600 dark:text-slate-300 line-clamp-1" x-text="course.lecturer"></div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold" x-text="course.sks"></span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="font-bold text-sm" :style="'color: ' + course.color" x-text="course.percentage + '%'"></span>
                                            <div class="w-16 h-1 bg-slate-100 dark:bg-slate-700 rounded-full mt-1">
                                                <div class="h-full rounded-full" :class="course.bgBar" :style="'width: ' + course.percentage + '%'"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex gap-1 text-[10px] font-bold">
                                            <span class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-1.5 py-0.5 rounded" x-text="'H:'+course.h"></span>
                                            <span class="bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 px-1.5 py-0.5 rounded" x-text="'S:'+course.s"></span>
                                            <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded" x-text="'I:'+course.i"></span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a :href="course.url" class="p-2 text-slate-400 hover:text-[#234C6A] dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all inline-block">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination (only for list view) -->
            <div x-show="viewMode === 'list' && totalPages > 1" class="mt-8 flex items-center justify-center gap-2" style="display: none;">
                <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                    class="px-3 py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="currentPage === 1 ? 'bg-siakad-light dark:bg-slate-700 text-siakad-secondary' : 'card-saas text-siakad-secondary hover:text-siakad-dark'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                
                <template x-for="page in totalPages" :key="page">
                    <button @click="goToPage(page)"
                        class="w-10 h-10 rounded-lg text-sm font-semibold transition-colors"
                        :class="currentPage === page ? 'bg-[#234C6A] text-white shadow-md' : 'card-saas text-siakad-secondary hover:text-siakad-dark'"
                        x-text="page">
                    </button>
                </template>
                
                <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                    class="px-3 py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="currentPage === totalPages ? 'bg-siakad-light dark:bg-slate-700 text-siakad-secondary' : 'card-saas text-siakad-secondary hover:text-siakad-dark'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
            
            <!-- Page Info (only for list view) -->
            <div x-show="viewMode === 'list' && totalPages > 1" class="mt-3 text-center text-xs text-siakad-secondary" style="display: none;">
                Menampilkan <span x-text="((currentPage - 1) * perPage) + 1"></span> - <span x-text="Math.min(currentPage * perPage, filteredCourses.length)"></span> dari <span x-text="filteredCourses.length"></span> mata kuliah
            </div>
        </div>
    </div>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>
