<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Pembayaran Mahasiswa - STIT Mambaul Hikmah</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 10mm 15mm 10mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111;
            background: #fff;
            margin: 0;
            padding: 10px;
            font-size: 11pt;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-img {
            width: 75px;
            height: auto;
        }
        .inst-title {
            text-align: center;
        }
        .inst-title h3 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .inst-title h2 {
            margin: 2px 0;
            font-size: 16pt;
            font-weight: bold;
            color: #1a5632;
        }
        .inst-title p {
            margin: 1px 0;
            font-size: 9pt;
            color: #333;
        }
        .report-title {
            text-align: center;
            margin: 12px 0 8px 0;
        }
        .report-title h4 {
            margin: 0;
            font-size: 12pt;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .report-meta {
            margin-bottom: 10px;
            font-size: 9pt;
            display: flex;
            justify-content: space-between;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #333;
            padding: 5px 6px;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .total-row {
            background-color: #fafafa;
            font-weight: bold;
        }
        .sig-section {
            margin-top: 25px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 250px;
            text-align: center;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
        .btn-print {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #16a34a;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-family: sans-serif;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

    <!-- Kop Surat -->
    <table class="header-table">
        <tr>
            <td style="width: 85px; text-align: center;">
                <img src="{{ asset('logo.PNG') }}" alt="Logo" class="logo-img" onerror="this.src='/logo.PNG'">
            </td>
            <td class="inst-title">
                <h3>SEKOLAH TINGGI ILMU TARBIYAH (STIT)</h3>
                <h2>MAMBAUL HIKMAH</h2>
                <p>Jl. Pesantren No. 01, Mambaul Hikmah, Tegal, Jawa Tengah</p>
                <p>Website: stitmambaulhikmah.ac.id | Email: info@stitmambaulhikmah.ac.id</p>
            </td>
            <td style="width: 85px;"></td>
        </tr>
    </table>

    <div class="report-title">
        <h4>Laporan Rekapitulasi Pembayaran Mahasiswa</h4>
    </div>

    <div class="report-meta">
        <div>Dicetak Oleh: <strong>{{ $user->name }}</strong> ({{ strtoupper($user->role) }})</div>
        <div>Tanggal Cetak: <strong>{{ now()->translatedFormat('d F Y H:i') }}</strong> WIB</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Invoice</th>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Program Studi</th>
                <th>Jenis Pembayaran</th>
                <th>Semester</th>
                <th>Tagihan (Rp)</th>
                <th>Dibayar (Rp)</th>
                <th>Status</th>
                <th>Tgl Bayar</th>
                <th>Metode</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumTagihan = 0;
                $sumDibayar = 0;
            @endphp
            @forelse($payments as $index => $p)
            @php
                $sumTagihan += $p->amount;
                $sumDibayar += $p->paid_amount;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-family: monospace;">{{ $p->invoice_number }}</td>
                <td class="text-center font-mono">{{ $p->mahasiswa->nim }}</td>
                <td>{{ $p->mahasiswa->user->name ?? '-' }}</td>
                <td>{{ $p->mahasiswa->prodi->nama ?? '-' }}</td>
                <td>{{ $p->paymentType->name ?? '-' }}</td>
                <td class="text-center">{{ $p->paymentType->semester ?? '-' }}</td>
                <td class="text-right">{{ number_format($p->amount, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($p->paid_amount, 0, ',', '.') }}</td>
                <td class="text-center font-bold">
                    {{ $p->status === 'paid' ? 'LUNAS' : ($p->status === 'cancelled' ? 'BATAL' : 'BELUM LUNAS') }}
                </td>
                <td class="text-center">{{ $p->payment_date ? $p->payment_date->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $p->payment_method ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding: 15px;">Tidak ada data pembayaran.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-right">Rp {{ number_format($sumTagihan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumDibayar, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div class="sig-section">
        <div class="sig-box">
            <p>Tegal, {{ now()->translatedFormat('d F Y') }}</p>
            <p style="margin-bottom: 50px;">Bagian Keuangan & Administrasi,</p>
            <p class="font-bold"><u>{{ $user->name }}</u></p>
            <p>NIP/NIDN. ..................................</p>
        </div>
    </div>
</body>
</html>
