<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran - {{ $payment->invoice_number }}</title>
    <style>
        @page {
            size: a5 landscape;
            margin: 5mm 5mm 5mm 5mm;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #222;
            font-size: 8.2pt;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0;
        }
        .logo-td {
            width: 58px;
            text-align: left;
        }
        .logo-img {
            width: 52px;
            height: 52px;
        }
        .header-text {
            padding-left: 6px;
            text-align: left;
        }
        .header-title-main {
            font-size: 10.5pt;
            font-weight: bold;
            color: #222;
            margin: 0 0 1px 0;
            letter-spacing: 0.2px;
        }
        .header-title-year {
            font-size: 8.2pt;
            color: #222;
            margin: 0 0 1px 0;
        }
        .header-title-inst {
            font-size: 8.2pt;
            font-weight: bold;
            color: #222;
            margin: 0 0 1px 0;
        }
        .header-title-contact {
            font-size: 6pt;
            color: #333;
            margin: 0;
        }
        .header-divider {
            border-bottom: 1.5px solid #000;
            margin-top: 3px;
            margin-bottom: 7px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 7px;
        }
        .meta-table td {
            padding: 1.5px 0;
            font-size: 8.2pt;
            vertical-align: top;
            color: #222;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .items-table th {
            background-color: #f5f5f5;
            border: 0.75px solid #000;
            padding: 4px 5px;
            font-size: 8.2pt;
            font-weight: bold;
            color: #222;
            text-align: center;
        }
        .items-table td {
            border: 0.75px solid #000;
            padding: 4px 5px;
            font-size: 8.2pt;
            color: #222;
        }
        .terbilang-text {
            font-size: 8.2pt;
            font-style: italic;
            color: #222;
            margin: 5px 0 10px 0;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .sig-table td {
            vertical-align: top;
            font-size: 8.2pt;
        }
        .qr-img {
            width: 52px;
            height: 52px;
            display: block;
            margin: 4px 0;
        }
        .sig-name {
            font-weight: bold;
            font-size: 8.2pt;
            color: #222;
            border-bottom: 0.75px solid #222;
            display: inline-block;
            margin-top: 2px;
            padding-bottom: 1px;
        }
        .sig-unit {
            font-size: 6.8pt;
            color: #333;
            margin-top: 3px;
        }
        .footer-divider {
            border-top: 0.75px dashed #ccc;
            margin-top: 12px;
            padding-top: 3px;
        }
        .footer-note {
            font-size: 6pt;
            color: #777;
        }
    </style>
</head>
<body>
    <!-- Kop Kwitansi -->
    <table class="header-table">
        <tr>
            <td class="logo-td">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
                @endif
            </td>
            <td class="header-text">
                <div class="header-title-main">KWITANSI PEMBAYARAN MAHASISWA</div>
                <div class="header-title-year">TAHUN AKADEMIK {{ $tahunAkademik ?? ($payment->tahunAkademik ? $payment->tahunAkademik->tahun . ' ' . $payment->tahunAkademik->semester : '2026 / 2027 Akhir') }}</div>
                <div class="header-title-inst">{{ $institutionName ?? 'Pascasarjana Universitas Islam Internasional Darullughah Wadda\'wah' }}</div>
                <div class="header-title-contact">Alamat : {{ $institutionAddress ?? 'Jl. Raya KH Muhammad Barmawi' }} | WhatsApp : {{ $institutionPhone ?? '0852-3519-7238' }} | Website : www.stitmambaulhikmah.ac.id</div>
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>

    <!-- Info Mahasiswa & Bukti Bayar -->
    <table class="meta-table">
        <tr>
            <td style="width: 14%;">Nomor Bukti</td>
            <td style="width: 38%;">: <strong>{{ $payment->invoice_number }}</strong></td>
            <td style="width: 10%;">NIM</td>
            <td style="width: 38%;">: <strong>{{ $mahasiswa->nim }}</strong></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: {{ $tanggalPembayaran ?? ($payment->payment_date ? $payment->payment_date->translatedFormat('d F Y') : ($payment->confirmed_at ? $payment->confirmed_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y'))) }}</td>
            <td>Nama</td>
            <td>: <strong>{{ strtoupper($mahasiswa->user->name) }}</strong></td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td colspan="3">: {{ $mahasiswa->prodi?->jenjang ?? 'S1' }} - {{ $mahasiswa->prodi?->nama }}</td>
        </tr>
    </table>

    <!-- Tabel Rincian Pembayaran -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 38%;">Nama Pembayaran</th>
                <th style="width: 16%;">Dibayarkan (Rp.)</th>
                <th style="width: 11%;">Keterangan</th>
                <th style="width: 18%;">Kekurangan Bayar (Rp.)</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td>{{ strtoupper($payment->paymentType->name) }} (Rp {{ number_format($payment->amount, 0, ',', '.') }})</td>
                <td style="text-align: right;">{{ number_format($payment->paid_amount > 0 ? $payment->paid_amount : $payment->amount, 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ $payment->notes && !str_starts_with($payment->notes, 'Kewajiban') ? $payment->notes : '-' }}</td>
                <td style="text-align: right;">{{ number_format($payment->remaining_amount, 0, ',', '.') }}</td>
                <td style="text-align: center; font-weight: bold;">{{ strtoupper($payment->isPaid() ? 'LUNAS' : ($payment->status === 'partial' ? 'CICILAN' : $payment->status)) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Terbilang -->
    <div class="terbilang-text">
        Terbilang di bayar: # {{ $terbilang }} Rupiah #
    </div>

    <!-- Tanda Tangan & QR Verification -->
    <table class="sig-table">
        <tr>
            <td style="width: 50%;">
                <table style="border-collapse: collapse; margin-left: 55px;">
                    <tr>
                        <td style="border: none; padding: 0 0 6px 0; font-size: 8.2pt; color: #222;">Yang Menerima,</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 0 0 6px 0;">
                            @if(!empty($qrBase64))
                                <img src="{{ $qrBase64 }}" style="width: 52px; height: 52px; display: block;" alt="QR Verifikasi">
                            @else
                                <div style="height: 52px;"></div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 0 0 2px 0;">
                            <span class="sig-name">{{ $payment->confirmedBy?->name ?? ($user?->name ?? 'Dzulkifli R. Takuloe') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 0; font-size: 6.8pt; color: #333;">
                            Bagian Keuangan {{ $institutionShortName ?? 'Pascasarjana UII Dalwa' }}
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; text-align: right; padding-right: 15px; vertical-align: top;">
                <div style="font-size: 8.2pt; color: #222;">{{ $kota ?? 'Bangil' }}, {{ $tanggalCetak ?? now()->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- Footer Note -->
    <div class="footer-divider"></div>
    <div class="footer-note">
        Catatan: Simpanlah kwitansi ini sebagai bukti pembayaran yang sah. Dicetak otomatis oleh SIAKADPASCA v2.0.
    </div>
</body>
</html>
