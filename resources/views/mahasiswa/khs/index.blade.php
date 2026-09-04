<x-app-layout>
    <x-slot name="header">
        Kartu Hasil Studi (KHS)
    </x-slot>

    <div class="mb-8">
        <h1 class="text-xl font-semibold text-siakad-dark hidden md:block">Kartu Hasil Studi</h1>
        <p class="text-siakad-secondary mt-1 hidden md:block">Lihat nilai per semester</p>
    </div>

    <!-- IPK Summary Card -->
    <div class="rounded-2xl p-6 bg-[#1B3C53] text-white mb-8">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-white/70">Indeks Prestasi Kumulatif (IPK)</p>
                <p class="text-4xl font-bold mt-2 text-white">{{ number_format($ipkData['ips'], 2) }}</p>
                <p class="text-sm text-white/50 mt-1">Total {{ $ipkData['total_sks'] }} SKS dari {{ $semesterList->count() }} semester</p>
            </div>
            <div class="w-20 h-20 rounded-full bg-white/10 flex items-center justify-center">
                <svg class="w-10 h-10 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
        </div>
    </div>

    @if($semesterList->isEmpty())
    <div class="card-saas p-12 text-center">
        <div class="w-20 h-20 rounded-full bg-siakad-light/50 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
        </div>
        <h3 class="text-xl font-bold text-siakad-dark mb-2">Belum Ada Data KHS</h3>
        <p class="text-siakad-secondary mb-4">KRS Anda belum ada yang diapprove.</p>
        <a href="{{ route('mahasiswa.krs.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-siakad-primary text-white rounded-lg hover:bg-siakad-primary/90 transition">
            Lihat KRS
        </a>
    </div>
    @else
    <!-- Semester List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($semesterList as $item)
        @php
            $ta = $item['tahun_akademik'];
            // Use system palette colors instead of Tailwind defaults
            $ipsColor = $item['ips'] >= 3.5 ? '#234C6A' : ($item['ips'] >= 3.0 ? '#456882' : ($item['ips'] >= 2.5 ? '#F59E0B' : '#EF4444'));
        @endphp
        <a href="{{ route('mahasiswa.khs.show', $ta) }}" class="card-saas group overflow-hidden hover:ring-2 hover:ring-siakad-primary transition-all">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-siakad-dark group-hover:text-siakad-primary transition">{{ $ta->tahun }}</h3>
                        <p class="text-sm text-siakad-secondary">Semester {{ $ta->semester }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold" style="color: {{ $ipsColor }}">{{ number_format($item['ips'], 2) }}</p>
                        <p class="text-xs text-siakad-secondary">IPS</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 text-sm text-siakad-secondary">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        {{ $item['jumlah_mk'] }} MK
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                        {{ $item['total_sks'] }} SKS
                    </span>
                </div>
                
                <!-- Progress Bar - Using system palette -->
                <div class="mt-4 h-2 bg-siakad-light rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all" style="width: {{ min($item['ips'] / 4 * 100, 100) }}%; background-color: {{ $ipsColor }}"></div>
                </div>
            </div>
            
            <div class="px-6 py-3 bg-siakad-light/30 border-t border-siakad-light flex items-center justify-between">
                <span class="text-xs text-siakad-secondary">Lihat Detail</span>
                <svg class="w-4 h-4 text-siakad-secondary group-hover:text-siakad-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</x-app-layout>
