<x-app-layout>
    <x-slot name="header">
        Ajukan Judul Skripsi
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('mahasiswa.skripsi.index') }}" class="inline-flex items-center gap-2 text-siakad-secondary hover:text-siakad-primary transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Kembali
            </a>
        </div>

        <div class="card-saas overflow-hidden">
            <div class="px-6 py-4 border-b border-siakad-light bg-gradient-to-r from-siakad-primary to-siakad-primary/80">
                <h2 class="text-lg font-semibold text-white">Form Pengajuan Judul Skripsi</h2>
                <p class="text-sm text-white/70 mt-1">Isi form berikut untuk mengajukan judul skripsi Anda</p>
            </div>

            <form action="{{ route('mahasiswa.skripsi.store') }}" method="POST" class="p-6 space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-2">Judul Skripsi *</label>
                    <textarea name="judul" rows="3" class="input-saas w-full text-sm @error('judul') border-red-500 @enderror" placeholder="Masukkan judul skripsi yang Anda ajukan..." required>{{ old('judul') }}</textarea>
                    @error('judul')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-2">Bidang Kajian</label>
                    <input type="text" name="bidang_kajian" value="{{ old('bidang_kajian') }}" class="input-saas w-full text-sm" placeholder="Contoh: Machine Learning, Web Development, dll">
                </div>

                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-2">Abstrak / Ringkasan</label>
                    <textarea name="abstrak" rows="5" class="input-saas w-full text-sm" placeholder="Tuliskan ringkasan rencana penelitian Anda...">{{ old('abstrak') }}</textarea>
                    <p class="text-xs text-siakad-secondary mt-1">Opsional, dapat dilengkapi kemudian</p>
                </div>

                <div class="pt-4 border-t border-siakad-light flex items-center justify-end gap-3">
                    <a href="{{ route('mahasiswa.skripsi.index') }}" class="btn-ghost-saas px-4 py-2.5 rounded-lg text-sm font-medium">Batal</a>
                    <button type="submit" class="btn-primary-saas px-6 py-2.5 rounded-lg text-sm font-medium">Ajukan Judul</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
