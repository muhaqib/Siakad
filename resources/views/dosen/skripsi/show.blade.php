<x-app-layout>
    <x-slot name="header">
        Detail Skripsi
    </x-slot>

    <div class="mb-6">
        <a href="{{ route('dosen.skripsi.index') }}" class="inline-flex items-center gap-2 text-siakad-secondary hover:text-siakad-primary transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Header Card -->
            <div class="card-saas p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-{{ $skripsi->status_color }}-100 text-{{ $skripsi->status_color }}-700">{{ $skripsi->status_label }}</span>
                        <h2 class="text-lg font-bold text-siakad-dark mt-3">{{ $skripsi->judul }}</h2>
                    </div>
                    <span class="text-2xl font-bold text-siakad-primary">{{ $skripsi->progress_percent }}%</span>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="p-3 rounded-lg bg-siakad-light/30">
                        <p class="text-xs text-siakad-secondary">Mahasiswa</p>
                        <p class="font-medium text-siakad-dark">{{ $skripsi->mahasiswa->user->name }}</p>
                        <p class="text-xs text-siakad-secondary">{{ $skripsi->mahasiswa->nim }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-siakad-light/30">
                        <p class="text-xs text-siakad-secondary">Bidang Kajian</p>
                        <p class="font-medium text-siakad-dark">{{ $skripsi->bidang_kajian ?? '-' }}</p>
                    </div>
                </div>

                <!-- Update Status -->
                <form action="{{ route('dosen.skripsi.update-status', $skripsi) }}" method="POST" class="mt-6 flex items-end gap-3">
                    @csrf @method('PUT')
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-siakad-dark mb-1">Update Status</label>
                        <select name="status" class="input-saas w-full text-sm">
                            @foreach(\App\Models\Skripsi::getStatusList() as $key => $label)
                            <option value="{{ $key }}" {{ $skripsi->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Update</button>
                </form>
            </div>

            <!-- Bimbingan History -->
            <div class="card-saas overflow-hidden">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Riwayat Bimbingan</h3>
                </div>
                @forelse($skripsi->bimbingan as $bimbingan)
                <div class="p-5 border-b border-siakad-light/50">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-{{ $bimbingan->status_color }}-100 flex items-center justify-center text-{{ $bimbingan->status_color }}-600">
                            @if($bimbingan->status === 'disetujui')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @elseif($bimbingan->status === 'revisi')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-medium text-siakad-dark">{{ $bimbingan->tanggal_bimbingan->format('d M Y') }}</span>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-{{ $bimbingan->status_color }}-100 text-{{ $bimbingan->status_color }}-700">{{ $bimbingan->status_label }}</span>
                            </div>
                            <p class="text-sm text-siakad-secondary">{{ $bimbingan->catatan_mahasiswa }}</p>
                            
                            @if($bimbingan->file_dokumen)
                            <a href="{{ Storage::url($bimbingan->file_dokumen) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-siakad-primary mt-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Lihat Dokumen
                            </a>
                            @endif

                            @if($bimbingan->catatan_dosen)
                            <div class="mt-3 p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                                <p class="text-xs text-emerald-600 mb-1">Feedback Anda:</p>
                                <p class="text-sm text-emerald-800">{{ $bimbingan->catatan_dosen }}</p>
                            </div>
                            @elseif($bimbingan->status === 'menunggu')
                            <!-- Review Form -->
                            <form action="{{ route('dosen.skripsi.bimbingan.review', $bimbingan) }}" method="POST" class="mt-3 p-3 rounded-lg bg-amber-50 border border-amber-100">
                                @csrf
                                <textarea name="catatan_dosen" rows="2" class="w-full text-sm border border-amber-200 rounded-lg p-2 focus:ring-amber-500 focus:border-amber-500" placeholder="Berikan feedback..." required></textarea>
                                <div class="flex items-center gap-2 mt-2">
                                    <button type="submit" name="status" value="disetujui" class="px-3 py-1.5 text-xs font-medium bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Setujui</button>
                                    <button type="submit" name="status" value="revisi" class="px-3 py-1.5 text-xs font-medium bg-red-600 text-white rounded-lg hover:bg-red-700">Perlu Revisi</button>
                                </div>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-siakad-secondary">Belum ada catatan bimbingan</div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Pembimbing -->
            <div class="card-saas p-5">
                <h3 class="font-semibold text-siakad-dark mb-4">Tim Pembimbing</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-siakad-primary text-white flex items-center justify-center font-bold">1</div>
                        <div>
                            <p class="font-medium text-siakad-dark">{{ $skripsi->pembimbing1?->user->name ?? '-' }}</p>
                            <p class="text-xs text-siakad-secondary">Pembimbing Utama</p>
                        </div>
                    </div>
                    @if($skripsi->pembimbing2)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-siakad-secondary/20 text-siakad-secondary flex items-center justify-center font-bold">2</div>
                        <div>
                            <p class="font-medium text-siakad-dark">{{ $skripsi->pembimbing2->user->name }}</p>
                            <p class="text-xs text-siakad-secondary">Pembimbing Pendamping</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="card-saas p-5">
                <h3 class="font-semibold text-siakad-dark mb-4">Timeline</h3>
                <div class="space-y-3 text-sm">
                    @php
                        $milestones = [
                            ['date' => $skripsi->tanggal_pengajuan, 'label' => 'Pengajuan'],
                            ['date' => $skripsi->tanggal_acc_judul, 'label' => 'ACC Judul'],
                            ['date' => $skripsi->tanggal_seminar_proposal, 'label' => 'Sempro'],
                            ['date' => $skripsi->tanggal_seminar_hasil, 'label' => 'Semhas'],
                            ['date' => $skripsi->tanggal_sidang, 'label' => 'Sidang'],
                            ['date' => $skripsi->tanggal_selesai, 'label' => 'Selesai'],
                        ];
                    @endphp
                    @foreach($milestones as $m)
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full {{ $m['date'] ? 'bg-emerald-500' : 'bg-siakad-light' }}"></div>
                        <span class="{{ $m['date'] ? 'text-siakad-dark' : 'text-siakad-secondary' }}">{{ $m['label'] }}</span>
                        <span class="ml-auto text-siakad-secondary">{{ $m['date'] ? $m['date']->format('d/m/Y') : '-' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
