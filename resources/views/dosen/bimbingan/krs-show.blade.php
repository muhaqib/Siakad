<x-app-layout>
    <x-slot name="header">
        Detail KRS
    </x-slot>

    <div class="mb-4">
        <a href="{{ route('dosen.bimbingan.krs-approval') }}" class="inline-flex items-center gap-2 text-sm text-siakad-secondary hover:text-siakad-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Approval KRS
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Student Info -->
        <div class="lg:col-span-1">
            <div class="card-saas p-6 sticky top-24">
                <!-- Profile Header -->
                <div class="text-center mb-6 pb-6 border-b border-siakad-light">
                    <div class="w-16 h-16 rounded-xl bg-siakad-primary flex items-center justify-center text-white text-2xl font-bold mx-auto mb-3">
                        {{ strtoupper(substr($krs->mahasiswa->user->name ?? 'X', 0, 1)) }}
                    </div>
                    <h3 class="text-lg font-semibold text-siakad-dark">{{ $krs->mahasiswa->user->name ?? '-' }}</h3>
                    <p class="text-sm text-siakad-secondary font-mono">{{ $krs->mahasiswa->nim ?? '-' }}</p>
                </div>

                <!-- Info Grid -->
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-siakad-secondary">Prodi</span>
                        <span class="font-medium text-siakad-dark text-right">{{ $krs->mahasiswa->prodi->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-siakad-secondary">Angkatan</span>
                        <span class="font-medium text-siakad-dark">{{ $krs->mahasiswa->angkatan ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-siakad-secondary">Tahun Akademik</span>
                        <span class="font-medium text-siakad-dark">{{ $krs->tahunAkademik->tahun ?? '-' }} {{ $krs->tahunAkademik->semester ?? '' }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-siakad-light">
                        <span class="text-siakad-secondary">Total SKS</span>
                        <span class="text-xl font-bold text-siakad-primary">{{ $totalSks }}</span>
                    </div>
                </div>

                <!-- Status & Actions -->
                <div class="mt-6 pt-6 border-t border-siakad-light">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm text-siakad-secondary">Status KRS</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold capitalize
                            {{ $krs->status == 'approved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 
                               ($krs->status == 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' : 
                               ($krs->status == 'rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' : 'bg-siakad-light text-siakad-secondary dark:bg-gray-700 dark:text-gray-300')) }}">
                            {{ $krs->status == 'approved' ? 'Disetujui' : ($krs->status == 'pending' ? 'Menunggu' : ($krs->status == 'rejected' ? 'Ditolak' : $krs->status)) }}
                        </span>
                    </div>

                    @if($krs->status === 'pending')
                    <div class="space-y-2">
                        <form action="{{ route('dosen.bimbingan.krs-approve', $krs->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-siakad-primary text-white rounded-lg text-sm font-medium hover:bg-siakad-primary/90 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Setujui KRS
                            </button>
                        </form>
                        <button type="button" onclick="showRejectModal()" class="w-full py-2.5 border border-red-300 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Tolak KRS
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Course List -->
        <div class="lg:col-span-2">
            <div class="card-saas overflow-hidden">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Mata Kuliah Diambil</h3>
                    <p class="text-sm text-siakad-secondary">{{ $krs->krsDetail->count() }} mata kuliah</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full table-saas">
                        <thead>
                            <tr class="bg-siakad-light/30 dark:bg-gray-900">
                                <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase w-12">No</th>
                                <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Mata Kuliah</th>
                                <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Dosen Pengampu</th>
                                <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase w-20">SKS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($krs->krsDetail as $index => $detail)
                            <tr class="border-b border-siakad-light/50">
                                <td class="py-4 px-5 text-sm text-siakad-secondary">{{ $index + 1 }}</td>
                                <td class="py-4 px-5">
                                    <p class="font-medium text-siakad-dark">{{ $detail->kelas->mataKuliah->nama_mk ?? '-' }}</p>
                                    <p class="text-xs text-siakad-secondary">{{ $detail->kelas->mataKuliah->kode_mk ?? '-' }} • Kelas {{ $detail->kelas->nama_kelas ?? '-' }}</p>
                                </td>
                                <td class="py-4 px-5 text-sm text-siakad-secondary">{{ $detail->kelas->dosen->user->name ?? '-' }}</td>
                                <td class="py-4 px-5 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-siakad-primary/10 text-siakad-primary text-sm font-semibold">
                                        {{ $detail->kelas->mataKuliah->sks ?? 0 }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-siakad-light/30 dark:bg-gray-900">
                                <td colspan="3" class="py-3 px-5 text-sm font-semibold text-siakad-dark text-right">Total SKS</td>
                                <td class="py-3 px-5 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-siakad-primary text-white text-sm font-bold">
                                        {{ $totalSks }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-xl">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Tolak KRS</h3>
            </div>
            <form action="{{ route('dosen.bimbingan.krs-reject', $krs->id) }}" method="POST">
                @csrf
                <div class="p-6">
                    <label class="block text-sm font-medium text-siakad-dark dark:text-white mb-2">Alasan Penolakan</label>
                    <textarea name="catatan" rows="4" class="input-saas w-full resize-none bg-white dark:bg-gray-900" placeholder="Masukkan alasan mengapa KRS ditolak... (opsional)"></textarea>
                    <p class="text-xs text-siakad-secondary mt-2">Catatan ini akan dilihat oleh mahasiswa sebagai alasan penolakan.</p>
                </div>
                <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="hideRejectModal()" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm dark:text-gray-300 dark:hover:text-white">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">Tolak KRS</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function hideRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
