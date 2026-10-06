<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran - {{ $nomorBukti ?? ($payment?->invoice_number ?? '') }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 0;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #222;
            font-size: 8pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .receipt-container {
            width: 21.59cm;
            max-width: 100%;
            height: 11cm;
            box-sizing: border-box;
            padding: 5mm 8mm 3mm 8mm;
            position: relative;
            background-color: #fff;
            overflow: hidden;
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
            width: 52px;
            text-align: left;
        }
        .logo-img {
            width: 44px;
            height: 50px;
        }
        .header-text {
            padding-left: 6px;
            text-align: left;
        }
        .header-title-main {
            font-size: 10pt;
            font-weight: bold;
            color: #222;
            margin: 0 0 1px 0;
            letter-spacing: 0.2px;
        }
        .header-title-inst {
            font-size: 8pt;
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
            margin-top: 2px;
            margin-bottom: 5px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .meta-table td {
            padding: 1px 0;
            font-size: 8pt;
            vertical-align: top;
            color: #222;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .items-table th {
            background-color: #f5f5f5;
            border: 0.75px solid #000;
            padding: 3px 5px;
            font-size: 8pt;
            font-weight: bold;
            color: #222;
            text-align: center;
        }
        .items-table td {
            border: 0.75px solid #000;
            padding: 3px 5px;
            font-size: 8pt;
            color: #222;
        }
        .terbilang-text {
            font-size: 8pt;
            font-style: italic;
            color: #222;
            margin: 4px 0 6px 0;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .sig-table td {
            vertical-align: top;
            font-size: 8pt;
        }
        .qr-img {
            width: 48px;
            height: 48px;
            display: block;
            margin: 2px 0;
        }
        .sig-name {
            font-weight: bold;
            font-size: 8pt;
            color: #222;
            border-bottom: 0.75px solid #222;
            display: inline-block;
            margin-top: 2px;
            padding-bottom: 1px;
        }
        .sig-unit {
            font-size: 6.5pt;
            color: #333;
            margin-top: 2px;
        }
        .footer-divider {
            border-top: 0.75px dashed #ccc;
            margin-top: 6px;
            padding-top: 2px;
        }
        .footer-note {
            font-size: 6pt;
            color: #777;
        }
        .cut-guide {
            width: 21.59cm;
            max-width: 100%;
            box-sizing: border-box;
            padding: 0 8mm;
            margin-top: 2px;
        }
        .cut-line {
            border-top: 1px dashed #aaa;
            height: 1px;
            width: 100%;
        }
        .cut-text {
            font-size: 6.5pt;
            color: #888;
            text-align: right;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
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
                    <div class="header-title-inst">{{ $institutionName ?? 'Sekolah Tinggi Ilmu Tarbiyah (STIT) Mambaul Hikmah' }}</div>
                    <div class="header-title-contact">Alamat : {{ $institutionAddress ?? 'Jl. Raya Tegalwangi RT 13 / RW 05 Tegalwangi - Talang - Tegal 52193' }} | Telp : 0813-9375-0612 | Website : www.stitmambaulhikmah.ac.id</div>
                </td>
            </tr>
        </table>
        <div class="header-divider"></div>

        <!-- Info Mahasiswa & Bukti Bayar -->
        <table class="meta-table">
            <tr>
                <td style="width: 14%;">Nomor Bukti</td>
                <td style="width: 38%;">: <strong>{{ $nomorBukti ?? ($payment?->invoice_number ?? '-') }}</strong></td>
                <td style="width: 10%;">NIM</td>
                <td style="width: 38%;">: <strong>{{ $mahasiswa->nim }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ $tanggalPembayaran ?? ($payment?->payment_date ? $payment->payment_date->translatedFormat('d F Y') : ($payment?->confirmed_at ? $payment->confirmed_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y'))) }}</td>
                <td>Nama</td>
                <td>: <strong>{{ strtoupper($mahasiswa->user->name) }}</strong></td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>: {{ $mahasiswa->prodi?->jenjang ?? 'S1' }} - {{ $mahasiswa->prodi?->nama }}</td>
                <td>Angkatan</td>
                <td>: {{ $mahasiswa->angkatan ?? '-' }}</td>
            </tr>
        </table>

        @php
            $receiptItems = $items ?? [
                [
                    'name' => $payment->paymentType->name,
                    'amount' => $payment->amount,
                    'paid_amount' => $payment->paid_amount > 0 ? $payment->paid_amount : $payment->amount,
                    'notes' => $payment->notes && !str_starts_with($payment->notes, 'Kewajiban') ? $payment->notes : '-',
                    'remaining_amount' => $payment->remaining_amount,
                    'status' => $payment->isPaid() ? 'LUNAS' : strtoupper($payment->status),
                ]
            ];
            $totalDibayarkan = $totalPaid ?? ($payment->paid_amount > 0 ? $payment->paid_amount : $payment->amount);
            $totalKekurangan = $totalRemaining ?? ($payment ? $payment->remaining_amount : 0);
        @endphp

        <!-- Tabel Rincian Pembayaran -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 38%;">Nama Pembayaran</th>
                    <th style="width: 15%;">Dibayarkan (Rp.)</th>
                    <th style="width: 20%;">Keterangan</th>
                    <th style="width: 10%;">Sisa Tagihan</th>
                    <th style="width: 12%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receiptItems as $index => $item)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ strtoupper($item['name']) }} (Rp {{ number_format($item['amount'], 0, ',', '.') }})</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($item['paid_amount'], 0, ',', '.') }}</td>
                        <td style="text-align: center;">{{ !empty($item['notes']) && !str_starts_with($item['notes'], 'Kewajiban') ? $item['notes'] : '-' }}</td>
                        <td style="text-align: right;">{{ number_format($item['remaining_amount'], 0, ',', '.') }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ strtoupper($item['status']) }}</td>
                    </tr>
                @endforeach
            </tbody>
            @if(count($receiptItems) > 1)
                <tfoot>
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                        <td colspan="2" style="text-align: right; font-weight: bold; padding: 3px 5px;">TOTAL:</td>
                        <td style="text-align: right; font-weight: bold; padding: 3px 5px;">{{ number_format($totalDibayarkan, 0, ',', '.') }}</td>
                        <td></td>
                        <td style="text-align: right; font-weight: bold; padding: 3px 5px;">{{ number_format($totalKekurangan, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
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
                            <td style="border: none; padding: 0 0 4px 0; font-size: 8pt; color: #222;">Yang Menerima,</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 0 0 4px 0;">
                                @if(!empty($qrBase64))
                                    <img src="{{ $qrBase64 }}" class="qr-img" alt="QR Verifikasi">
                                @else
                                    <div style="height: 48px;"></div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 0 0 2px 0;">
                                <span class="sig-name">{{ $confirmedByName ?? ($payment?->confirmedBy?->name ?? 'Muhammad Ziidan Amani') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 0; font-size: 6.5pt; color: #333;">
                                Bagian Keuangan {{ $institutionShortName ?? 'STIT Mambaul Hikmah' }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; text-align: right; padding-right: 15px; vertical-align: top;">
                    <div style="font-size: 8pt; color: #222;">{{ $kota ?? 'Tegal' }}, {{ $tanggalCetak ?? now()->translatedFormat('d F Y') }}</div>
                </td>
            </tr>
        </table>

        <!-- Footer Note -->
        <div class="footer-divider"></div>
        <div class="footer-note">
            Catatan: Simpanlah kwitansi ini sebagai bukti pembayaran yang sah. Dicetak otomatis oleh SIAKADMAWA.
        </div>
    </div>

    <!-- Garis panduan potong kertas kwitansi (21,59 cm x 11 cm) -->
    <div class="cut-guide">
        <div class="cut-line"></div>
        <div class="cut-text">&#9986; Garis Potong Kwitansi (21,59 cm x 11 cm)</div>
    </div>
</body>
</html>
