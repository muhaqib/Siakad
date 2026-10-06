<x-app-layout>
    <x-slot name="header">
        Notifikasi
    </x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-siakad-dark dark:text-white flex items-center gap-2">
                Daftar Notifikasi
                @if($unreadCount > 0)
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400">
                    {{ $unreadCount }} Baru
                </span>
                @endif
            </h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400">Semua notifikasi dan informasi terbaru untuk Anda</p>
        </div>
        <div class="flex items-center gap-2">
            @if($unreadCount > 0)
            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium dark:text-white text-indigo-600 hover:bg-indigo-50 dark:hover:bg-gray-800 transition">
                    Tandai Semua Dibaca
                </button>
            </form>
            @endif

            @if($notifications->isNotEmpty())
            <form action="{{ route('notifications.clear-all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua notifikasi? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/20 transition flex items-center gap-1.5" title="Hapus semua notifikasi">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Hapus Semua</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="card-saas overflow-hidden dark:bg-gray-800">
        @forelse($notifications as $notif)
        <div class="flex items-start gap-4 p-5 {{ !$notif->isRead() ? 'bg-indigo-50/40 dark:bg-indigo-950/20' : '' }} hover:bg-siakad-light/20 dark:hover:bg-gray-700/30 transition border-b border-siakad-light/50 dark:border-gray-700">
            <div class="w-10 h-10 rounded-xl bg-{{ $notif->color }}-100 dark:bg-{{ $notif->color }}-900/30 flex items-center justify-center text-lg flex-shrink-0">
                {{ $notif->icon }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold {{ !$notif->isRead() ? 'text-indigo-700 dark:text-indigo-400' : 'text-siakad-dark dark:text-white' }}">
                                {{ $notif->title }}
                            </h3>
                            @if(!$notif->isRead())
                            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                            @endif
                        </div>
                        <p class="text-sm text-siakad-secondary dark:text-gray-300 mt-1 whitespace-pre-line">{{ $notif->message }}</p>
                        @if(!empty($notif->data['changes']))
                        <div class="mt-2 text-xs space-y-1">
                            @foreach($notif->data['changes'] as $field => $change)
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-gray-300">{{ ucfirst($field) }}</span>
                                <span class="text-slate-400">{{ $change['old'] }}</span>
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $change['new'] }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400 whitespace-nowrap">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
            </div>

            <div class="flex items-center gap-1 flex-shrink-0">
                @if(!$notif->isRead())
                <form action="{{ route('notifications.mark-read', $notif) }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400" title="Tandai dibaca">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                </form>
                @endif
                <form action="{{ route('notifications.destroy', $notif) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition text-slate-400 hover:text-red-600 dark:hover:text-red-400" title="Hapus notifikasi">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-1">Tidak ada notifikasi</h3>
            <p class="text-sm text-slate-500 dark:text-gray-400">Anda tidak memiliki notifikasi saat ini</p>
        </div>
        @endforelse
    </div>
</x-app-layout>
