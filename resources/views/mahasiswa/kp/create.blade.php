<x-app-layout>
    <x-slot name="header">Ajukan Kerja Praktek</x-slot>
    <div class="max-w-2xl mx-auto">
        <div class="mb-6"><a href="{{ route('mahasiswa.kp.index') }}" class="text-siakad-secondary hover:text-siakad-primary text-sm">← Kembali</a></div>
        <div class="card-saas overflow-hidden">
            <div class="px-6 py-4 border-b border-siakad-light bg-gradient-to-r from-siakad-primary to-siakad-primary/80">
                <h2 class="text-lg font-semibold text-white">Form Pengajuan Kerja Praktek</h2>
            </div>
            <form action="{{ route('mahasiswa.kp.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div><label class="block text-sm font-medium text-siakad-dark mb-1">Nama Perusahaan *</label><input type="text" name="nama_perusahaan" class="input-saas w-full text-sm" required></div>
                <div><label class="block text-sm font-medium text-siakad-dark mb-1">Alamat</label><textarea name="alamat_perusahaan" rows="2" class="input-saas w-full text-sm"></textarea></div>
                <div><label class="block text-sm font-medium text-siakad-dark mb-1">Bidang Usaha</label><input type="text" name="bidang_usaha" class="input-saas w-full text-sm"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-siakad-dark mb-1">Tanggal Mulai *</label><input type="date" name="tanggal_mulai" class="input-saas w-full text-sm" required></div>
                    <div><label class="block text-sm font-medium text-siakad-dark mb-1">Tanggal Selesai *</label><input type="date" name="tanggal_selesai" class="input-saas w-full text-sm" required></div>
                </div>
                <hr class="border-siakad-light">
                <p class="text-xs text-siakad-secondary">Pembimbing Lapangan (opsional)</p>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-siakad-dark mb-1">Nama</label><input type="text" name="nama_pembimbing_lapangan" class="input-saas w-full text-sm"></div>
                    <div><label class="block text-sm font-medium text-siakad-dark mb-1">Jabatan</label><input type="text" name="jabatan_pembimbing_lapangan" class="input-saas w-full text-sm"></div>
                </div>
                <div><label class="block text-sm font-medium text-siakad-dark mb-1">No. Telp Pembimbing</label><input type="text" name="no_telp_pembimbing" class="input-saas w-full text-sm"></div>
                <div class="pt-4 flex justify-end gap-3">
                    <a href="{{ route('mahasiswa.kp.index') }}" class="btn-ghost-saas px-4 py-2.5 rounded-lg text-sm">Batal</a>
                    <button type="submit" class="btn-primary-saas px-6 py-2.5 rounded-lg text-sm font-medium">Ajukan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
