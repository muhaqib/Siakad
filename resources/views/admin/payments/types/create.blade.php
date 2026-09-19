<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">Tambah Tarif</span>
        <span class="hidden md:inline">Tambah Jenis & Tarif Pembayaran</span>
    </x-slot>

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.payment-types.index') }}" class="btn-ghost-saas p-2 rounded-lg flex items-center justify-center" title="Kembali">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-xl font-semibold text-siakad-dark dark:text-white hidden md:block">
                        Tambah Jenis & Tarif Pembayaran
                    </h1>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">
                        Tambahkan master komponen biaya perkuliahan baru mahasiswa STIT Mambaul Hikmah
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.payment-types.index') }}" class="btn-ghost-saas px-3.5 py-2 text-xs font-semibold rounded-lg">
                    Kembali ke Daftar Tarif
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm">
                <div class="font-semibold mb-1 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Terdapat beberapa kesalahan pengisian form:
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="max-w-2xl mx-auto" x-data="{ category: '{{ old('category', 'semester') }}' }">
            <div class="card-saas p-6 dark:bg-gray-800">
                <form action="{{ route('admin.payment-types.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">
                            Kode Biaya <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="code" value="{{ old('code') }}" required placeholder="Contoh: WISUDA, PRAKTIKUM, DPP, SEM_9"
                            class="input-saas w-full text-xs py-2.5 px-3 uppercase font-mono @error('code') border-rose-500 @enderror">
                        <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-1">
                            Kode unik singkat (hanya huruf, angka, strip, atau garis bawah). Akan digunakan dalam format nomor invoice tagihan.
                        </p>
                        @error('code')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">
                            Nama Jenis Pembayaran <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Biaya Wisuda & Ijazah, Praktikum Komputer"
                            class="input-saas w-full text-xs py-2.5 px-3 @error('name') border-rose-500 @enderror">
                        @error('name')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">
                                Kategori <span class="text-rose-500">*</span>
                            </label>
                            <select name="category" x-model="category" required class="input-saas w-full text-xs py-2.5 px-3 @error('category') border-rose-500 @enderror">
                                <option value="semester">Semester (SPP / Perkuliahan Rutin)</option>
                                <option value="registration">Pendaftaran (Heregistrasi / Masuk)</option>
                                <option value="other">Lainnya (Wisuda, Praktikum, Skripsi, dll)</option>
                            </select>
                            @error('category')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="category === 'semester'" x-transition>
                            <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">
                                Semester Ke <span class="text-rose-500" x-show="category === 'semester'">*</span>
                            </label>
                            <input type="number" name="semester" value="{{ old('semester') }}" min="1" max="14" placeholder="1 - 14"
                                class="input-saas w-full text-xs py-2.5 px-3 @error('semester') border-rose-500 @enderror">
                            <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-1">Masukkan angka semester perkuliahan.</p>
                            @error('semester')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">
                            Tarif Default (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-gray-500 dark:text-gray-400">Rp</span>
                            <input type="number" name="default_amount" value="{{ old('default_amount', 0) }}" required min="0" step="1000"
                                class="input-saas w-full text-xs py-2.5 pl-10 pr-3 font-semibold @error('default_amount') border-rose-500 @enderror">
                        </div>
                        <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-1">
                            Besaran tarif standar yang akan otomatis menjadi nominal tagihan mahasiswa saat generate kewajiban biaya.
                        </p>
                        @error('default_amount')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">
                            Status Keaktifan <span class="text-rose-500">*</span>
                        </label>
                        <select name="is_active" class="input-saas w-full text-xs py-2.5 px-3 @error('is_active') border-rose-500 @enderror">
                            <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Aktif (Dapat Diterapkan pada Tagihan)</option>
                            <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Nonaktif (Ditangguhkan)</option>
                        </select>
                        @error('is_active')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">
                            Deskripsi / Keterangan
                        </label>
                        <textarea name="description" rows="3" placeholder="Tuliskan catatan atau rincian komponen biaya..."
                            class="input-saas w-full text-xs py-2 px-3 @error('description') border-rose-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end items-center gap-3 pt-4 border-t border-siakad-light dark:border-gray-700">
                        <a href="{{ route('admin.payment-types.index') }}" class="btn-ghost-saas px-4 py-2 text-xs font-medium rounded-lg">
                            Batal
                        </a>
                        <button type="submit" class="btn-primary-saas px-5 py-2 text-xs font-semibold rounded-lg shadow-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Simpan Biaya
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
