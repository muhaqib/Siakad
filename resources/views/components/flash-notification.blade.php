@php
    $type = session()->has('error') ? 'error' : (session()->has('success') ? 'success' : null);
@endphp

@if($type)
    <div
        data-flash-notification
        x-data="{ visible: false, timer: null, dismiss() { clearTimeout(this.timer); this.visible = false }, init() { this.$nextTick(() => { this.visible = true; this.timer = setTimeout(() => this.dismiss(), 5000) }) }, destroy() { clearTimeout(this.timer) } }"
        x-cloak
        x-show="visible"
        x-transition:enter="transform transition ease-out duration-300 motion-reduce:transition-none"
        x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-6"
        x-transition:enter-end="opacity-100 translate-x-0 translate-y-0"
        x-transition:leave="transform transition ease-in duration-200 motion-reduce:transition-none"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-6"
        class="fixed right-4 top-20 z-[100] w-[calc(100%-2rem)] max-w-sm overflow-hidden rounded-2xl border shadow-xl sm:right-6 {{ $type === 'success' ? 'border-emerald-200 dark:border-emerald-800' : 'border-red-200 dark:border-red-800' }}"
        style="background-color: var(--bg-card); color: var(--text-primary);"
        role="{{ $type === 'error' ? 'alert' : 'status' }}"
        aria-atomic="true"
    >
        <div class="flex items-start gap-3 p-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $type === 'success' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-red-50 text-red-600 dark:bg-red-900/40 dark:text-red-400' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    @if($type === 'success')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    @endif
                </svg>
            </span>
            <div class="flex min-w-0 flex-1 flex-col gap-1">
                <p class="text-sm font-semibold">{{ $type === 'success' ? 'Berhasil!' : 'Gagal memproses' }}</p>
                <p class="break-words text-sm leading-relaxed" style="color: var(--text-secondary);">{{ session($type) }}</p>
            </div>
            <button type="button" @click="dismiss()" aria-label="Tutup notifikasi" class="shrink-0 rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M6 18 18 6" /></svg>
            </button>
        </div>
    </div>
@endif
