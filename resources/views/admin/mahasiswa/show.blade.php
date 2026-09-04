<x-app-layout>
    <x-slot name="header">
        Detail Mahasiswa
    </x-slot>

    <div class="mb-6">
        <a href="{{ route('admin.mahasiswa.index') }}" class="inline-flex items-center gap-2 text-sm text-siakad-secondary hover:text-siakad-primary transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Daftar
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="card-saas p-6">
            <div class="text-center mb-6">
                <div class="w-20 h-20 mx-auto rounded-xl bg-siakad-primary flex items-center justify-center text-white text-3xl font-bold mb-4">
                    {{ strtoupper(substr($mahasiswa->user->name ?? '-', 0, 1)) }}
                </div>
                <h2 class="text-xl font-semibold text-siakad-dark">{{ $mahasiswa->user->name ?? '-' }}</h2>
                <p class="text-sm font-mono text-siakad-secondary">{{ $mahasiswa->nim }}</p>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-siakad-light/50">
                    <span class="text-sm text-siakad-secondary">Program Studi</span>
                    <span class="text-sm font-medium text-siakad-dark">{{ $mahasiswa->prodi->nama ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-siakad-light/50">
                    <span class="text-sm text-siakad-secondary">Angkatan</span>
                    <span class="text-sm font-medium text-siakad-dark">{{ $mahasiswa->angkatan }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-siakad-light/50">
                    <span class="text-sm text-siakad-secondary">Dosen PA</span>
                    <span class="text-sm font-medium text-siakad-dark">{{ $mahasiswa->dosenPa->user->name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-siakad-secondary">Status</span>
                    @if($mahasiswa->status === 'aktif')
                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full">Aktif</span>
                    @else
                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-600 rounded-full">{{ ucfirst($mahasiswa->status ?? 'Aktif') }}</span>
                    @endif
                </div>
            </div>

            <!-- IPK Card -->
            <div class="mt-6 bg-siakad-primary rounded-xl p-5 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs opacity-80 uppercase tracking-wide">Indeks Prestasi Kumulatif</p>
                        <p class="text-3xl font-bold mt-1">{{ number_format($ipkData['ips'] ?? 0, 2) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs opacity-80">Total SKS</p>
                        <p class="text-xl font-semibold">{{ $ipkData['total_sks'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic History -->
        <div class="lg:col-span-2 space-y-6">
            <!-- IPS History Chart -->
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Perkembangan IPS</h3>
                <div class="h-48">
                    <canvas id="ipsChart"></canvas>
                </div>
            </div>

            <!-- Grade Distribution -->
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Distribusi Nilai</h3>
                <div class="grid grid-cols-7 gap-3">
                    @foreach($gradeDistribution as $grade => $count)
                    <div class="text-center p-3 bg-siakad-light/30 rounded-lg">
                        <p class="text-lg font-bold text-siakad-dark">{{ $count }}</p>
                        <p class="text-xs text-siakad-secondary">{{ $grade }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- KRS History -->
            <div class="card-saas overflow-hidden">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Riwayat KRS</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full table-saas">
                        <thead>
                            <tr class="bg-siakad-light/30">
                                <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">Tahun Akademik</th>
                                <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">SKS</th>
                                <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">IPS</th>
                                <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ipsHistory as $semester)
                            <tr class="border-b border-siakad-light/50">
                                <td class="py-3 px-5 text-sm text-siakad-dark">{{ $semester['tahun_akademik'] }}</td>
                                <td class="py-3 px-5 text-sm text-siakad-secondary">{{ $semester['total_sks'] }} SKS</td>
                                <td class="py-3 px-5 text-sm font-semibold text-siakad-primary">{{ number_format($semester['ips'], 2) }}</td>
                                <td class="py-3 px-5">
                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-700 rounded-full">Lulus</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-siakad-secondary text-sm">Belum ada riwayat KRS</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const siakadPrimary = '#234C6A';
        const siakadLight = '#E3E3E3';
        
        const ipsData = @json($ipsHistory);
        const ctx = document.getElementById('ipsChart').getContext('2d');
        new Chart(ctx, {
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
    </script>
    @endpush
</x-app-layout>
