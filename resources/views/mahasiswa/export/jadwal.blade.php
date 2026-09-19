<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kuliah {{ $activeTA->tahun }} Semester {{ $activeTA->semester }} - {{ $mahasiswa->nim }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.PNG') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.4; padding: 20mm; background: white; color: #111; }
        .header { margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 12px; }
        .kop-container { display: flex; align-items: center; justify-content: center; gap: 18px; text-align: center; }
        .kop-logo { height: 80px; width: auto; object-fit: contain; }
        .kop-text h1 { font-size: 15pt; font-weight: bold; text-transform: uppercase; margin-bottom: 3px; }
        .kop-text h2 { font-size: 12pt; font-weight: 600; margin-bottom: 2px; }
        .kop-text p { font-size: 10pt; color: #333; }
        .title { text-align: center; font-size: 13pt; font-weight: bold; margin: 18px 0 6px 0; text-transform: uppercase; letter-spacing: 1.5px; }
        .subtitle { text-align: center; font-size: 11pt; margin-bottom: 18px; }
        .info-table { width: 100%; margin-bottom: 18px; }
        .info-table td { padding: 3px 0; vertical-align: top; font-size: 10.5pt; }
        .info-table .label { width: 150px; }
        .info-table .separator { width: 18px; text-align: center; }
        table.jadwal { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.jadwal th, table.jadwal td { border: 1px solid #000; padding: 6px 8px; font-size: 10pt; }
        table.jadwal th { background: #f0f0f0; font-weight: bold; text-align: center; }
        table.jadwal td.center { text-align: center; }
        table.jadwal tfoot td { font-weight: bold; background: #f9f9f9; }
        .footer { margin-top: 35px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .footer .signature { text-align: center; width: 220px; font-size: 10.5pt; }
        .footer .signature .line { border-top: 1px solid #000; margin-top: 60px; padding-top: 5px; font-weight: bold; }
        .print-btn { 
            position: fixed; bottom: 20px; right: 20px; padding: 12px 24px; 
            background: #234C6A; color: white; border: none; border-radius: 8px; 
            cursor: pointer; font-size: 14px; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex; align-items: center; gap: 8px; font-family: sans-serif;
            transition: all 0.2s ease;
        }
        .print-btn:hover { background: #1B3C53; transform: translateY(-1px); }
        @media print {
            body { padding: 10mm; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        🖨️ Cetak / Simpan PDF
    </button>

    <div class="header">
        <div class="kop-container">
            <img src="{{ asset('logo.PNG') }}" alt="Logo STIT Mambaul Hikmah" class="kop-logo">
            <div class="kop-text">
                <h1>SEKOLAH TINGGI ILMU TARBIYAH (STIT) MAMBAUL HIKMAH</h1>
                <h2>Program Studi {{ $mahasiswa->prodi->nama ?? '-' }}</h2>
                <p>Sistem Informasi Akademik (SIAKAD) &bull; Tegal, Jawa Tengah</p>
            </div>
        </div>
    </div>

    <div class="title">Jadwal Perkuliahan Mahasiswa</div>
    <div class="subtitle">Tahun Akademik {{ $activeTA->tahun }} — Semester {{ $activeTA->semester }}</div>

    <table class="info-table">
        <tr>
            <td class="label">Nama Mahasiswa</td>
            <td class="separator">:</td>
            <td><strong>{{ $mahasiswa->user->name }}</strong></td>
            <td class="label">Program Studi</td>
            <td class="separator">:</td>
            <td>{{ $mahasiswa->prodi->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td class="separator">:</td>
            <td><strong>{{ $mahasiswa->nim }}</strong></td>
            <td class="label">Dosen Pembimbing</td>
            <td class="separator">:</td>
            <td>{{ $mahasiswa->dosenPa->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Semester / Angkatan</td>
            <td class="separator">:</td>
            <td>Angkatan {{ $mahasiswa->angkatan ?? '-' }}</td>
            <td class="label">Tanggal Cetak</td>
            <td class="separator">:</td>
            <td>{{ now()->locale('id')->isoFormat('D MMMM Y') }}</td>
        </tr>
    </table>

    <table class="jadwal">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th style="width: 75px;">Hari</th>
                <th style="width: 95px;">Waktu</th>
                <th style="width: 75px;">Kode MK</th>
                <th>Mata Kuliah</th>
                <th style="width: 45px;">SKS</th>
                <th style="width: 60px;">Kelas</th>
                <th style="width: 85px;">Ruang</th>
                <th>Dosen Pengampu</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($jadwalPerHari as $hari => $list)
                @foreach($list as $item)
                @php
                    $jamMulai = \Carbon\Carbon::parse($item['jadwal']->jam_mulai)->format('H:i');
                    $jamSelesai = \Carbon\Carbon::parse($item['jadwal']->jam_selesai)->format('H:i');
                @endphp
                <tr>
                    <td class="center">{{ $no++ }}</td>
                    <td class="center"><strong>{{ $hari }}</strong></td>
                    <td class="center">{{ $jamMulai }} - {{ $jamSelesai }}</td>
                    <td class="center font-mono">{{ $item['kelas']->mataKuliah->kode_mk }}</td>
                    <td>{{ $item['kelas']->mataKuliah->nama_mk }}</td>
                    <td class="center">{{ $item['kelas']->mataKuliah->sks }}</td>
                    <td class="center">{{ $item['kelas']->nama_kelas }}</td>
                    <td class="center">{{ $item['jadwal']->ruangan ?? '-' }}</td>
                    <td>{{ $item['kelas']->dosen->user->name ?? '-' }}</td>
                </tr>
                @endforeach
            @empty
            <tr>
                <td colspan="9" class="center" style="padding: 20px;">Belum ada jadwal perkuliahan yang disetujui untuk semester ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right; font-weight: bold; padding-right: 12px;">Total SKS Terjadwal</td>
                <td class="center">{{ $totalSks }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <div class="signature">
            Mengetahui,<br>
            Dosen Pembimbing Akademik
            <div class="line">
                {{ $mahasiswa->dosenPa->user->name ?? '________________________' }}<br>
                <span style="font-weight: normal; font-size: 9pt;">NIDN. {{ $mahasiswa->dosenPa->nidn ?? '___________________' }}</span>
            </div>
        </div>
        <div class="signature">
            Tegal, {{ now()->locale('id')->isoFormat('D MMMM Y') }}<br>
            Mahasiswa Bersangkutan
            <div class="line">
                {{ $mahasiswa->user->name }}<br>
                <span style="font-weight: normal; font-size: 9pt;">NIM. {{ $mahasiswa->nim }}</span>
            </div>
        </div>
    </div>
</body>
</html>
