<x-app-layout>
    <x-slot name="header">Detail KP</x-slot>
    <div class="mb-6"><a href="{{ route('admin.kp.index') }}" class="text-siakad-secondary hover:text-siakad-primary text-sm">← Kembali</a></div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card-saas p-6">
                <div class="flex items-start justify-between mb-4"><span class="px-3 py-1 text-xs font-medium rounded-full bg-{{ $kp->status_color }}-100 text-{{ $kp->status_color }}-700">{{ $kp->status_label }}</span><span class="text-2xl font-bold text-siakad-primary">{{ $kp->progress_percent }}%</span></div>
                <h2 class="text-lg font-bold text-siakad-dark">{{ $kp->nama_perusahaan }}</h2>
                <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                    <div class="p-3 rounded-lg bg-siakad-light/30"><p class="text-xs text-siakad-secondary">Mahasiswa</p><p class="font-medium text-siakad-dark">{{ $kp->mahasiswa->user->name }}</p><p class="text-xs text-siakad-secondary">{{ $kp->mahasiswa->nim }}</p></div>
                    <div class="p-3 rounded-lg bg-siakad-light/30"><p class="text-xs text-siakad-secondary">Periode</p><p class="font-medium text-siakad-dark">{{ $kp->tanggal_mulai->format('d M') }} - {{ $kp->tanggal_selesai->format('d M Y') }}</p></div>
                </div>
            </div>
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Assign Pembimbing</h3>
                <form action="{{ route('admin.kp.assign-pembimbing', $kp) }}" method="POST" class="flex items-end gap-3">@csrf
                    <div class="flex-1"><label class="block text-xs font-medium text-siakad-dark mb-1">Pembimbing Kampus</label><select name="pembimbing_id" class="input-saas w-full text-sm" required><option value="">Pilih Dosen</option>@foreach($dosenList as $d)<option value="{{ $d->id }}" {{ $kp->pembimbing_id == $d->id ? 'selected' : '' }}>{{ $d->user->name }}</option>@endforeach</select></div>
                    <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Simpan</button>
                </form>
            </div>
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Update Status</h3>
                <form action="{{ route('admin.kp.update-status', $kp) }}" method="POST" class="space-y-4">@csrf @method('PUT')
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Status</label><select name="status" class="input-saas w-full text-sm">@foreach(\App\Models\KerjaPraktek::getStatusList() as $k => $v)<option value="{{ $k }}" {{ $kp->status === $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach</select></div>
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Catatan</label><textarea name="catatan" rows="2" class="input-saas w-full text-sm">{{ $kp->catatan }}</textarea></div>
                    <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Update</button>
                </form>
            </div>
            @if(in_array($kp->status, ['seminar', 'revisi']))
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Input Nilai</h3>
                <form action="{{ route('admin.kp.update-nilai', $kp) }}" method="POST" class="grid grid-cols-2 gap-4">@csrf @method('PUT')
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Perusahaan</label><input type="number" name="nilai_perusahaan" value="{{ $kp->nilai_perusahaan }}" step="0.01" class="input-saas w-full text-sm"></div>
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Pembimbing</label><input type="number" name="nilai_pembimbing" value="{{ $kp->nilai_pembimbing }}" step="0.01" class="input-saas w-full text-sm"></div>
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Seminar</label><input type="number" name="nilai_seminar" value="{{ $kp->nilai_seminar }}" step="0.01" class="input-saas w-full text-sm"></div>
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Akhir *</label><input type="number" name="nilai_akhir" value="{{ $kp->nilai_akhir }}" step="0.01" class="input-saas w-full text-sm" required></div>
                    <div class="col-span-2"><label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Huruf *</label><select name="nilai_huruf" class="input-saas w-full text-sm" required>@foreach(['A','B+','B','C+','C','D','E'] as $h)<option value="{{ $h }}" {{ $kp->nilai_huruf === $h ? 'selected' : '' }}>{{ $h }}</option>@endforeach</select></div>
                    <div class="col-span-2"><button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Simpan Nilai</button></div>
                </form>
            </div>
            @endif
        </div>
        <div class="space-y-6">
            <div class="card-saas p-5"><h3 class="font-semibold text-siakad-dark mb-3">Info Perusahaan</h3><p class="text-sm text-siakad-dark">{{ $kp->nama_perusahaan }}</p><p class="text-xs text-siakad-secondary">{{ $kp->alamat_perusahaan }}</p><p class="text-xs text-siakad-secondary">{{ $kp->bidang_usaha }}</p></div>
            <div class="card-saas p-5"><h3 class="font-semibold text-siakad-dark mb-3">Logbook</h3><p class="text-3xl font-bold text-siakad-primary">{{ $kp->logbook->count() }}</p><p class="text-sm text-siakad-secondary">entries</p></div>
        </div>
    </div>
</x-app-layout>
