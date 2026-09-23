<x-app-layout>
    <x-slot name="header">
        Dashboard Pembayaran Mahasiswa
    </x-slot>

    <!-- Page Title & Actions -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-siakad-dark dark:text-white hidden md:block">Dashboard Pembayaran Mahasiswa</h1>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-1">Monitoring keuangan, rekap tunggakan, dan pelunasan SPP/Akademik STIT Mambaul Hikmah</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.payments.index') }}" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                Daftar Pembayaran
            </a>
            <a href="{{ route('admin.payments.export') }}" target="_blank" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2 bg-white dark:bg-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Laporan
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-saas p-4 mb-6 dark:bg-gray-800">
        <form method="GET" action="{{ route('admin.payments.dashboard') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @if($user->isSuperAdmin() && $fakultasList->count() > 0)
            <div>
                <label class="block text-xs font-medium text-siakad-secondary dark:text-gray-400 mb-1">Fakultas</label>
                <select name="fakultas_id" onchange="this.form.submit()" class="input-saas w-full px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                    <option value="">Semua Fakultas</option>
                    @foreach($fakultasList as $f)
                        <option value="{{ $f->id }}" {{ request('fakultas_id') == $f->id ? 'selected' : '' }}>{{ $f->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-siakad-secondary dark:text-gray-400 mb-1">Program Studi</label>
                <select name="prodi_id" onchange="this.form.submit()" class="input-saas w-full px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                    <option value="">Semua Prodi</option>
                    @foreach($prodiList as $p)
                        <option value="{{ $p->id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-siakad-secondary dark:text-gray-400 mb-1">Angkatan</label>
                <select name="angkatan" onchange="this.form.submit()" class="input-saas w-full px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                    <option value="">Semua Angkatan</option>
                    @foreach($angkatanList as $akt)
                        <option value="{{ $akt }}" {{ request('angkatan') == $akt ? 'selected' : '' }}>{{ $akt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-siakad-secondary dark:text-gray-400 mb-1">Jenis Pembayaran</label>
                <select name="payment_type_id" onchange="this.form.submit()" class="input-saas w-full px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                    <option value="">Semua Jenis</option>
                    @foreach($paymentTypes as $pt)
                        <option value="{{ $pt->id }}" {{ request('payment_type_id') == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary-saas flex-1 px-4 py-2 rounded-lg text-sm font-medium">
                    Filter
                </button>
                <a href="{{ route('admin.payments.dashboard') }}" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Mahasiswa -->
        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-siakad-secondary dark:text-gray-400">Total Mahasiswa</p>
                    <p class="text-2xl font-bold text-siakad-dark dark:text-white mt-1">{{ number_format($stats['total_mahasiswa']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-siakad-primary/10 dark:bg-blue-500/20 text-siakad-primary dark:text-blue-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>
            <p class="text-[11px] text-siakad-secondary/70 dark:text-gray-500 mt-3">Data mahasiswa terdaftar</p>
        </div>

        <!-- Total Sudah Lunas -->
        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Total Sudah Lunas</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($stats['total_nominal_lunas'], 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="flex items-center justify-between mt-3 text-[11px] text-siakad-secondary dark:text-gray-400">
                <span>{{ number_format($stats['total_lunas']) }} transaksi</span>
                @php
                    $pctLunas = $stats['total_tagihan'] > 0 ? round(($stats['total_lunas'] / $stats['total_tagihan']) * 100, 1) : 0;
                @endphp
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $pctLunas }}% lunas</span>
            </div>
        </div>

        <!-- Total Tunggakan -->
        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-amber-600 dark:text-amber-400">Total Tunggakan</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">Rp {{ number_format($stats['total_tunggakan'], 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-3">{{ number_format($stats['total_belum_lunas']) }} tagihan belum lunas</p>
        </div>

        <!-- Pembayaran Bulan Ini -->
        <div class="card-saas p-5 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-siakad-secondary dark:text-gray-400">Pembayaran Bulan Ini</p>
                    <p class="text-xl font-bold text-siakad-dark dark:text-white mt-1">Rp {{ number_format($stats['pembayaran_bulan_ini'], 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-siakad-secondary/10 dark:bg-gray-700/50 text-siakad-secondary dark:text-gray-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-siakad-secondary dark:text-gray-400 flex justify-between border-t border-siakad-light/50 dark:border-gray-700 pt-2">
                <span>Hari ini:</span>
                <span class="font-semibold text-siakad-dark dark:text-white">Rp {{ number_format($stats['pembayaran_hari_ini'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- CHARTS SECTION: 2 Kolom Chart Utama                           -->
    <!-- ============================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8 items-start">

        <!-- --------------------------------------------------------- -->
        <!-- CHART 1 (5 Kolom): Penyelesaian Pembayaran Semester Ini   -->
        <!-- --------------------------------------------------------- -->
        <div class="lg:col-span-5 card-saas p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700">
            <div class="flex items-start justify-between gap-3 mb-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="font-bold text-sm text-siakad-dark dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-siakad-primary"></span>
                        Penyelesaian Pembayaran Semester Ini
                    </h3>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-0.5">
                        Status seluruh mahasiswa &bull; {{ $semesterCompletion['active_semester_label'] }}
                    </p>
                </div>
            </div>

            <!-- Chart Doughnut (Style identik dengan Dashboard Utama SIAKAD) -->
            <div class="h-48 mb-4">
                <canvas id="semesterCompletionChart"></canvas>
            </div>


            <!-- Rincian Nominal Uang Semester Ini -->
            <div class="mt-4 p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700 text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Telah Terbayar Semester Ini:</span>
                    <span class="font-bold text-siakad-primary dark:text-blue-400 font-mono">
                        Rp {{ number_format($semesterCompletion['total_nominal_lunas'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Sisa Tunggakan Semester Ini:</span>
                    <span class="font-bold text-amber-600 dark:text-amber-400 font-mono">
                        Rp {{ number_format($semesterCompletion['total_nominal_tunggakan'], 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Progres Pelunasan Per Program Studi -->
            @if(count($semesterCompletion['prodi_breakdown']) > 0)
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-siakad-secondary dark:text-gray-400 mb-2">
                    Progres Pelunasan Per Prodi:
                </h4>
                <div class="space-y-2">
                    @foreach($semesterCompletion['prodi_breakdown'] as $prodiName => $pData)
                        @php
                            $pctProdi = $pData['total'] > 0 ? round(($pData['lunas'] / $pData['total']) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-medium text-siakad-dark dark:text-gray-200 truncate max-w-[200px]">{{ $prodiName }}</span>
                                <span class="font-mono text-siakad-secondary dark:text-gray-400 text-[11px]">
                                    {{ $pData['lunas'] }}/{{ $pData['total'] }} mhs ({{ $pctProdi }}%)
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-siakad-primary h-1.5 rounded-full transition-all duration-300" style="width: {{ $pctProdi }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- --------------------------------------------------------- -->
        <!-- CHART 2 (7 Kolom): Tren Mingguan (Online vs Offline)      -->
        <!-- --------------------------------------------------------- -->
        <div class="lg:col-span-7 card-saas p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-siakad-light/70 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="font-bold text-sm text-siakad-dark dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-siakad-secondary"></span>
                        Tren Pembayaran Mingguan
                    </h3>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-0.5">
                        Frekuensi transaksi via Online &amp; Offline per minggu
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-siakad-primary/10 text-siakad-primary dark:bg-blue-900/40 dark:text-blue-300 border border-siakad-primary/20">
                        <span class="w-2 h-2 rounded-full bg-siakad-primary"></span>
                        Online
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-[#86c5e0]/20 text-siakad-secondary dark:bg-[#86c5e0]/10 dark:text-[#86c5e0] border border-[#86c5e0]/30">
                        <span class="w-2 h-2 rounded-full bg-[#86c5e0]"></span>
                        Offline
                    </span>
                </div>
            </div>

            <!-- Chart Bar Mingguan -->
            <div class="h-64 w-full mb-4">
                <canvas id="weeklyPaymentChart"></canvas>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const siakadPrimary = '#234C6A';
        const siakadSecondary = '#456882';
        const siakadDark = '#1B3C53';

        // =========================================================
        // 1. Chart Penyelesaian Pembayaran Semester Ini (Doughnut)
        // =========================================================
        const completionCtx = document.getElementById('semesterCompletionChart');
        if (completionCtx) {
            const lunas = {{ $semesterCompletion['lunas_count'] }};
            const partial = {{ $semesterCompletion['partial_count'] }};
            const unpaid = {{ $semesterCompletion['unpaid_count'] }};
            const total = {{ $semesterCompletion['total_mahasiswa'] }};

            new Chart(completionCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Lunas', 'Cicilan / Sebagian', 'Belum Bayar'],
                    datasets: [{
                        data: total > 0 ? [lunas, partial, unpaid] : [0, 0, 1],
                        backgroundColor: total > 0 ? [
                            siakadPrimary,   // '#234C6A'
                            '#86c5e0',       // Light Oceanic Blue
                            '#E3E3E3',       // Light Gray
                        ] : ['#E3E3E3', '#E3E3E3', '#E3E3E3'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { boxWidth: 12, padding: 12, font: { size: 11 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const count = context.raw || 0;
                                    const pct = total > 0 ? Math.round((count / total) * 100) : 0;
                                    return ` ${context.label}: ${count} mhs (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // =========================================================
        // 2. Chart Tren Mingguan Pembayaran (Online vs Offline Bar)
        // =========================================================
        const weeklyCtx = document.getElementById('weeklyPaymentChart');
        if (weeklyCtx) {
            const weeklyWeeks = @json($weeklyPayments['weeks']);
            const labels = weeklyWeeks.map(w => w.week_short + ' (' + w.date_range + ')');
            const onlineData = weeklyWeeks.map(w => w.online_count);
            const offlineData = weeklyWeeks.map(w => w.offline_count);
            const onlineAmounts = weeklyWeeks.map(w => w.online_amount);
            const offlineAmounts = weeklyWeeks.map(w => w.offline_amount);

            new Chart(weeklyCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Online (Midtrans)',
                            data: onlineData,
                            backgroundColor: siakadPrimary,
                            borderRadius: 6,
                            borderWidth: 0,
                            maxBarThickness: 24,
                        },
                        {
                            label: 'Offline (Tunai / Kasir)',
                            data: offlineData,
                            backgroundColor: '#86c5e0',
                            borderRadius: 6,
                            borderWidth: 0,
                            maxBarThickness: 24,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 } }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: { size: 10 }
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Transaksi',
                                font: { size: 11 }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: { boxWidth: 12, padding: 12, font: { size: 11 } }
                        },
                        tooltip: {
                            callbacks: {
                                footer: function(tooltipItems) {
                                    const idx = tooltipItems[0].dataIndex;
                                    const onAmt = new Intl.NumberFormat('id-ID').format(onlineAmounts[idx] || 0);
                                    const offAmt = new Intl.NumberFormat('id-ID').format(offlineAmounts[idx] || 0);
                                    return `Nominal Online: Rp ${onAmt}\nNominal Offline: Rp ${offAmt}`;
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
