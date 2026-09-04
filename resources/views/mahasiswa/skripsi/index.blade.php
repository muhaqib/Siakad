<x-app-layout>
    <x-slot name="header">
        Skripsi / Tugas Akhir
    </x-slot>

    <div class="mb-6">
        <p class="text-sm text-siakad-secondary">Kelola skripsi dan tugas akhir Anda</p>
    </div>

    @if(!$skripsi)
    <!-- No Skripsi Yet -->
    <div class="card-saas p-12 text-center">
        <div class="w-20 h-20 rounded-full bg-siakad-light/50 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
        <h3 class="text-xl font-bold text-siakad-dark mb-2">Belum Ada Pengajuan Skripsi</h3>
        <p class="text-siakad-secondary mb-6">Ajukan judul skripsi Anda untuk memulai proses bimbingan</p>
        <a href="{{ route('mahasiswa.skripsi.create') }}" class="btn-primary-saas px-6 py-3 rounded-lg text-sm font-medium inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Ajukan Judul Skripsi
        </a>
    </div>
    @else
    <!-- Skripsi Dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Status Card -->
        <div class="lg:col-span-2 card-saas p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <span class="inline-flex px-3 py-1 text-xs font-medium rounded-full bg-{{ $skripsi->status_color }}-100 text-{{ $skripsi->status_color }}-700">
                        {{ $skripsi->status_label }}
                    </span>
                    <h2 class="text-lg font-bold text-siakad-dark mt-3">{{ $skripsi->judul }}</h2>
                    @if($skripsi->bidang_kajian)
                    <p class="text-sm text-siakad-secondary mt-1">Bidang: {{ $skripsi->bidang_kajian }}</p>
                    @endif
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-6">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="text-siakad-secondary">Progress</span>
                    <span class="font-semibold text-siakad-primary">{{ $skripsi->progress_percent }}%</span>
                </div>
                <div class="h-3 bg-siakad-light rounded-full overflow-hidden">
                    <div class="h-full bg-siakad-primary rounded-full transition-all" style="width: {{ $skripsi->progress_percent }}%"></div>
                </div>
            </div>

            <!-- Pembimbing -->
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-siakad-light/30">
                    <p class="text-xs text-siakad-secondary mb-1">Pembimbing 1</p>
                    <p class="font-medium text-siakad-dark">{{ $skripsi->pembimbing1?->user->name ?? 'Belum ditentukan' }}</p>
                </div>
                <div class="p-4 rounded-xl bg-siakad-light/30">
                    <p class="text-xs text-siakad-secondary mb-1">Pembimbing 2</p>
                    <p class="font-medium text-siakad-dark">{{ $skripsi->pembimbing2?->user->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card-saas p-6">
            <h3 class="font-semibold text-siakad-dark mb-4">Timeline</h3>
            @php
                // If status is 'selesai' or progress is 100%, all milestones are completed
                $allCompleted = $skripsi->status === 'selesai' || $skripsi->progress_percent >= 100;
                
                $milestones = [
                    ['date' => $skripsi->tanggal_pengajuan, 'label' => 'Pengajuan'],
                    ['date' => $skripsi->tanggal_acc_judul, 'label' => 'ACC Judul'],
                    ['date' => $skripsi->tanggal_seminar_proposal, 'label' => 'Sempro'],
                    ['date' => $skripsi->tanggal_seminar_hasil, 'label' => 'Semhas'],
                    ['date' => $skripsi->tanggal_sidang, 'label' => 'Sidang'],
                    ['date' => $skripsi->tanggal_selesai, 'label' => 'Selesai'],
                ];
                
                // Find the last completed milestone index
                $lastCompletedIndex = -1;
                foreach($milestones as $idx => $m) {
                    if ($m['date'] != null) {
                        $lastCompletedIndex = $idx;
                    }
                }
            @endphp
            <div class="space-y-0">
                @foreach($milestones as $index => $m)
                @php
                    // Mark as completed if has date OR if all milestones should be completed
                    $isCompleted = $m['date'] != null || $allCompleted;
                    // Current is the next step after the last completed one (only if not all completed)
                    $isCurrent = !$allCompleted && !$m['date'] && $index == $lastCompletedIndex + 1;
                    $isPending = !$isCompleted && !$isCurrent;
                @endphp
                <div class="flex items-start gap-3 relative">
                    <!-- Connector Line -->
                    @if($index < count($milestones) - 1)
                    <div class="absolute left-[7px] top-4 w-0.5 h-full {{ $isCompleted ? 'bg-[#234C6A]' : 'bg-slate-200' }}"></div>
                    @endif
                    
                    <!-- Dot -->
                    <div class="relative z-10 mt-1 flex-shrink-0">
                        @if($isCompleted)
                        <div class="w-4 h-4 rounded-full bg-[#234C6A] flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        @elseif($isCurrent)
                        <div class="w-4 h-4 rounded-full bg-[#456882] animate-pulse"></div>
                        @else
                        <div class="w-4 h-4 rounded-full border-2 border-slate-300 bg-white"></div>
                        @endif
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 pb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm {{ $isCompleted ? 'font-medium text-siakad-dark' : ($isCurrent ? 'font-medium text-[#456882]' : 'text-slate-400') }}">{{ $m['label'] }}</span>
                            <span class="text-xs {{ $isCompleted ? 'text-siakad-secondary' : 'text-slate-400' }}">{{ $m['date'] ? $m['date']->format('d/m/Y') : ($isCompleted ? '✓' : '-') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Stats & Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card-saas p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-[#234C6A]/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-[#234C6A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark">{{ $bimbinganList->count() ?? 0 }}</p>
                <p class="text-sm text-siakad-secondary">Total Bimbingan</p>
            </div>
        </div>
        <div class="card-saas p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark">{{ $skripsi->tanggal_pengajuan ? round($skripsi->tanggal_pengajuan->diffInMonths(now())) : 0 }}</p>
                <p class="text-sm text-siakad-secondary">Bulan Berjalan</p>
            </div>
        </div>
        <div class="card-saas p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark">{{ $skripsi->progress_percent }}%</p>
                <p class="text-sm text-siakad-secondary">Progress Selesai</p>
            </div>
        </div>
    </div>

    <!-- Bimbingan Section -->
    @if(in_array($skripsi->status, ['bimbingan', 'penelitian', 'diterima', 'selesai']))
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card-saas overflow-hidden">
            <div class="px-6 py-4 border-b border-siakad-light flex items-center justify-between">
                <h3 class="font-semibold text-siakad-dark">Riwayat Bimbingan</h3>
                <span class="text-sm text-siakad-secondary">{{ $bimbinganList->count() }} catatan</span>
            </div>
            @forelse($bimbinganList as $bimbingan)
            <div class="p-5 border-b border-siakad-light/50">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-siakad-primary/10 flex items-center justify-center text-siakad-primary font-bold text-sm">
                        {{ $loop->iteration }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="font-medium text-siakad-dark">{{ $bimbingan->tanggal_bimbingan->format('d M Y') }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-{{ $bimbingan->status_color }}-100 text-{{ $bimbingan->status_color }}-700">{{ $bimbingan->status_label }}</span>
                        </div>
                        <p class="text-sm text-siakad-secondary mb-2">{{ $bimbingan->catatan_mahasiswa }}</p>
                        @if($bimbingan->catatan_dosen)
                        <div class="mt-3 p-3 rounded-lg bg-siakad-light/30">
                            <p class="text-xs text-siakad-secondary mb-1">Feedback {{ $bimbingan->dosen->user->name }}:</p>
                            <p class="text-sm text-siakad-dark">{{ $bimbingan->catatan_dosen }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-siakad-secondary">Belum ada catatan bimbingan</div>
            @endforelse
        </div>

        <!-- Add Bimbingan Form -->
        <div class="card-saas p-6">
            <h3 class="font-semibold text-siakad-dark mb-4">Tambah Catatan Bimbingan</h3>
            <form action="{{ route('mahasiswa.skripsi.bimbingan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark mb-2">Progress / Catatan *</label>
                        <textarea name="catatan_mahasiswa" rows="4" class="input-saas w-full text-sm" style="background-color: var(--bg-card);" placeholder="Deskripsikan progress Anda..." required></textarea>
                    </div>
                    <div x-data="{ fileName: '' }">
                        <label class="block text-sm font-medium text-siakad-dark mb-2">Upload Dokumen</label>
                        <label class="relative flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-[#234C6A] hover:bg-[#234C6A]/5 transition-all">
                            <div class="flex flex-col items-center justify-center text-center px-4">
                                <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <p class="text-sm text-slate-500" x-show="!fileName">Klik untuk upload atau drag file</p>
                                <p class="text-sm font-medium text-[#234C6A]" x-show="fileName" x-text="fileName"></p>
                                <p class="text-xs text-slate-400 mt-1">PDF, DOC, DOCX (max 10MB)</p>
                            </div>
                            <input type="file" name="file_dokumen" accept=".pdf,.doc,.docx" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="fileName = $event.target.files[0]?.name || ''">
                        </label>
                    </div>
                    <button type="submit" class="btn-primary-saas w-full py-2.5 rounded-lg text-sm font-medium">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endif
</x-app-layout>
