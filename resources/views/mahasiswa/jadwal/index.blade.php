<x-app-layout>
    <x-slot name="header">
        Jadwal Kuliah
    </x-slot>

    @php
        $today = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd');
        $now = \Carbon\Carbon::now();
        $hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    @endphp

    <div x-data="{ selectedDay: '{{ $today }}', viewMode: 'card' }">
        @if(!$activeTA)
        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-6 text-center max-w-md mx-auto">
            <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="font-semibold text-amber-800 mb-1">Tidak Ada Tahun Akademik Aktif</h3>
            <p class="text-sm text-amber-600">Hubungi admin untuk mengaktifkan tahun akademik.</p>
        </div>
        @elseif($jadwalPerHari->isEmpty())
        <div class="card-saas p-10 text-center max-w-md mx-auto">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Belum Ada Jadwal</h3>
            <p class="text-slate-500 mb-5">KRS Anda belum diapprove atau belum ada jadwal kuliah.</p>
            <a href="{{ route('mahasiswa.krs.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#234C6A] text-white rounded-lg text-sm font-medium hover:bg-[#1B3C53] transition">
                Lihat KRS
            </a>
        </div>
        @else
        
        <!-- Controls Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <!-- Day Tabs -->
            <div class="overflow-x-auto pb-1 sm:pb-0">
                <div class="flex gap-2 min-w-max">
                    @foreach($hariOrder as $hari)
                        @php $hasClass = $jadwalPerHari->has($hari); $count = $hasClass ? $jadwalPerHari[$hari]->count() : 0; @endphp
                        <button 
                            @click="selectedDay = '{{ $hari }}'"
                            :class="selectedDay === '{{ $hari }}' ? 'bg-[#234C6A] text-white shadow-md' : '{{ $hasClass ? 'bg-siakad-light dark:bg-slate-700 text-siakad-secondary hover:bg-siakad-light/70 dark:hover:bg-slate-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-not-allowed' }}'"
                            class="px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 flex items-center gap-1.5"
                            {{ !$hasClass ? 'disabled' : '' }}>
                            <span>{{ $hari }}</span>
                            @if($hasClass)
                            <span class="text-xs opacity-70">({{ $count }})</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
            
            <!-- View Toggle -->
            <div class="flex items-center gap-1 bg-siakad-light dark:bg-slate-700 p-1 rounded-lg self-start sm:self-auto hidden md:block">
                <button @click="viewMode = 'card'" 
                    :class="viewMode === 'card' ? 'bg-white shadow-sm text-[#234C6A]' : 'text-slate-400'"
                    class="p-2 rounded-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                </button>
                <button @click="viewMode = 'compact'" 
                    :class="viewMode === 'compact' ? 'bg-white shadow-sm text-[#234C6A]' : 'text-slate-400'"
                    class="p-2 rounded-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </div>

        <!-- Schedule Content -->
        @foreach($hariOrder as $hari)
            @if($jadwalPerHari->has($hari))
            <div x-show="selectedDay === '{{ $hari }}'" x-cloak class="space-y-4">
                @foreach($jadwalPerHari[$hari] as $item)
                @php
                    $kelas = $item['kelas'];
                    $jadwal = $item['jadwal'];
                    $jamMulai = \Carbon\Carbon::parse($jadwal->jam_mulai);
                    $jamSelesai = \Carbon\Carbon::parse($jadwal->jam_selesai);
                    $isOngoing = $hari === $today && $now->between($jamMulai, $jamSelesai);
                @endphp
                
                <!-- Card View -->
                <div x-show="viewMode === 'card'" class="flex gap-5 items-start">
                    <!-- Time Column -->
                    <div class="hidden sm:block w-20 flex-shrink-0 text-right pt-5">
                        <p class="text-lg font-semibold text-siakad-dark">{{ $jamMulai->format('H:i') }}</p>
                        <p class="text-xs text-siakad-secondary">{{ $jamSelesai->format('H:i') }}</p>
                    </div>
                    
                    <!-- Card -->
                    <div class="flex-1 card-saas p-4 sm:p-5 hover:shadow-md transition {{ $isOngoing ? 'border-l-[3px] border-l-[#234C6A]' : '' }}">
                        <!-- Mobile Time Header -->
                        <div class="md:hidden flex justify-between items-center mb-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-siakad-dark">{{ $jamMulai->format('H:i') }}</span>
                                <span class="text-gray-400 text-xs">-</span>
                                <span class="text-sm text-siakad-secondary">{{ $jamSelesai->format('H:i') }}</span>
                            </div>
                            @if($isOngoing)
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-medium rounded-full flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                Berlangsung
                            </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-siakad-light dark:bg-slate-700 text-siakad-secondary text-xs font-semibold rounded">{{ $kelas->mataKuliah->kode_mk }}</span>
                                <span class="text-xs text-siakad-secondary">{{ $kelas->mataKuliah->sks }} SKS</span>
                            </div>
                            @if($isOngoing)
                            <span class="hidden md:flex px-2 py-1 bg-emerald-50 text-emerald-600 text-xs font-medium rounded-full items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                Berlangsung
                            </span>
                            @endif
                        </div>
                        
                        <h4 class="font-semibold text-siakad-dark mb-1">{{ $kelas->mataKuliah->nama_mk }}</h4>
                        <p class="text-sm text-siakad-secondary mb-3">Kelas {{ $kelas->nama_kelas }}</p>
                        
                        <div class="flex flex-wrap items-center gap-3 pt-3 border-t border-slate-100 text-sm">
                            <span class="flex items-center gap-1.5 text-siakad-secondary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ $kelas->dosen->user->name ?? 'TBA' }}
                            </span>
                            @if($jadwal->ruangan)
                            <span class="flex items-center gap-1.5 text-[#234C6A] bg-[#234C6A]/10 px-2 py-1 rounded font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                {{ $jadwal->ruangan }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Compact View -->
                <div x-show="viewMode === 'compact'" class="card-saas px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-3 {{ $isOngoing ? 'border-l-[3px] border-l-[#234C6A]' : '' }}">
                    <div class="sm:w-24 flex-shrink-0">
                        <span class="font-semibold text-siakad-dark">{{ $jamMulai->format('H:i') }}</span>
                        <span class="text-siakad-secondary mx-1">—</span>
                        <span class="text-siakad-secondary">{{ $jamSelesai->format('H:i') }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-xs text-siakad-secondary">{{ $kelas->mataKuliah->kode_mk }}</span>
                            <span class="font-semibold text-siakad-dark">{{ $kelas->mataKuliah->nama_mk }}</span>
                            @if($isOngoing)
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            @endif
                        </div>
                        <p class="text-sm text-siakad-secondary">{{ $kelas->dosen->user->name ?? 'TBA' }} • Kelas {{ $kelas->nama_kelas }}</p>
                    </div>
                    @if($jadwal->ruangan)
                    <span class="text-[#234C6A] bg-[#234C6A]/10 px-3 py-1.5 rounded font-medium text-sm flex-shrink-0">
                        {{ $jadwal->ruangan }}
                    </span>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        @endforeach

        <!-- Summary -->
        <div class="mt-8 bg-[#1B3C53] rounded-2xl p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-white font-semibold">Ringkasan Jadwal</p>
                        <p class="text-white/60 text-sm">{{ $activeTA->tahun }} • Semester {{ $activeTA->semester }}</p>
                    </div>
                </div>
                <div class="flex justify-between w-full sm:w-auto sm:justify-start gap-4 sm:gap-8">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-white text-xl font-bold">{{ $jadwalPerHari->flatten(1)->count() }}</p>
                            <p class="text-white/60 text-xs">Sesi Kuliah</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <p class="text-white text-xl font-bold">{{ $jadwalPerHari->keys()->count() }}</p>
                            <p class="text-white/60 text-xs">Hari Aktif</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <style>[x-cloak] { display: none !important; }</style>
</x-app-layout>
