<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">KRS</span>
        <span class="hidden md:inline">Kartu Rencana Studi (KRS)</span>
    </x-slot>

    @if(!empty($isLocked) && $isLocked)
    <!-- Locked State UI -->
    <div class="py-8 max-w-3xl mx-auto" x-data="{ openContact: false }">
        <div class="card-saas p-8 text-center space-y-6 bg-white dark:bg-gray-800">
            <!-- Icon Lock -->
            <div class="w-20 h-20 mx-auto rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-400 shadow-sm">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>

            <!-- Header Text -->
            <div class="space-y-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">
                    Akses Akademik Terkunci
                </span>
                <h2 class="text-2xl font-bold text-siakad-dark dark:text-white">KRS Belum Dapat Diakses</h2>
                <p class="text-sm text-siakad-secondary dark:text-gray-400 max-w-lg mx-auto leading-relaxed">
                    Pengisian KRS Semester {{ $targetSemester ?? 1 }} belum dapat dilakukan karena pembayaran perkuliahan belum dikonfirmasi lunas oleh Bagian Keuangan STIT Mambaul Hikmah.
                </p>
            </div>

            <!-- Payment Details Card -->
            <div class="card-saas p-5 bg-siakad-light/20 dark:bg-gray-900/50 border border-siakad-light dark:border-gray-700 text-left max-w-md mx-auto space-y-3 text-xs">
                <div class="flex justify-between items-center border-b border-siakad-light dark:border-gray-700 pb-2">
                    <span class="text-siakad-secondary dark:text-gray-400">Jenis Pembayaran:</span>
                    <span class="font-bold text-siakad-dark dark:text-white">{{ $unpaidPayment?->paymentType?->name ?? 'Pembayaran Perkuliahan' }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-siakad-light dark:border-gray-700 pb-2">
                    <span class="text-siakad-secondary dark:text-gray-400">Semester Target:</span>
                    <span class="font-bold text-siakad-dark dark:text-white">Semester {{ $targetSemester ?? 1 }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-siakad-light dark:border-gray-700 pb-2">
                    <span class="text-siakad-secondary dark:text-gray-400">Nominal Tagihan:</span>
                    <span class="font-extrabold text-sm text-siakad-dark dark:text-white">
                        Rp {{ number_format($unpaidPayment?->amount ?? 1500000, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-siakad-secondary dark:text-gray-400">Status Pembayaran:</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                        {{ strtoupper($unpaidPayment?->status ?? 'UNPAID') }}
                    </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a href="{{ route('mahasiswa.payments.index') }}" class="btn-primary-saas w-full sm:w-auto px-6 py-2.5 rounded-lg text-xs font-semibold shadow-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Lihat Rincian Pembayaran
                </a>
                <button type="button" @click="openContact = true" class="btn-ghost-saas w-full sm:w-auto px-6 py-2.5 rounded-lg text-xs font-semibold flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    Hubungi Keuangan Kampus
                </button>
            </div>
        </div>

        <!-- Modal Kontak Administrasi -->
        <div x-show="openContact" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div x-show="openContact" @click="openContact = false" class="fixed inset-0 bg-siakad-dark/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                <div x-show="openContact" class="card-saas inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-siakad-light dark:border-gray-700 pb-3">
                        <h3 class="text-sm font-bold text-siakad-dark dark:text-white">Kontak Layanan Keuangan & SPP</h3>
                        <button type="button" @click="openContact = false" class="text-siakad-secondary hover:text-siakad-dark dark:hover:text-white">✕</button>
                    </div>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">
                        Silakan hubungi bagian keuangan kampus atau staf administrasi fakultas untuk melakukan konfirmasi pembayaran Anda:
                    </p>
                    <div class="space-y-2.5 text-xs">
                        <div class="p-3.5 bg-siakad-light/20 dark:bg-gray-900/50 rounded-xl border border-siakad-light dark:border-gray-700">
                            <p class="font-bold text-siakad-dark dark:text-white">Loket Administrasi Keuangan Kampus</p>
                            <p class="text-siakad-secondary dark:text-gray-400 mt-1">Gedung Rektorat Lt. 1, STIT Mambaul Hikmah Tegal</p>
                            <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-1">Jam Layanan: Senin - Sabtu, 08:00 - 15:00 WIB</p>
                        </div>
                        <div class="p-3.5 bg-emerald-50/60 dark:bg-emerald-950/20 rounded-xl border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-300">
                            <p class="font-bold">WhatsApp Layanan Pembayaran & SPP:</p>
                            <p class="font-mono font-bold mt-1 text-sm text-emerald-700 dark:text-emerald-400">+62 812-3456-7890</p>
                        </div>
                    </div>
                    <div class="pt-2 flex justify-end">
                        <button type="button" @click="openContact = false" class="btn-ghost-saas px-4 py-2 text-xs font-semibold rounded-lg">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Status Banner -->
    <div class="mb-8">
        <div class="bg-siakad-primary rounded-xl p-6 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <p class="text-[10px] md:text-xs opacity-70 uppercase tracking-wider">Tahun Akademik Aktif</p>
                    <h3 class="text-xl font-bold mt-1">{{ \App\Models\TahunAkademik::where('is_active', true)->first()?->tahun ?? '-' }} - {{ \App\Models\TahunAkademik::where('is_active', true)->first()?->semester ?? '-' }}</h3>
                </div>
                <div class="flex items-center justify-between md:justify-end gap-6 border-t border-white/10 pt-4 md:border-0 md:pt-0">
                    <div class="text-center">
                        <p class="text-2xl font-bold">{{ $krs->krsDetail->sum(fn($d) => $d->kelas->mataKuliah->sks) }}</p>
                        <p class="text-xs opacity-70">Total SKS</p>
                    </div>
                    <div class="text-center">
                        @php
                            $statusColors = [
                                'approved' => 'bg-emerald-500/30 text-emerald-100',
                                'rejected' => 'bg-red-500/30 text-red-100',
                                'pending' => 'bg-amber-500/30 text-amber-100',
                                'draft' => 'bg-white/20 text-white',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $statusColors[$krs->status] ?? 'bg-white/20' }}">
                            {{ ucfirst($krs->status) }}
                        </span>
                    </div>
                </div>
            </div>
            
            @if($krs->status == 'draft')
            <div class="mt-5 pt-5 border-t border-white/20">
                <form action="{{ url('mahasiswa/krs/submit') }}" method="POST" class="flex items-center justify-between">
                    @csrf
                    <p class="text-sm opacity-80">Setelah diajukan, KRS tidak dapat diubah lagi.</p>
                    <button type="submit" onclick="return confirm('Yakin ingin mengajukan KRS? Anda tidak dapat mengubah lagi setelah ini.')"
                        class="px-5 py-2 bg-white text-siakad-primary rounded-lg font-semibold text-sm hover:bg-siakad-light transition">
                        Ajukan KRS
                    </button>
                </form>
            </div>
            @elseif($krs->status == 'rejected')
            <div class="mt-5 pt-5 border-t border-white/20">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-red-200 mb-1">❌ KRS Ditolak</p>
                        <p class="text-sm opacity-80">{{ $krs->catatan ?? 'Silakan revisi KRS Anda dan ajukan kembali.' }}</p>
                    </div>
                    <form action="{{ url('mahasiswa/krs/revise') }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Ubah status KRS menjadi draft untuk direvisi?')"
                            class="px-5 py-2 bg-white text-siakad-primary rounded-lg font-semibold text-sm hover:bg-siakad-light transition whitespace-nowrap">
                            Revisi KRS
                        </button>
                    </form>
                </div>
            </div>
            @elseif($krs->status == 'pending')
            <div class="mt-5 pt-5 border-t border-white/20">
                <p class="text-sm opacity-80">⏳ KRS Anda sedang menunggu persetujuan dari Dosen PA.</p>
            </div>
            @elseif($krs->status == 'approved')
            <div class="mt-5 pt-5 border-t border-white/20">
                <p class="text-sm opacity-80">✅ KRS Anda telah disetujui oleh Dosen PA.</p>
            </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Taken Classes -->
        <div class="{{ $krs->status == 'draft' ? 'lg:col-span-2' : 'lg:col-span-3' }}">
            <div class="card-saas overflow-hidden">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Mata Kuliah Diambil</h3>
                    <p class="text-xs text-siakad-secondary mt-1">{{ $krs->krsDetail->count() }} mata kuliah dipilih</p>
                </div>
                
                <div class="divide-y divide-siakad-light/50">
                    @forelse($krs->krsDetail as $detail)
                    <div class="p-4 flex items-center gap-4 hover:bg-siakad-light/20 transition">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-siakad-dark truncate">{{ $detail->kelas->mataKuliah->nama_mk }}</p>
                            <p class="text-xs text-siakad-secondary">{{ $detail->kelas->mataKuliah->kode_mk }} • {{ $detail->kelas->dosen->user->name }}</p>
                        </div>
                        <div class="text-center px-3">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-siakad-primary/10 text-siakad-primary font-semibold text-sm">
                                {{ $detail->kelas->mataKuliah->sks }}
                            </span>
                            <p class="text-[10px] text-siakad-secondary mt-1">SKS</p>
                        </div>
                        @if($krs->status == 'draft')
                        <form action="{{ url('mahasiswa/krs/'.$detail->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-siakad-secondary hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                    @empty
                    <div class="py-12 text-center">
                        <div class="w-14 h-14 rounded-xl bg-siakad-light/50 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <p class="text-siakad-secondary font-medium">Belum ada mata kuliah diambil</p>
                        <p class="text-xs text-siakad-secondary/70">Pilih kelas di samping untuk memulai</p>
                    </div>
                    @endforelse
                </div>
                <!-- Total SKS Footer -->
                <div class="px-6 py-4 border-t border-siakad-light dark:border-slate-700 flex justify-between items-center" style="background-color: var(--bg-card);">
                    <span class="font-semibold text-siakad-dark">Total SKS Diambil</span>
                    <span class="font-bold text-siakad-primary text-lg">{{ $krs->krsDetail->sum(fn($d) => $d->kelas->mataKuliah->sks) }} SKS</span>
                </div>
            </div>
        </div>

        <!-- Available Classes -->
        @if($krs->status == 'draft')
        <div class="lg:col-span-1">
            <div class="card-saas overflow-hidden sticky top-24">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Kelas Tersedia</h3>
                    <p class="text-xs text-siakad-secondary mt-1">Pilih kelas untuk diambil</p>
                </div>
                
                <div class="max-h-[60vh] overflow-y-auto">
                    @forelse($availableKelas as $semester => $kelasList)
                    <div x-data="{ open: false }" class="border-b border-siakad-light/50 last:border-b-0">
                        <button @click="open = !open" class="w-full px-4 py-3 bg-siakad-light/30 dark:bg-slate-700/30 flex items-center justify-between hover:bg-siakad-light/50 dark:hover:bg-slate-700/50 transition cursor-pointer">
                            <div class="text-left">
                                <h4 class="font-semibold text-siakad-primary text-sm">{{ $semester }}</h4>
                                <p class="text-[10px] text-siakad-secondary">{{ $kelasList->count() }} kelas tersedia</p>
                            </div>
                            <svg class="w-4 h-4 text-siakad-secondary transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="divide-y divide-siakad-light/30">
                            @foreach($kelasList as $k)
                            <div class="p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-siakad-dark text-sm truncate">{{ $k->mataKuliah->nama_mk }}</p>
                                        <p class="text-[11px] text-siakad-secondary mt-0.5">{{ $k->mataKuliah->sks }} SKS • {{ $k->dosen->user->name ?? '-' }}</p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <div class="flex-1 h-1 bg-siakad-light rounded-full overflow-hidden">
                                                <div class="h-full bg-siakad-primary rounded-full" style="width: {{ min(100, ($k->krsDetail->count() / $k->kapasitas) * 100) }}%"></div>
                                            </div>
                                            <span class="text-[10px] text-siakad-secondary">{{ $k->krsDetail->count() }}/{{ $k->kapasitas }}</span>
                                        </div>
                                    </div>
                                </div>
                                <form action="{{ url('mahasiswa/krs') }}" method="POST" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="kelas_id" value="{{ $k->id }}">
                                    <button type="submit" class="w-full py-2 px-3 bg-siakad-primary/10 text-siakad-primary rounded-lg font-medium text-sm hover:bg-siakad-primary/20 transition">
                                        + Ambil Kelas
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-siakad-secondary text-sm">
                        Tidak ada kelas tersedia
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
</x-app-layout>
