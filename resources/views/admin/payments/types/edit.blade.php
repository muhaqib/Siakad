<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">Edit Tarif</span>
        <span class="hidden md:inline">Edit Jenis & Tarif Pembayaran</span>
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
                        Edit Jenis Pembayaran: {{ $paymentType->name }}
                    </h1>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">
                        Ubah besaran tarif default dan status keaktifan biaya perkuliahan
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.payment-types.index') }}" class="btn-ghost-saas px-3.5 py-2 text-xs font-semibold rounded-lg">
                    Kembali ke Daftar Tarif
                </a>
            </div>
        </div>

        <div class="max-w-2xl mx-auto">
            <div class="card-saas p-6 dark:bg-gray-800">
                <form action="{{ route('admin.payment-types.update', $paymentType->id) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">Kode Jenis</label>
                        <input type="text" value="{{ $paymentType->code }}" disabled
                            class="input-saas w-full text-xs py-2.5 px-3 bg-gray-50 dark:bg-gray-900/50 text-siakad-secondary dark:text-gray-400 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">Nama Jenis Pembayaran</label>
                        <input type="text" name="name" value="{{ old('name', $paymentType->name) }}" required
                            class="input-saas w-full text-xs py-2.5 px-3">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">Tarif Default (Rp)</label>
                        <input type="number" name="default_amount" value="{{ old('default_amount', (int)$paymentType->default_amount) }}" required min="0"
                            class="input-saas w-full text-xs py-2.5 px-3 font-semibold">
                        <p class="text-[11px] text-siakad-secondary dark:text-gray-400 mt-1">Tarif ini akan menjadi acuan saat pembuatan invoice tagihan mahasiswa.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">Status Keaktifan</label>
                        <select name="is_active" class="input-saas w-full text-xs py-2.5 px-3">
                            <option value="1" {{ old('is_active', $paymentType->is_active) ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !old('is_active', $paymentType->is_active) ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-siakad-dark dark:text-gray-200 mb-1.5">Deskripsi / Keterangan</label>
                        <textarea name="description" rows="3" class="input-saas w-full text-xs py-2 px-3">{{ old('description', $paymentType->description) }}</textarea>
                    </div>

                    <div class="flex justify-end items-center gap-3 pt-4 border-t border-siakad-light dark:border-gray-700">
                        <a href="{{ route('admin.payment-types.index') }}" class="btn-ghost-saas px-4 py-2 text-xs font-medium rounded-lg">
                            Batal
                        </a>
                        <button type="submit" class="btn-primary-saas px-5 py-2 text-xs font-semibold rounded-lg shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
