<x-app-layout>
    <x-slot name="header">
        {{ $tugas->judul }}
    </x-slot>

    <!-- Back Link -->
    <div class="mb-4">
        <a href="{{ route('mahasiswa.tugas.index', $kelas->id) }}" class="text-sm text-siakad-secondary hover:text-siakad-primary transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Daftar Tugas
        </a>
    </div>

    @if($isArchived)
    <!-- Archive Notice -->
    <div class="mb-4 p-3 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
            <span>Ini adalah tugas dari semester sebelumnya ({{ $kelas->tahunAkademik->display_name ?? 'Arsip' }}). Pengumpulan tugas tidak tersedia.</span>
        </div>
    </div>
    @endif

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Tugas Detail -->
        <div class="md:col-span-2 space-y-6">
            <div class="card-saas p-5 dark:bg-gray-800">
                <div class="flex items-center gap-2 mb-2">
                    <h2 class="text-xl font-bold text-siakad-dark dark:text-white">{{ $tugas->judul }}</h2>
                    @if($isArchived)
                    <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-full">Arsip</span>
                    @endif
                </div>
                <p class="text-sm text-siakad-secondary dark:text-gray-400 mb-4">{{ $kelas->mataKuliah->nama_mk }} - {{ $kelas->nama_kelas }}</p>
                
                @if($tugas->deskripsi)
                <div class="prose dark:prose-invert max-w-none text-siakad-dark dark:text-gray-300 mb-4">
                    {!! nl2br(e($tugas->deskripsi)) !!}
                </div>
                @endif

                @if($tugas->file_tugas)
                <div class="mt-4">
                    <a href="{{ route('mahasiswa.tugas.download', [$kelas->id, $tugas->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-siakad-light dark:bg-gray-700 text-siakad-dark dark:text-white rounded-lg hover:bg-siakad-light/80 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download File Soal
                    </a>
                </div>
                @endif
            </div>

            <!-- Submission Form or Status -->
            @if($submission)
            <div class="card-saas p-5 dark:bg-gray-800">
                <h3 class="font-semibold text-siakad-dark dark:text-white mb-4">Tugas Anda</h3>
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                    <div class="flex items-center gap-2 text-green-600 dark:text-green-400 mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-medium">Tugas Sudah Dikumpulkan</span>
                    </div>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">
                        Dikumpulkan pada {{ $submission->submitted_at->format('d M Y, H:i') }}
                        @if(!$submission->isOnTime())
                        <span class="text-yellow-500">(Terlambat)</span>
                        @endif
                    </p>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">File: {{ $submission->file_name }}</p>
                </div>

                @if($submission->isGraded())
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-siakad-dark dark:text-white">Nilai Anda</span>
                        <span class="text-2xl font-bold text-siakad-primary">{{ $submission->nilai }} <span class="text-sm">({{ $submission->grade_letter }})</span></span>
                    </div>
                    @if($submission->feedback)
                    <div class="mt-3 pt-3 border-t border-blue-200 dark:border-blue-800">
                        <p class="text-sm font-medium text-siakad-dark dark:text-white mb-1">Feedback Dosen:</p>
                        <p class="text-sm text-siakad-secondary dark:text-gray-400">{{ $submission->feedback }}</p>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-sm text-siakad-secondary dark:text-gray-400 italic">Menunggu penilaian dari dosen...</p>
                @endif
            </div>
            @elseif($isArchived)
            <div class="card-saas p-5 dark:bg-gray-800">
                <div class="text-center py-4">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    <p class="text-siakad-dark dark:text-white font-medium">Arsip Semester</p>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">Tugas ini dari semester sebelumnya. Pengumpulan tidak tersedia.</p>
                </div>
            </div>
            @elseif($tugas->isOpen())
            <div class="card-saas p-5 dark:bg-gray-800">
                <h3 class="font-semibold text-siakad-dark dark:text-white mb-4">Kumpulkan Tugas</h3>
                <form action="{{ route('mahasiswa.tugas.submit', [$kelas->id, $tugas->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Upload File</label>
                            <input type="file" name="file" class="input-saas w-full px-4 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-siakad-primary/10 file:text-siakad-primary" required>
                            <p class="text-xs text-siakad-secondary mt-1">Ekstensi: {{ $tugas->allowed_extensions }} • Max: {{ $tugas->formatted_max_file_size }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Catatan (Opsional)</label>
                            <textarea name="catatan" rows="2" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Tambahkan catatan jika perlu..."></textarea>
                        </div>
                        <button type="submit" class="w-full btn-primary-saas py-3 rounded-lg text-sm font-medium">
                            Kumpulkan Tugas
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="card-saas p-5 dark:bg-gray-800">
                <div class="text-center py-4">
                    <svg class="w-12 h-12 mx-auto mb-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-siakad-dark dark:text-white font-medium">Tidak Dapat Mengumpulkan</p>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">Deadline sudah lewat atau tugas tidak aktif.</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
            <div class="card-saas p-4 dark:bg-gray-800">
                <h4 class="font-medium text-siakad-dark dark:text-white mb-3">Informasi Tugas</h4>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-siakad-secondary dark:text-gray-400">Deadline</dt>
                        <dd class="text-siakad-dark dark:text-white font-medium">{{ $tugas->deadline->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-siakad-secondary dark:text-gray-400">Waktu</dt>
                        <dd class="text-siakad-dark dark:text-white font-medium">{{ $tugas->deadline->format('H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-siakad-secondary dark:text-gray-400">Status</dt>
                        <dd>
                            @if($tugas->isOverdue())
                            <span class="text-red-500 font-medium">Lewat Deadline</span>
                            @else
                            <span class="text-green-500 font-medium">{{ $tugas->remaining_time }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
