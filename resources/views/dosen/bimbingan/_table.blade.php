<table class="w-full table-saas">
    <thead>
        <tr class="bg-siakad-light/30 dark:bg-gray-900">
            <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider w-16">#</th>
            <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider sortable-header cursor-pointer hover:bg-siakad-light/50 transition" data-sort="nama">
                <div class="flex items-center gap-1">
                    Mahasiswa
                    <span class="sort-icon">
                        @if(request('sort') === 'nama')
                            @if(request('dir') === 'asc')
                                <svg class="w-3 h-3 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            @else
                                <svg class="w-3 h-3 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            @endif
                        @else
                            <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                        @endif
                    </span>
                </div>
            </th>
            <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider sortable-header cursor-pointer hover:bg-siakad-light/50 transition" data-sort="nim">
                <div class="flex items-center gap-1">
                    NIM
                    <span class="sort-icon">
                        @if(request('sort') === 'nim')
                            @if(request('dir') === 'asc')
                                <svg class="w-3 h-3 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            @else
                                <svg class="w-3 h-3 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            @endif
                        @else
                            <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                        @endif
                    </span>
                </div>
            </th>
            <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider sortable-header cursor-pointer hover:bg-siakad-light/50 transition" data-sort="angkatan">
                <div class="flex items-center gap-1">
                    Angkatan
                    <span class="sort-icon">
                        @if(request('sort') === 'angkatan' || !request('sort'))
                            @if(request('dir') === 'asc')
                                <svg class="w-3 h-3 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            @else
                                <svg class="w-3 h-3 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            @endif
                        @else
                            <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                        @endif
                    </span>
                </div>
            </th>
            <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary uppercase tracking-wider sortable-header cursor-pointer hover:bg-siakad-light/50 transition" data-sort="status">
                <div class="flex items-center gap-1">
                    Status
                    <span class="sort-icon">
                        @if(request('sort') === 'status')
                            @if(request('dir') === 'asc')
                                <svg class="w-3 h-3 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            @else
                                <svg class="w-3 h-3 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            @endif
                        @else
                            <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                        @endif
                    </span>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($mahasiswaBimbingan as $index => $m)
        <tr class="border-b border-siakad-light/50">
            <td class="py-4 px-5 text-sm text-siakad-secondary">{{ $mahasiswaBimbingan->firstItem() + $index }}</td>
            <td class="py-4 px-5">
                <span class="text-sm font-medium text-siakad-dark">{{ $m->user->name ?? '-' }}</span>
            </td>
            <td class="py-4 px-5">
                <span class="text-sm font-mono text-siakad-secondary">{{ $m->nim }}</span>
            </td>
            <td class="py-4 px-5">
                <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-siakad-primary/10 text-siakad-primary rounded-full">{{ $m->angkatan }}</span>
            </td>
            <td class="py-4 px-5">
                @php
                    $statusClass = match($m->status ?? 'aktif') {
                        'aktif' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-700/30 dark:text-emerald-400',
                        'cuti' => 'bg-amber-100 text-amber-700 dark:bg-amber-700/30 dark:text-amber-400',
                        'lulus' => 'bg-blue-100 text-blue-700 dark:bg-blue-700/30 dark:text-blue-400',
                        'do' => 'bg-red-100 text-red-700 dark:bg-red-700/30 dark:text-red-400',
                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700/30 dark:text-gray-400',
                    };
                    $statusLabel = ucfirst($m->status ?? 'Aktif');
                @endphp
                <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold {{ $statusClass }} rounded-full">{{ $statusLabel }}</span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="py-12 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 bg-siakad-light/50 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <p class="text-siakad-secondary text-sm">Tidak ada data ditemukan</p>
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@if($mahasiswaBimbingan->hasPages())
<div class="px-5 py-4 border-t border-siakad-light">
    {{ $mahasiswaBimbingan->links() }}
</div>
@endif
