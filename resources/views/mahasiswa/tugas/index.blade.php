<x-app-layout>
    <x-slot name="header">
        Tugas - {{ $kelas->mataKuliah->nama_mk }}
    </x-slot>

    @if($isArchived)
    <!-- Archive Notice -->
    <div class="mb-4 p-3 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
            <span>Ini adalah kelas dari semester sebelumnya ({{ $kelas->tahunAkademik->display_name ?? 'Arsip' }}). Pengumpulan tugas tidak tersedia.</span>
        </div>
    </div>
    @endif

    <!-- Class Info -->
    <div class="card-saas p-4 mb-6 dark:bg-gray-800">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-siakad-primary to-siakad-dark rounded-xl flex items-center justify-center text-white font-bold text-lg">
                {{ $kelas->nama_kelas }}
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-siakad-dark dark:text-white">{{ $kelas->mataKuliah->nama_mk }}</h2>
                    @if($isArchived)
                    <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-full">Arsip</span>
                    @endif
                </div>
                <p class="text-sm text-siakad-secondary dark:text-gray-400">{{ $kelas->mataKuliah->kode_mk }} • Dosen: {{ $kelas->dosen->user->name ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Tugas List -->
    <div class="space-y-4">
        @forelse($tugasList as $tugas)
        @php
            $submission = $tugas->submissions->first();
        @endphp
        <div class="card-saas dark:bg-gray-800 overflow-hidden">
            <div class="p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="font-semibold text-siakad-dark dark:text-white mb-1">{{ $tugas->judul }}</h3>
                        @if($tugas->deskripsi)
                        <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-3 line-clamp-2">{{ $tugas->deskripsi }}</p>
                        @endif
                        <div class="flex items-center gap-4 text-xs text-siakad-secondary dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @if($tugas->isOverdue())
                                <span class="text-red-500">Deadline lewat</span>
                                @else
                                {{ $tugas->remaining_time }}
                                @endif
                            </span>
                            <span>Deadline: {{ $tugas->deadline->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        @if($submission)
                            @if($submission->isGraded())
                            <div class="text-center">
                                <p class="text-2xl font-bold text-siakad-primary">{{ $submission->nilai }}</p>
                                <p class="text-xs text-siakad-secondary">Nilai ({{ $submission->grade_letter }})</p>
                            </div>
                            @else
                            <span class="px-3 py-1 text-xs bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 rounded-lg">Menunggu Penilaian</span>
                            @endif
                        @elseif($isArchived)
                        <span class="px-3 py-1 text-xs bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-lg">Arsip Semester</span>
                        @elseif($tugas->isOpen())
                        <a href="{{ route('mahasiswa.tugas.show', [$kelas->id, $tugas->id]) }}" class="px-4 py-2 text-sm font-medium bg-siakad-primary text-white rounded-lg hover:bg-siakad-primary/90 transition">
                            Kumpulkan Tugas
                        </a>
                        @else
                        <span class="px-3 py-1 text-xs bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg">Tidak Dapat Mengumpulkan</span>
                        @endif
                    </div>
                </div>
            </div>
            @if($submission)
            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/30 border-t border-siakad-light dark:border-gray-700 flex items-center justify-between">
                <p class="text-xs text-siakad-secondary dark:text-gray-400">
                    Dikumpulkan: {{ $submission->submitted_at->format('d M Y, H:i') }}
                    @if(!$submission->isOnTime())
                    <span class="text-yellow-500">(Terlambat)</span>
                    @endif
                </p>
                @if($submission->feedback)
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Feedback: {{ Str::limit($submission->feedback, 50) }}</p>
                @endif
            </div>
            @endif
        </div>
        @empty
        <div class="card-saas p-8 text-center dark:bg-gray-800">
            <svg class="w-12 h-12 mx-auto mb-3 text-siakad-secondary/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <p class="text-siakad-secondary dark:text-gray-400">Belum ada tugas untuk kelas ini.</p>
        </div>
        @endforelse
    </div>

    <!-- Back Link -->
    <div class="mt-6">
        <a href="{{ route('mahasiswa.lms.index') }}" class="text-sm text-siakad-secondary hover:text-siakad-primary transition">
            ← Kembali ke Materi & Tugas
        </a>
    </div>
</x-app-layout>
