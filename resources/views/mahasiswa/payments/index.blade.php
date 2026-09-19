<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">Pembayaran</span>
        <span class="hidden md:inline">Informasi Pembayaran & SPP</span>
    </x-slot>

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-siakad-dark dark:text-white hidden md:block">
                    Informasi Pembayaran & SPP Mahasiswa
                </h1>
                <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-1">
                    Monitoring kewajiban biaya perkuliahan dan status pembukaan akses KRS STIT Mambaul Hikmah
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ url('mahasiswa/krs') }}" class="btn-primary-saas px-4 py-2 text-xs font-semibold rounded-lg flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Menuju Pengisian KRS
                </a>
            </div>
        </div>

        <!-- Status KRS & Semester Aktif Banner -->
        @if(!$krsAccess['allowed'])
            <div class="rounded-xl p-5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-200">
                <div class="flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex-shrink-0 flex items-center justify-center text-amber-600 dark:text-amber-400 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-amber-950 dark:text-amber-100">KRS Semester {{ $activeSemester }} Belum Dibuka</h4>
                        <p class="text-xs mt-1 leading-relaxed text-amber-800 dark:text-amber-300">
                            {{ $krsAccess['reason'] ?? "Anda belum menyelesaikan pembayaran Semester {$activeSemester}." }} 
                            Segera lakukan pembayaran dan hubungi Administrasi Keuangan STIT Mambaul Hikmah agar akses pengisian KRS Anda diaktifkan.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-xl p-4 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 text-emerald-900 dark:text-emerald-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 flex-shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs text-emerald-950 dark:text-emerald-100">Akses KRS Semester {{ $activeSemester }} Aktif</h4>
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-300">Kewajiban pembayaran semester berjalan telah lunas terverifikasi.</p>
                    </div>
                </div>
                <a href="{{ url('mahasiswa/krs') }}" class="btn-primary-saas px-3.5 py-1.5 text-xs font-semibold rounded-lg text-center flex-shrink-0">
                    Isi KRS Sekarang &rarr;
                </a>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="card-saas p-5 bg-white dark:bg-gray-800">
                <p class="text-xs font-medium text-siakad-secondary dark:text-gray-400">Total Kewajiban Biaya</p>
                <p class="text-xl font-bold text-siakad-dark dark:text-white mt-1">Rp {{ number_format($totalKewajiban, 0, ',', '.') }}</p>
                <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-2">Pendaftaran & Semester 1 s/d 8</p>
            </div>

            <div class="card-saas p-5 bg-white dark:bg-gray-800">
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Total Sudah Dibayar</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <div class="flex-1 bg-siakad-light/40 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                        @php
                            $pct = $totalKewajiban > 0 ? min(100, round(($totalDibayar / $totalKewajiban) * 100)) : 0;
                        @endphp
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-600">{{ $pct }}%</span>
                </div>
            </div>

            <div class="card-saas p-5 bg-white dark:bg-gray-800">
                <p class="text-xs font-medium text-amber-600 dark:text-amber-400">Sisa Tunggakan</p>
                <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</p>
                <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-2">Kewajiban semester berjalan & mendatang</p>
            </div>
        </div>

        <!-- Timeline Pembayaran -->
        <div class="card-saas overflow-hidden dark:bg-gray-800">
            <div class="p-5 border-b border-siakad-light dark:border-gray-700 bg-white dark:bg-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-siakad-light/40 dark:bg-gray-700 flex items-center justify-center text-siakad-primary dark:text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="font-bold text-sm text-siakad-dark dark:text-white">
                        Timeline & Daftar Kewajiban Pembayaran
                    </h3>
                </div>
                <span class="text-xs text-siakad-secondary dark:text-gray-400">
                    Semester Berjalan Anda: <strong class="text-siakad-dark dark:text-white">Semester {{ $activeSemester }}</strong>
                </span>
            </div>

            <div class="p-5 space-y-3.5">
                @foreach($payments as $p)
                @php
                    $isCurrent = ($p->paymentType->semester === $activeSemester);
                    $isPaid = $p->isPaid();
                @endphp
                <div class="p-4 rounded-xl border transition {{ $isPaid ? 'bg-emerald-50/20 border-emerald-200/70 dark:border-emerald-900/40' : ($isCurrent ? 'bg-amber-50/30 border-amber-200 dark:border-amber-800/60 ring-1 ring-amber-400/30' : 'bg-white dark:bg-gray-800/60 border-siakad-light dark:border-gray-700') }}">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-sm font-bold
                                {{ $isPaid ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : ($isCurrent ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-siakad-light/40 text-siakad-secondary dark:bg-gray-700 dark:text-gray-400') }}">
                                @if($isPaid)
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                @elseif($isCurrent)
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-xs font-bold text-siakad-dark dark:text-white">{{ $p->paymentType->name }}</h4>
                                    @if($isCurrent)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white">Semester Berjalan</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-siakad-secondary dark:text-gray-400 font-mono mt-0.5">{{ $p->invoice_number }}</p>
                                <div class="flex items-center gap-3 text-[11px] text-siakad-secondary dark:text-gray-400 mt-1 flex-wrap">
                                    <span>Tagihan: <strong class="text-siakad-dark dark:text-white">Rp {{ number_format($p->amount, 0, ',', '.') }}</strong></span>
                                    @if($isPaid)
                                        <span>&bull; Terbayar: <strong class="text-emerald-600">Rp {{ number_format($p->paid_amount, 0, ',', '.') }}</strong></span>
                                        <span>&bull; Tgl: {{ $p->payment_date ? $p->payment_date->format('d/m/Y') : ($p->confirmed_at ? $p->confirmed_at->format('d/m/Y') : '-') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-siakad-light dark:border-gray-700">
                            <div>
                                @if($isPaid)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                        ✓ LUNAS
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                                        BELUM LUNAS
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('mahasiswa.payments.show', $p->id) }}" class="btn-ghost-saas px-3 py-1.5 text-xs font-semibold rounded-lg">
                                    Rincian
                                </a>
                                @if($isPaid)
                                    <a href="{{ route('mahasiswa.payments.receipt', $p->id) }}" target="_blank" class="btn-primary-saas px-3 py-1.5 text-xs font-semibold rounded-lg flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Kwitansi
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Prosedur & Rekening Pembayaran -->
        <div class="card-saas p-6 dark:bg-gray-800 bg-white">
            <h4 class="font-bold text-siakad-dark dark:text-white text-sm mb-4 flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-siakad-light/40 dark:bg-gray-700 flex items-center justify-center text-siakad-primary dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                Prosedur & Rekening Pembayaran STIT Mambaul Hikmah
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs text-siakad-secondary dark:text-gray-300">
                <div class="space-y-2">
                    <p><strong>1. Pembayaran Tunai:</strong> Datang langsung ke loket administrasi dan keuangan kampus STIT Mambaul Hikmah pada jam layanan operasional.</p>
                    <p><strong>2. Pembayaran Transfer:</strong> Transfer ke rekening resmi kampus:</p>
                    <div class="p-3.5 bg-siakad-light/20 dark:bg-gray-900/50 rounded-xl border border-siakad-light dark:border-gray-700 font-mono">
                        <p class="font-bold text-siakad-dark dark:text-white">Bank Syariah Indonesia (BSI)</p>
                        <p class="text-sm font-bold text-siakad-primary dark:text-blue-400">No. Rek: 712-3456-789</p>
                        <p class="text-[11px] text-siakad-secondary dark:text-gray-400">a.n. STIT MAMBAUL HIKMAH TEGAL</p>
                    </div>
                </div>
                <div class="space-y-2">
                    <p><strong>3. Konfirmasi Bukti:</strong> Setelah transfer berhasil, serahkan struk/bukti transfer ke bagian keuangan atau kirimkan via WhatsApp Administrasi Fakultas untuk diverifikasi menjadi status <strong>LUNAS</strong>.</p>
                    <p><strong>4. Aktivasi Otomatis KRS:</strong> Begitu status pembayaran dinyatakan lunas oleh staf keuangan, sistem SIAKAD secara otomatis langsung membuka portal pemilihan Kartu Rencana Studi (KRS) untuk semester Anda.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
