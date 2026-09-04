<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <!-- Greeting -->
    <div class="mb-8 hidden md:block">
        <h1 class="text-2xl font-semibold text-siakad-dark">
            {{ $greeting }}, {{ explode(' ', $user->name)[0] }}! 
            @php
                $hour = now()->hour;
                if ($hour < 11) { $emoji = '🌅'; }
                elseif ($hour < 15) { $emoji = '☀️'; }
                elseif ($hour < 18) { $emoji = '🌤️'; }
                else { $emoji = '🌙'; }
            @endphp
            {{ $emoji }}
        </h1>
        <p class="text-siakad-secondary text-sm mt-1">Semoga harimu menyenangkan!</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Profile & IPK Card -->
        <div class="card-saas p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-xl bg-siakad-primary flex items-center justify-center text-white text-xl font-semibold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-semibold text-siakad-dark">{{ $user->name }}</h3>
                    <p class="text-sm text-siakad-secondary font-mono">{{ $mahasiswa->nim }}</p>
                    <p class="text-xs text-siakad-secondary/70">{{ $mahasiswa->prodi->nama ?? '-' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <!-- IPK Card -->
                <div class="bg-siakad-primary rounded-xl p-4 text-white">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[11px] font-medium opacity-80 uppercase tracking-wide">IPK</span>
                    </div>
                    <p class="text-2xl font-bold">{{ number_format($ipkData['ips'], 2) }}</p>
                    <p class="text-[10px] opacity-70 mt-1">Indeks Kumulatif</p>
                </div>

                <!-- IPS Card -->
                <div class="bg-siakad-dark rounded-xl p-4 text-white">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[11px] font-medium opacity-80 uppercase tracking-wide">IP Semester</span>
                    </div>
                    <p class="text-2xl font-bold">{{ $currentIps ? number_format($currentIps['ips'], 2) : '-' }}</p>
                    <p class="text-[10px] opacity-70 mt-1">Semester Lalu</p>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-siakad-light/50">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-siakad-secondary">Total SKS Lulus</span>
                    <span class="font-semibold text-siakad-dark">{{ $ipkData['total_sks'] }} SKS</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-2">
                    <span class="text-siakad-secondary">Maks SKS Semester Depan</span>
                    <span class="font-semibold text-siakad-primary">{{ $maxSks }} SKS</span>
                </div>
            </div>
        </div>

        <!-- Quick Action Cards -->
        <div class="lg:col-span-2 grid grid-cols-2 gap-4">
            <!-- Pengisian KRS -->
            <a href="{{ route('mahasiswa.krs.index') }}" class="relative card-saas p-5 hover:border-siakad-primary/30 group">
                @if($currentKrs && $currentKrs->status === 'draft')
                <span class="absolute top-4 right-4 px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-semibold rounded-full">Draft</span>
                @elseif(!$currentKrs)
                <span class="absolute top-4 right-4 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-semibold rounded-full">Baru</span>
                @endif
                <div class="w-11 h-11 bg-siakad-primary/10 rounded-xl flex items-center justify-center mb-4 group-hover:bg-siakad-primary/20 transition">
                    <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                <h3 class="font-semibold text-siakad-dark mb-1">Pengisian KRS</h3>
                <p class="text-sm text-siakad-secondary">{{ $activeTA?->tahun ?? '-' }} {{ $activeTA?->semester ?? '' }}</p>
            </a>

            <!-- Perkuliahan -->
            <a href="{{ route('mahasiswa.jadwal.index') }}" class="card-saas p-5 hover:border-siakad-primary/30 group">
                <div class="w-11 h-11 bg-siakad-secondary/10 rounded-xl flex items-center justify-center mb-4 group-hover:bg-siakad-secondary/20 transition">
                    <svg class="w-5 h-5 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="font-semibold text-siakad-dark mb-1">Perkuliahan</h3>
                <p class="text-sm text-siakad-secondary">Jadwal & Kelas</p>
            </a>

            <!-- Riwayat Kuliah -->
            <a href="{{ route('mahasiswa.transkrip.index') }}" class="card-saas p-5 hover:border-siakad-primary/30 group">
                <div class="w-11 h-11 bg-siakad-primary/10 rounded-xl flex items-center justify-center mb-4 group-hover:bg-siakad-primary/20 transition">
                    <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <h3 class="font-semibold text-siakad-dark mb-1">Riwayat Kuliah</h3>
                <p class="text-sm text-siakad-secondary">Transkrip Nilai</p>
            </a>

            <!-- Biodata -->
            <a href="{{ route('mahasiswa.biodata.index') }}" class="card-saas p-5 hover:border-siakad-primary/30 group">
                <div class="w-11 h-11 bg-siakad-dark/10 rounded-xl flex items-center justify-center mb-4 group-hover:bg-siakad-dark/20 transition">
                    <svg class="w-5 h-5 text-siakad-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <h3 class="font-semibold text-siakad-dark mb-1">Biodata</h3>
                <p class="text-sm text-siakad-secondary">Data Diri</p>
            </a>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- SKS per Semester Chart -->
        <div class="card-saas p-6">
            <h3 class="font-semibold text-siakad-dark mb-4">Grafik Jumlah SKS per Semester</h3>
            <div class="h-48">
                <canvas id="sksChart"></canvas>
            </div>
        </div>

        <!-- IPS Progression Chart -->
        <div class="card-saas p-6">
            <h3 class="font-semibold text-siakad-dark mb-4">Grafik Perkembangan Studi per Semester - IP</h3>
            <div class="h-48">
                <canvas id="ipsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Dosen PA Info -->
    @if($mahasiswa->dosenPa)
    <div class="mt-6 card-saas p-6">
        <h3 class="font-semibold text-siakad-dark mb-4">Dosen Pembimbing Akademik</h3>
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-siakad-secondary flex items-center justify-center text-white font-semibold">
                {{ strtoupper(substr($mahasiswa->dosenPa->user->name ?? 'D', 0, 1)) }}
            </div>
            <div>
                <p class="font-medium text-siakad-dark">{{ $mahasiswa->dosenPa->user->name ?? '-' }}</p>
                <p class="text-sm text-siakad-secondary">{{ $mahasiswa->dosenPa->nidn ?? '-' }}</p>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // SIAKAD Colors
        const siakadPrimary = '#234C6A';
        const siakadSecondary = '#456882';
        
        // Detect dark mode
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? '#334155' : '#E3E3E3';
        const textColor = isDark ? '#94A3B8' : '#456882';

        // SKS Chart
        const sksData = @json($sksHistory);
        const sksCtx = document.getElementById('sksChart').getContext('2d');
        new Chart(sksCtx, {
            type: 'bar',
            data: {
                labels: sksData.map(d => d.semester.substring(0, 9)),
                datasets: [{
                    label: 'SKS',
                    data: sksData.map(d => d.sks),
                    backgroundColor: siakadPrimary,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 24, grid: { color: gridColor }, ticks: { color: textColor } },
                    x: { grid: { display: false }, ticks: { color: textColor } }
                }
            }
        });

        // IPS Chart
        const ipsData = @json($ipsHistory);
        const ipsCtx = document.getElementById('ipsChart').getContext('2d');
        new Chart(ipsCtx, {
            type: 'line',
            data: {
                labels: ipsData.map(d => d.tahun_akademik.substring(0, 9)),
                datasets: [{
                    label: 'IPS',
                    data: ipsData.map(d => d.ips),
                    borderColor: isDark ? '#60A5FA' : siakadPrimary,
                    backgroundColor: isDark ? 'rgba(96, 165, 250, 0.15)' : 'rgba(35, 76, 106, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: isDark ? '#60A5FA' : siakadPrimary,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { min: 0, max: 4, ticks: { stepSize: 0.5, color: textColor }, grid: { color: gridColor } },
                    x: { grid: { display: false }, ticks: { color: textColor } }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
