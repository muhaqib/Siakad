<x-app-layout>
    <x-slot name="header">
        Input Nilai - {{ $kelas->mataKuliah->nama_mk }}
    </x-slot>

    <div class="mb-4">
        <a href="{{ route('dosen.penilaian.index') }}" class="inline-flex items-center gap-2 text-sm text-siakad-secondary hover:text-siakad-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Daftar Mata Kuliah
        </a>
    </div>

    <!-- Class Info -->
    <div class="card-saas p-6 mb-6 border-none" style="background-color: #1B3C53;">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-white">{{ $kelas->mataKuliah->nama_mk }}</h3>
                <p class="text-white/80 mt-1">{{ $kelas->mataKuliah->kode_mk }} • {{ $kelas->mataKuliah->sks }} SKS</p>
                <div class="flex items-center gap-3 mt-4 text-sm font-medium bg-white/10 w-fit px-3 py-1.5 rounded-lg text-white">
                    <span>Kelas {{ $kelas->nama_kelas }}</span>
                    <span class="w-1 h-1 bg-white rounded-full"></span>
                    <span>{{ $kelas->krsDetail->count() }} Mahasiswa</span>
                </div>
            </div>
            <div class="hidden sm:block">
                <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center text-3xl font-bold text-white">
                    {{ substr($kelas->nama_kelas, 0, 1) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Input Form -->
    <div class="card-saas overflow-hidden">
        <form action="{{ route('dosen.penilaian.store', $kelas->id) }}" method="POST">
            @csrf
            
            <div class="px-6 py-4 border-b border-siakad-light bg-siakad-light/30">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-siakad-dark">Daftar Mahasiswa</h3>
                    <p class="text-sm text-siakad-secondary text-right">Masukkan nilai angka (0-100)</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-saas">
                    <thead>
                        <tr class="bg-siakad-light/30">
                            <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase w-16">No</th>
                            <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">NIM</th>
                            <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase">Nama Mahasiswa</th>
                            <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase w-32">Nilai Angka</th>
                            <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase w-24">Nilai Huruf</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-siakad-light/50">
                        @forelse($kelas->krsDetail as $index => $detail)
                            @php
                                $nilai = $kelas->nilai->where('mahasiswa_id', $detail->krs->mahasiswa_id)->first();
                            @endphp
                        <tr class="hover:bg-siakad-light/10 transition">
                            <td class="py-4 px-5 text-sm text-siakad-secondary text-center">{{ $index + 1 }}</td>
                            <td class="py-4 px-5">
                                <span class="font-mono text-sm text-siakad-secondary">{{ $detail->krs->mahasiswa->nim }}</span>
                            </td>
                            <td class="py-4 px-5">
                                <span class="font-medium text-siakad-dark">{{ $detail->krs->mahasiswa->user->name }}</span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <input type="number" 
                                    name="nilai[{{ $detail->krs->mahasiswa_id }}]" 
                                    value="{{ $nilai?->nilai_angka }}" 
                                    step="0.01" 
                                    min="0" 
                                    max="100" 
                                    class="input-saas text-center font-mono focus:ring-siakad-primary focus:border-siakad-primary transition-all duration-200" 
                                    placeholder="0-100">
                            </td>
                            <td class="py-4 px-5 text-center">
                                @if($nilai?->nilai_huruf)
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold
                                    {{ in_array($nilai->nilai_huruf, ['A', 'B+']) ? 'bg-emerald-100 text-emerald-700' : 
                                       (in_array($nilai->nilai_huruf, ['B', 'C+']) ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $nilai->nilai_huruf }}
                                </span>
                                @else
                                <span class="text-siakad-light text-2xl">•</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-siakad-secondary">
                                Tidak ada mahasiswa di kelas ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-siakad-light/30 border-t border-siakad-light sticky bottom-0 flex justify-end">
                <button type="submit" class="btn-primary-saas px-6 py-2.5 rounded-lg text-sm font-semibold shadow-lg shadow-siakad-primary/20 hover:shadow-siakad-primary/40 transition-all duration-300 transform hover:-translate-y-0.5">
                    Simpan Semua Nilai
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
