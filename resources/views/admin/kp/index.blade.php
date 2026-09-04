<x-app-layout>
    <x-slot name="header">Manajemen Kerja Praktek</x-slot>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        @foreach([['Total', $stats['total'], 'indigo'], ['Aktif', $stats['aktif'], 'blue'], ['Perlu Pembimbing', $stats['perlu_pembimbing'], 'amber'], ['Selesai', $stats['selesai'], 'emerald']] as $s)
        <div class="card-saas p-4 flex items-center gap-3 dark:bg-gray-800"><div class="w-10 h-10 rounded-lg bg-{{ $s[2] }}-100 dark:bg-{{ $s[2] }}-900/50 flex items-center justify-center"><svg class="w-5 h-5 text-{{ $s[2] }}-600 dark:text-{{ $s[2] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div><div><p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $s[1] }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $s[0] }}</p></div></div>
        @endforeach
    </div>
    <div class="card-saas p-4 mb-6 dark:bg-gray-800">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]"><label class="block text-xs font-medium text-siakad-dark dark:text-gray-300 mb-1">Cari</label><input type="text" name="search" value="{{ request('search') }}" class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Nama, NIM, perusahaan..."></div>
            <div class="w-48"><label class="block text-xs font-medium text-siakad-dark dark:text-gray-300 mb-1">Status</label><select name="status" class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white"><option value="">Semua</option>@foreach($statusList as $k => $v)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach</select></div>
            <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Filter</button>
        </form>
    </div>
    <!-- Table Card (Desktop) -->
    <div class="hidden md:block card-saas overflow-hidden dark:bg-gray-800">
        <table class="w-full table-saas">
            <thead>
                <tr class="bg-siakad-light/30 dark:bg-gray-900">
                    <!-- Sortable: Mahasiswa -->
                    <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'mahasiswa_name', 'order' => request('sort') == 'mahasiswa_name' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                            Mahasiswa
                            <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'mahasiswa_name' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                <i class="opacity-{{ request('sort') == 'mahasiswa_name' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                <i class="opacity-{{ request('sort') == 'mahasiswa_name' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                            </span>
                        </a>
                    </th>

                    <!-- Sortable: Perusahaan -->
                    <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama_perusahaan', 'order' => request('sort') == 'nama_perusahaan' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                            Perusahaan
                            <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'nama_perusahaan' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                <i class="opacity-{{ request('sort') == 'nama_perusahaan' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                <i class="opacity-{{ request('sort') == 'nama_perusahaan' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                            </span>
                        </a>
                    </th>

                    <!-- Sortable: Pembimbing -->
                    <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'pembimbing_name', 'order' => request('sort') == 'pembimbing_name' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                            Pembimbing
                            <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'pembimbing_name' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                <i class="opacity-{{ request('sort') == 'pembimbing_name' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                <i class="opacity-{{ request('sort') == 'pembimbing_name' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                            </span>
                        </a>
                    </th>

                    <!-- Sortable: Status -->
                    <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'order' => request('sort') == 'status' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition justify-center">
                            Status
                            <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'status' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                <i class="opacity-{{ request('sort') == 'status' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                <i class="opacity-{{ request('sort') == 'status' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                            </span>
                        </a>
                    </th>

                    <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($kpList as $kp)
            <tr class="border-b border-siakad-light/50 dark:border-gray-700/50">
                <td class="py-4 px-5"><p class="font-medium text-siakad-dark dark:text-white">{{ $kp->mahasiswa->user->name }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $kp->mahasiswa->nim }}</p></td>
                <td class="py-4 px-5 text-sm text-siakad-dark dark:text-white">{{ $kp->nama_perusahaan }}</td>
                <td class="py-4 px-5 text-sm">@if($kp->pembimbing)<span class="text-siakad-dark dark:text-white">{{ $kp->pembimbing->user->name }}</span>@else<span class="text-amber-600 dark:text-amber-400 text-xs">Belum</span>@endif</td>
                <td class="py-4 px-5 text-center"><span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $kp->status_color }}-100 text-{{ $kp->status_color }}-700 dark:bg-{{ $kp->status_color }}-900/50 dark:text-{{ $kp->status_color }}-400">{{ $kp->status_label }}</span></td>
                <td class="py-4 px-5 text-right"><a href="{{ route('admin.kp.show', $kp) }}" class="text-sm text-siakad-primary dark:text-blue-400 hover:underline">Detail</a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-12 text-center text-siakad-secondary dark:text-gray-400">Tidak ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card List -->
    <div class="md:hidden space-y-4">
        @forelse($kpList as $kp)
        <div class="card-saas p-4 dark:bg-gray-800">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-siakad-primary dark:bg-blue-600 flex items-center justify-center text-white text-sm font-semibold">
                        {{ strtoupper(substr($kp->mahasiswa->user->name ?? '-', 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-siakad-dark dark:text-white">{{ $kp->mahasiswa->user->name }}</h4>
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 font-mono">{{ $kp->mahasiswa->nim }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-{{ $kp->status_color }}-100 text-{{ $kp->status_color }}-700 dark:bg-{{ $kp->status_color }}-900/50 dark:text-{{ $kp->status_color }}-400">{{ $kp->status_label }}</span>
            </div>

            <div class="mb-4">
                <p class="text-xs text-siakad-secondary dark:text-gray-400 mb-1">Perusahaan</p>
                <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $kp->nama_perusahaan }}</p>
            </div>

            <div class="bg-siakad-light/30 dark:bg-gray-700/30 rounded-lg p-3 mb-4">
                <p class="text-xs text-siakad-secondary dark:text-gray-400 mb-1">Pembimbing</p>
                @if($kp->pembimbing)
                <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $kp->pembimbing->user->name }}</p>
                @else
                <span class="text-amber-600 dark:text-amber-400 text-xs">Belum ditentukan</span>
                @endif
            </div>

            <a href="{{ route('admin.kp.show', $kp) }}" class="flex items-center justify-center w-full py-2 bg-siakad-light dark:bg-gray-700 text-siakad-dark dark:text-white font-medium rounded-lg hover:bg-gray-200 transition text-sm">
                Lihat Detail
            </a>
        </div>
        @empty
        <div class="card-saas p-8 text-center">
            <p class="text-siakad-secondary dark:text-gray-400">Tidak ada data KP</p>
        </div>
        @endforelse
    </div>

    @if($kpList->hasPages())
    <div class="card-saas px-5 py-4 border-t border-siakad-light dark:border-gray-700 dark:bg-gray-800 mt-4 md:mt-0">
        {{ $kpList->links() }}
    </div>
    @endif
</x-app-layout>
