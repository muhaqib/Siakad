<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Transkrip Nilai</span>
            <a href="{{ route('mahasiswa.export.transkrip') }}" target="_blank" class="hidden md:flex ml-4 px-4 py-2 bg-siakad-primary text-white text-sm font-semibold rounded-lg hover:bg-siakad-dark transition items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2-4h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6a2 2 0 012-2zm9-2V3a1 1 0 00-1-1H6a1 1 0 00-1 1v10m3-4h8a2 2 0 012 2v4H9v-4a2 2 0 012-2z"></path></svg>
                Cetak PDF
            </a>
        </div>
    </x-slot>

    <!-- Summary Cards -->
    @php
        $totalSks = $transcript['total_sks_lulus'] ?? 0;
        $ipk = $transcript['ipk'] ?? 0;
    @endphp

    <!-- Mobile Download Button -->
    <div class="mb-6 md:hidden">
        <a href="{{ route('mahasiswa.export.transkrip') }}" target="_blank" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-siakad-primary text-white font-semibold rounded-xl shadow-lg active:scale-95 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2-4h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6a2 2 0 012-2zm9-2V3a1 1 0 00-1-1H6a1 1 0 00-1 1v10m3-4h8a2 2 0 012 2v4H9v-4a2 2 0 012-2z"></path></svg>
            Unduh Transkrip PDF
        </a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-8">
        <div class="card-saas p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-siakad-primary rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark">{{ number_format($ipk, 2) }}</p>
                    <p class="text-xs text-siakad-secondary">IPK</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-siakad-secondary rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark">{{ $totalSks }}</p>
                    <p class="text-xs text-siakad-secondary">SKS Lulus</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-siakad-dark rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark">{{ $maxSks }}</p>
                    <p class="text-xs text-siakad-secondary">Maks SKS</p>
                </div>
            </div>
        </div>

        <div class="card-saas p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-siakad-dark">{{ count($transcript['semesters'] ?? []) }}</p>
                    <p class="text-xs text-siakad-secondary">Semester</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card-saas p-6">
            <h3 class="font-semibold text-siakad-dark mb-4">Perkembangan IPS</h3>
            <div class="h-48">
                <canvas id="ipsChart"></canvas>
            </div>
        </div>

        <div class="card-saas p-6">
            <h3 class="font-semibold text-siakad-dark mb-4">Distribusi Nilai</h3>
            <div class="h-48">
                <canvas id="gradeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Transcript by Semester -->
    @foreach($transcript['semesters'] ?? [] as $semester)
    <div x-data="{ open: false }" class="card-saas overflow-hidden mb-6">
        <div @click="open = !open" class="px-6 py-4 border-b border-siakad-light dark:border-slate-700 flex items-center justify-between cursor-pointer hover:bg-siakad-light/30 dark:hover:bg-slate-700/50 transition" style="background-color: var(--bg-card);">
            <div class="flex items-center gap-3">
                <div class="transition-transform duration-200" :class="{ 'rotate-180': open }">
                    <svg class="w-5 h-5 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
                <h3 class="font-semibold text-siakad-dark">{{ $semester['semester'] }}</h3>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-siakad-secondary">IPS: <span class="font-semibold text-siakad-primary">{{ number_format($semester['ips'], 2) }}</span></span>
                <span class="text-sm text-siakad-secondary">SKS: <span class="font-semibold text-siakad-dark">{{ $semester['total_sks'] }}</span></span>
            </div>
        </div>
        <div x-show="open" x-collapse style="display: none;" class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">Kode</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">Mata Kuliah</th>
                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">SKS</th>
                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">Nilai</th>
                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">Huruf</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semester['courses'] as $course)
                    <tr class="border-b border-siakad-light/50">
                        <td class="py-3 px-5 text-sm font-mono text-siakad-primary">{{ $course['kode'] }}</td>
                        <td class="py-3 px-5 text-sm text-siakad-dark">{{ $course['nama'] }}</td>
                        <td class="py-3 px-5 text-sm text-siakad-secondary text-center">{{ $course['sks'] }}</td>
                        <td class="py-3 px-5 text-sm text-siakad-secondary text-center">{{ $course['nilai_angka'] }}</td>
                        <td class="py-3 px-5 text-center">
                            @php
                                $gradeColors = [
                                    'A' => 'bg-emerald-100 text-emerald-700',
                                    'B+' => 'bg-siakad-primary/10 text-siakad-primary',
                                    'B' => 'bg-siakad-secondary/10 text-siakad-secondary',
                                    'C+' => 'bg-amber-100 text-amber-700',
                                    'C' => 'bg-orange-100 text-orange-700',
                                    'D' => 'bg-red-100 text-red-700',
                                    'E' => 'bg-red-200 text-red-800',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $gradeColors[$course['nilai_huruf']] ?? 'bg-slate-100 text-slate-600' }}">{{ $course['nilai_huruf'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const siakadPrimary = '#234C6A';
        const siakadSecondary = '#456882';
        const siakadDark = '#1B3C53';
        const siakadLight = '#E3E3E3';

        // IPS Chart
        const ipsData = @json($ipsHistory->filter(fn($s) => $s['ips'] > 0)->values());
        const ipsCtx = document.getElementById('ipsChart').getContext('2d');
        new Chart(ipsCtx, {
            type: 'line',
            data: {
                labels: ipsData.map(d => d.tahun_akademik.substring(0, 9)),
                datasets: [{
                    label: 'IPS',
                    data: ipsData.map(d => d.ips),
                    borderColor: siakadPrimary,
                    backgroundColor: 'rgba(35, 76, 106, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: siakadPrimary,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { min: 0, max: 4, ticks: { stepSize: 0.5 }, grid: { color: siakadLight } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Grade Chart
        const gradeData = @json($gradeDistribution);
        const gradeCtx = document.getElementById('gradeChart').getContext('2d');
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(gradeData),
                datasets: [{
                    data: Object.values(gradeData),
                    backgroundColor: [siakadPrimary, siakadSecondary, siakadDark, '#86c5e0', '#b9dded', '#dceef6', siakadLight],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } },
                cutout: '60%'
            }
        });
    </script>
    @endpush
</x-app-layout>
