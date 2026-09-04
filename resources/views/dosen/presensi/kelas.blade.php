<x-app-layout>
    <x-slot name="header">
        Presensi Kelas
    </x-slot>

    <!-- Breadcrumb -->
    <div class="mb-5">
        <a href="{{ route('dosen.presensi.index') }}" class="inline-flex items-center gap-2 text-siakad-secondary hover:text-siakad-primary transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Daftar Kelas
        </a>
    </div>

    <!-- Compact Header Card -->
    <div class="card-saas p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-xl bg-siakad-primary/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-siakad-dark">{{ $kelas->mataKuliah->nama_mk }}</h1>
                    <p class="text-sm text-siakad-secondary mt-0.5">{{ $kelas->mataKuliah->kode_mk }} • {{ $kelas->mataKuliah->sks }} SKS • Kelas {{ $kelas->nama_kelas }}</p>
                    <div class="flex items-center gap-4 mt-2">
                        <span class="inline-flex items-center gap-1.5 text-xs text-siakad-secondary">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ $kelas->krsDetail->count() }} Mahasiswa
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs text-siakad-secondary">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ $pertemuanList->count() }} Pertemuan
                        </span>
                    </div>
                </div>
            </div>
            <a href="{{ route('dosen.presensi.pertemuan.create', $kelas) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-siakad-primary text-white rounded-lg font-medium hover:bg-siakad-primary/90 transition text-sm min-h-[44px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Buat Pertemuan
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pertemuan List -->
        <div class="lg:col-span-2">
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-4 border-b border-siakad-light flex items-center justify-between">
                    <h3 class="font-semibold text-siakad-dark">Daftar Pertemuan</h3>
                    <span class="text-xs text-siakad-secondary bg-siakad-light px-2 py-1 rounded-full">{{ $pertemuanList->count() }} total</span>
                </div>
                
                @if($pertemuanList->isEmpty())
                <div class="p-10 text-center">
                    <div class="w-16 h-16 rounded-full bg-siakad-light/50 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="text-siakad-secondary mb-4">Belum ada pertemuan untuk kelas ini</p>
                    <a href="{{ route('dosen.presensi.pertemuan.create', $kelas) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-siakad-primary text-white rounded-lg hover:bg-siakad-primary/90 transition text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Buat Pertemuan Pertama
                    </a>
                </div>
                @else
                <div class="divide-y divide-siakad-light">
                    @foreach($pertemuanList as $pertemuan)
                    <div class="p-4 flex items-center justify-between hover:bg-siakad-light/30 transition group">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-lg bg-siakad-primary/10 text-siakad-primary flex items-center justify-center font-bold text-sm">
                                {{ $pertemuan->pertemuan_ke }}
                            </div>
                            <div>
                                <p class="font-medium text-siakad-dark">Pertemuan {{ $pertemuan->pertemuan_ke }}</p>
                                <p class="text-sm text-siakad-secondary">{{ $pertemuan->tanggal->format('d M Y') }} • {{ $pertemuan->materi ?? 'Belum ada materi' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @php
                                $count = $pertemuan->presensi->count();
                                $total = $kelas->krsDetail->count();
                            @endphp
                            <span class="text-xs px-2.5 py-1 rounded-full {{ $count > 0 ? 'bg-siakad-primary/10 text-siakad-primary' : 'bg-siakad-light text-siakad-secondary' }}">
                                {{ $count }}/{{ $total }} hadir
                            </span>
                            <a href="{{ route('dosen.presensi.input', $pertemuan) }}" class="p-2 text-siakad-primary hover:bg-siakad-primary/10 rounded-lg transition opacity-0 group-hover:opacity-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Rekap Mahasiswa -->
        <div>
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Rekap Kehadiran</h3>
                </div>
                
                @if($rekapMahasiswa->isEmpty())
                <div class="p-8 text-center text-siakad-secondary text-sm">
                    Belum ada mahasiswa terdaftar
                </div>
                @else
                <div class="divide-y divide-siakad-light max-h-[480px] overflow-y-auto">
                    @foreach($rekapMahasiswa as $item)
                    <div class="p-4 hover:bg-siakad-light/20 transition">
                        <div class="flex items-center gap-3 mb-2.5">
                            <div class="w-9 h-9 rounded-full bg-siakad-primary text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($item['mahasiswa']->user->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-siakad-dark truncate">{{ $item['mahasiswa']->user->name }}</p>
                                <p class="text-xs text-siakad-secondary">{{ $item['mahasiswa']->nim }}</p>
                            </div>
                            <span class="text-sm font-bold {{ $item['rekap']['persentase'] >= 75 ? 'text-siakad-primary' : 'text-siakad-secondary' }}">
                                {{ $item['rekap']['persentase'] }}%
                            </span>
                        </div>
                        <!-- Progress bar -->
                        <div class="h-1.5 bg-siakad-light rounded-full overflow-hidden mb-2">
                            <div class="h-full bg-siakad-primary rounded-full transition-all" style="width: {{ $item['rekap']['persentase'] }}%"></div>
                        </div>
                        <div class="flex gap-2 text-xs">
                            <span class="px-2 py-0.5 bg-siakad-primary/10 text-siakad-primary rounded">H:{{ $item['rekap']['hadir'] }}</span>
                            <span class="px-2 py-0.5 bg-siakad-primary/5 text-siakad-secondary rounded">S:{{ $item['rekap']['sakit'] }}</span>
                            <span class="px-2 py-0.5 bg-siakad-primary/5 text-siakad-secondary rounded">I:{{ $item['rekap']['izin'] }}</span>
                            <span class="px-2 py-0.5 bg-siakad-light text-siakad-secondary rounded">A:{{ $item['rekap']['alpa'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
