<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran - {{ $payment->invoice_number }}</title>
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
            width: 80px;
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
            color: #1a5632;
        }
        .inst-title p {
            margin: 1px 0;
            font-size: 9pt;
            color: #333;
        }
        .receipt-title {
            text-align: center;
            margin: 15px 0;
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
            color: #555;
        }
        table.content-table {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }
        table.content-table td {
            padding: 6px 4px;
            vertical-align: top;
        }
        table.content-table td.label {
            width: 180px;
            color: #333;
        }
        table.content-table td.colon {
            width: 15px;
            text-align: center;
        }
        .amount-box {
            margin: 15px 0;
            padding: 12px 15px;
            border: 2px solid #1a5632;
            background-color: #f4fbf7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .amount-val {
            font-size: 15pt;
            font-weight: bold;
            color: #1a5632;
        }
        .amount-status {
            font-weight: bold;
            text-transform: uppercase;
            color: #16a34a;
            border: 2px solid #16a34a;
            padding: 4px 12px;
            border-radius: 4px;
            letter-spacing: 1px;
        }
        .sig-section {
            margin-top: 30px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 220px;
            text-align: center;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #16a34a;
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-family: sans-serif;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ Cetak Kwitansi</button>

    <!-- Kop Surat -->
    <table class="header-table">
        <tr>
            <td style="width: 90px; text-align: center;">
                <img src="{{ asset('logo.PNG') }}" alt="Logo" class="logo-img" onerror="this.src='/logo.PNG'">
            </td>
            <td class="inst-title">
                <h3>SEKOLAH TINGGI ILMU TARBIYAH (STIT)</h3>
                <h2>MAMBAUL HIKMAH</h2>
                <p>Jl. Pesantren No. 01, Mambaul Hikmah, Tegal, Jawa Tengah</p>
                <p>Website: stitmambaulhikmah.ac.id | Email: info@stitmambaulhikmah.ac.id</p>
            </td>
            <td style="width: 90px;"></td>
        </tr>
    </table>

    <div class="receipt-title">
        <h4>BUKTI PEMBAYARAN AKADEMIK (KWITANSI)</h4>
        <p>NO. INVOICE: {{ $payment->invoice_number }}</p>
    </div>

    <table class="content-table">
        <tr>
            <td class="label">Telah Diterima Dari</td>
            <td class="colon">:</td>
            <td><strong>{{ $payment->mahasiswa->user->name }}</strong></td>
        </tr>
        <tr>
            <td class="label">Nomor Induk Mahasiswa (NIM)</td>
            <td class="colon">:</td>
            <td style="font-family: monospace;">{{ $payment->mahasiswa->nim }}</td>
        </tr>
        <tr>
            <td class="label">Program Studi / Fakultas</td>
            <td class="colon">:</td>
            <td>{{ $payment->mahasiswa->prodi->nama }} / {{ $payment->mahasiswa->prodi->fakultas->nama }}</td>
        </tr>
        <tr>
            <td class="label">Tahun Masuk / Angkatan</td>
            <td class="colon">:</td>
            <td>{{ $payment->mahasiswa->angkatan }}</td>
        </tr>
        <tr>
            <td class="label">Untuk Pembayaran</td>
            <td class="colon">:</td>
            <td><strong>{{ $payment->paymentType->name }}</strong> @if($payment->paymentType->semester) (Semester {{ $payment->paymentType->semester }}) @endif</td>
        </tr>
        <tr>
            <td class="label">Metode Pembayaran</td>
            <td class="colon">:</td>
            <td>{{ $payment->payment_method ?? 'Tunai' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Pelunasan</td>
            <td class="colon">:</td>
            <td>{{ $payment->payment_date ? $payment->payment_date->translatedFormat('d F Y') : ($payment->confirmed_at ? $payment->confirmed_at->translatedFormat('d F Y') : '-') }}</td>
        </tr>
        @if($payment->notes)
        <tr>
            <td class="label">Keterangan / Catatan</td>
            <td class="colon">:</td>
            <td>{{ $payment->notes }}</td>
        </tr>
        @endif
    </table>

    <div class="amount-box">
        <div>
            <div style="font-size: 9pt; color: #555; text-transform: uppercase;">Jumlah Uang:</div>
            <div class="amount-val">Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}</div>
        </div>
        <div class="amount-status">
            LUNAS
        </div>
    </div>

    <div class="sig-section">
        <div class="sig-box">
            <p>Mahasiswa Bersangkutan,</p>
            <div style="height: 55px;"></div>
            <p style="font-weight: bold;">{{ $payment->mahasiswa->user->name }}</p>
            <p style="font-family: monospace; font-size: 9pt;">NIM. {{ $payment->mahasiswa->nim }}</p>
        </div>
        <div class="sig-box">
            <p>Tegal, {{ $payment->payment_date ? $payment->payment_date->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</p>
            <p>Bagian Administrasi & Keuangan,</p>
            <div style="height: 40px;"></div>
            <p style="font-weight: bold;">{{ $payment->confirmedBy->name ?? 'Bendahara STIT MH' }}</p>
            <p style="font-size: 9pt;">STIT Mambaul Hikmah</p>
        </div>
    </div>
</body>
</html>
