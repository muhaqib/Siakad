<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran Resmi - {{ $payment->invoice_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111;
            background: #fff;
            margin: 0;
            padding: 15px;
            font-size: 11pt;
            line-height: 1.4;
        }
        .no-print-bar {
            background: #1e293b;
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 14px;
        }
        .btn-print {
            background: #059669;
            color: #fff;
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-back {
            background: #475569;
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
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
        }
        .inst-title h2 {
            margin: 2px 0;
            font-size: 16pt;
            font-weight: bold;
            color: #065f46;
        }
        .inst-title p {
            margin: 1px 0;
            font-size: 9pt;
            color: #333;
        }
        .receipt-title {
            text-align: center;
            margin: 15px 0 10px 0;
        }
        .receipt-title h4 {
            margin: 0;
            font-size: 14pt;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .receipt-title p {
            margin: 3px 0 0 0;
            font-family: monospace;
            font-size: 10pt;
            color: #444;
        }
        table.content-table {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }
        table.content-table td {
            padding: 5px 4px;
            vertical-align: top;
        }
        table.content-table td.label {
            width: 190px;
            color: #333;
        }
        table.content-table td.colon {
            width: 15px;
            text-align: center;
        }
        .amount-box {
            margin: 15px 0;
            padding: 12px 18px;
            border: 2px solid #065f46;
            background-color: #f0fdf4;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .amount-val {
            font-size: 15pt;
            font-weight: bold;
            color: #065f46;
        }
        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10pt;
            text-transform: uppercase;
        }
        .badge-paid {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        .badge-partial {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
        }
        .signatures {
            margin-top: 30px;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            text-align: center;
            width: 220px;
        }
        .sig-space {
            height: 60px;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10pt;
        }
        .history-table th, .history-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        .history-table th {
            background-color: #f8fafc;
            font-weight: bold;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>Kuitansi Resmi Keuangan STIT Mambaul Hikmah</strong> &bull; Invoice: {{ $payment->invoice_number }}
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn-back">&larr; Kembali ke Detail</a>
            <button onclick="window.print()" class="btn-print">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Kuitansi (Print)
            </button>
        </div>
    </div>

    <!-- Kop Surat -->
    <table class="header-table">
        <tr>
            <td style="width: 80px; text-align: center;">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img" onerror="this.style.display='none'">
            </td>
            <td>
                <div class="inst-title">
                    <h3>YAYASAN PENDIDIKAN MAMBAUL HIKMAH</h3>
                    <h2>SEKOLAH TINGGI ILMU TARBIYAH (STIT) MAMBAUL HIKMAH</h2>
                    <p>BAGIAN KEUANGAN DAN ADMINISTRASI AKADEMIK</p>
                    <p>Jl. Pesantren No. 01, Mambaul Hikmah &bull; Email: finance@stit-mambaulhikmah.ac.id</p>
                </div>
            </td>
        </tr>
    </table>

    <div class="receipt-title">
        <h4>BUKTI KUITANSI PEMBAYARAN MAHASISWA</h4>
        <p>No. Kuitansi: {{ $payment->invoice_number }}</p>
    </div>

    <table class="content-table">
        <tr>
            <td class="label">Telah Terima Dari</td>
            <td class="colon">:</td>
            <td><strong>{{ $payment->mahasiswa->user->name ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Nomor Induk Mahasiswa (NIM)</td>
            <td class="colon">:</td>
            <td><span style="font-family: monospace; font-weight: bold;">{{ $payment->mahasiswa->nim }}</span></td>
        </tr>
        <tr>
            <td class="label">Program Studi / Fakultas</td>
            <td class="colon">:</td>
            <td>{{ $payment->mahasiswa->prodi->nama ?? '-' }} &bull; {{ $payment->mahasiswa->prodi->fakultas->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Angkatan / Status</td>
            <td class="colon">:</td>
            <td>Tahun {{ $payment->mahasiswa->angkatan ?? '-' }} &bull; Mahasiswa {{ ucfirst($payment->mahasiswa->status ?? 'Aktif') }}</td>
        </tr>
        <tr>
            <td class="label">Untuk Pembayaran</td>
            <td class="colon">:</td>
            <td>
                <strong>{{ $payment->paymentType->name }}</strong>
                @if($payment->paymentType->semester)
                    (Semester {{ $payment->paymentType->semester }})
                @endif
                &bull; Tahun Akademik {{ $payment->tahunAkademik->nama ?? '-' }}
            </td>
        </tr>
        <tr>
            <td class="label">Metode & Tanggal Bayar</td>
            <td class="colon">:</td>
            <td>{{ $payment->payment_method ?? 'Tunai' }} &bull; {{ $payment->payment_date ? $payment->payment_date->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</td>
        </tr>
        @if($payment->notes)
        <tr>
            <td class="label">Catatan / Keterangan</td>
            <td class="colon">:</td>
            <td><em>{{ $payment->notes }}</em></td>
        </tr>
        @endif
    </table>

    <!-- Amount Summary Box -->
    <div class="amount-box">
        <div>
            <div style="font-size: 9pt; color: #555; text-transform: uppercase; letter-spacing: 0.5px;">Akumulasi Nominal Terbayar:</div>
            <div class="amount-val">Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}</div>
            <div style="font-size: 9pt; color: #555; margin-top: 3px;">
                Total Kewajiban: <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                @if($payment->remaining_amount > 0)
                    &bull; Sisa Tunggakan: <strong style="color: #b91c1c;">Rp {{ number_format($payment->remaining_amount, 0, ',', '.') }}</strong>
                @endif
            </div>
        </div>
        <div>
            @if($payment->isPaid())
                <span class="badge-status badge-paid">✓ LUNAS</span>
            @elseif($payment->isPartial())
                <span class="badge-status badge-partial">CICILAN (Sisa: Rp {{ number_format($payment->remaining_amount, 0, ',', '.') }})</span>
            @else
                <span class="badge-status" style="background: #f1f5f9; color: #334155;">BELUM LUNAS</span>
            @endif
        </div>
    </div>

    <!-- Riwayat Transaksi Setoran -->
    @if($payment->histories->count() > 0)
    <div style="margin-top: 15px;">
        <div style="font-weight: bold; font-size: 10pt; margin-bottom: 5px; color: #333;">Rincian Transaksi Setoran Pembayaran:</div>
        <table class="history-table">
            <thead>
                <tr>
                    <th style="width: 30px; text-align: center;">No</th>
                    <th>Tanggal & Waktu</th>
                    <th>Aksi Transaksi</th>
                    <th>Nominal Disetor</th>
                    <th>Petugas / Kasir</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment->histories as $idx => $h)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>{{ $h->created_at->translatedFormat('d/m/Y H:i') }}</td>
                    <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $h->action) }}</td>
                    <td style="font-weight: bold;">Rp {{ number_format($h->amount ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $h->performer->name ?? 'Sistem' }}</td>
                    <td style="font-size: 9pt; color: #444;">{{ $h->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig-box">
            <p style="margin: 0; font-size: 10pt;">Mahasiswa Pembayar,</p>
            <div class="sig-space"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $payment->mahasiswa->user->name ?? '-' }}</p>
            <p style="margin: 0; font-size: 9pt;">NIM: {{ $payment->mahasiswa->nim }}</p>
        </div>

        <div class="sig-box">
            <p style="margin: 0; font-size: 10pt;">Diverifikasi Petugas Keuangan,</p>
            <p style="margin: 2px 0 0 0; font-size: 9pt; color: #666;">{{ now()->translatedFormat('d F Y') }}</p>
            <div class="sig-space"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $payment->confirmedBy->name ?? ($user->name ?? 'Admin Keuangan') }}</p>
            <p style="margin: 0; font-size: 9pt;">Bagian Administrasi Keuangan</p>
        </div>
    </div>

</body>
</html>
