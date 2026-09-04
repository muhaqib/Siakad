<x-app-layout>
    <x-slot name="header">
        Detail Dosen - {{ $dosen->user->name }}
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="card-saas p-8 dark:bg-gray-800">
                <div class="text-center mb-8">
                    <div class="w-24 h-24 rounded-full bg-siakad-primary/10 dark:bg-blue-900/30 flex items-center justify-center text-siakad-primary dark:text-blue-400 text-3xl font-bold mx-auto mb-5 ring-4 ring-white dark:ring-gray-700 shadow-lg">
                        {{ strtoupper(substr($dosen->user->name, 0, 1)) }}
                    </div>
                    <h3 class="text-xl font-bold text-siakad-dark dark:text-white mb-1">{{ $dosen->user->name }}</h3>
                    <p class="text-sm font-medium text-siakad-secondary dark:text-gray-400">{{ $dosen->nidn }}</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-siakad-light/30 dark:bg-gray-700/30">
                        <span class="text-xs font-medium text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Email</span>
                        <span class="text-sm font-semibold text-siakad-dark dark:text-gray-200 text-right">{{ $dosen->user->email }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-siakad-light/30 dark:bg-gray-700/30">
                        <span class="text-xs font-medium text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Prodi</span>
                        <span class="text-sm font-semibold text-siakad-dark dark:text-gray-200 text-right">{{ $dosen->prodi->nama }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-siakad-light/30 dark:bg-gray-700/30">
                        <span class="text-xs font-medium text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Fakultas</span>
                        <span class="text-sm font-semibold text-siakad-dark dark:text-gray-200 text-right">{{ $dosen->prodi->fakultas->nama }}</span>
                    </div>
                </div>
            </div>

            <!-- Teaching Summary -->
            <div class="bg-gradient-to-br from-siakad-primary to-siakad-dark text-white p-6 shadow-xl rounded-2xl dark:border dark:border-gray-700">
                <div class="grid grid-cols-2 gap-6 text-center divide-x divide-white/10">
                    <div>
                        <p class="text-4xl font-bold tracking-tight mb-1">{{ $dosen->kelas->count() }}</p>
                        <p class="text-xs font-medium text-white/70 uppercase tracking-wider">Kelas Diampu</p>
                    </div>
                    <div>
                        <p class="text-4xl font-bold tracking-tight mb-1">{{ $totalSks }}</p>
                        <p class="text-xs font-medium text-white/70 uppercase tracking-wider">Total SKS</p>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-white/10 text-center">
                    <p class="text-4xl font-bold tracking-tight mb-1">{{ $totalStudents }}</p>
                    <p class="text-xs font-medium text-white/70 uppercase tracking-wider">Total Mahasiswa</p>
                </div>
            </div>

            <a href="{{ route('admin.dosen.index') }}" class="flex items-center justify-center gap-2 w-full py-3 text-sm font-medium text-siakad-secondary dark:text-gray-400 hover:text-siakad-primary dark:hover:text-blue-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke daftar dosen
            </a>
        </div>

        <!-- Teaching Load -->
        <div class="lg:col-span-2">
            <div class="card-saas overflow-hidden dark:bg-gray-800">
                <div class="px-6 py-5 border-b border-siakad-light dark:border-gray-700 bg-siakad-light/10 dark:bg-gray-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-siakad-dark dark:text-white">Beban Mengajar</h3>
                        <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-1 pb-2">Monitoring aktivitas pengajaran semester ini</p>
                    </div>
                    @if($teachingLoad->isNotEmpty())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-siakad-primary/10 text-siakad-primary dark:bg-blue-900/50 dark:text-blue-400 border border-siakad-primary/20 dark:border-blue-500/30">
                        {{ count($teachingLoad) }} Mata Kuliah Aktif
                    </span>
                    @endif
                </div>

                <div class="divide-y divide-siakad-light/50 dark:divide-gray-700/50">
                    @forelse($teachingLoad as $kelas)
                    @php
                        $studentCount = $kelas->krsDetail->count();
                        $gradedCount = \App\Models\Nilai::where('kelas_id', $kelas->id)->count();
                        $progress = $studentCount > 0 ? round(($gradedCount / $studentCount) * 100) : 0;
                    @endphp
                    <div class="p-6 hover:bg-siakad-light/5 dark:hover:bg-gray-700/20 transition-colors group">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-siakad-primary/5 dark:bg-gray-700 flex items-center justify-center text-siakad-primary dark:text-blue-400 font-bold text-lg group-hover:bg-siakad-primary text-transition group-hover:text-white dark:group-hover:bg-blue-600 dark:group-hover:text-white transition-all duration-300">
                                    {{ substr($kelas->nama_kelas, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-siakad-dark dark:text-white text-lg">{{ $kelas->mataKuliah->nama_mk }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 rounded textxs font-medium bg-siakad-light dark:bg-gray-700 text-siakad-dark dark:text-gray-300">{{ $kelas->nama_kelas }}</span>
                                        <span class="text-sm text-siakad-secondary dark:text-gray-500">• {{ $kelas->mataKuliah->kode_mk }} • {{ $kelas->mataKuliah->sks }} SKS</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-siakad-dark dark:text-white text-lg">{{ $studentCount }} <span class="text-sm font-normal text-siakad-secondary dark:text-gray-400">Mahasiswa</span></p>
                                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 mt-1 flex items-center justify-end gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $gradedCount }} sudah dinilai
                                </p>
                            </div>
                        </div>
                        
                        <div class="relative pt-2">
                            <div class="flex items-center justify-between text-xs font-medium text-siakad-secondary dark:text-gray-400 mb-2">
                                <span>Progress Penilaian</span>
                                <span>{{ $progress }}%</span>
                            </div>
                            <div class="flex w-full h-2.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="flex flex-col justify-center overflow-hidden bg-siakad-primary dark:bg-blue-500 text-xs text-white text-center whitespace-nowrap transition-all duration-500 ease-out shadow-sm"
                                     style="width: {{ $progress }}%">
                                     @if($progress > 0)<div class="w-full h-full bg-white/20 animate-pulse"></div>@endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-siakad-light/30 dark:bg-gray-700/30 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-siakad-secondary dark:text-gray-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h4 class="text-siakad-dark dark:text-white font-medium mb-1">Dosen Belum Memiliki Kelas</h4>
                        <p class="text-siakad-secondary dark:text-gray-400 text-sm max-w-sm mx-auto">Dosen ini belum ditugaskan untuk mengampu kelas manapun pada semester aktif saat ini.</p>
                    </div>
                    @endforelse
                </div>
                <!-- Pagination -->
                @if($teachingLoad->hasPages())
                <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700">
                    {{ $teachingLoad->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
