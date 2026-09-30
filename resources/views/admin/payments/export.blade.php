<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pembayaran - STIT Mambaul Hikmah</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 10mm 10mm 10mm 10mm;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #111;
            font-size: 7.5pt;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* ===== HEADER ===== */
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
            width: 60px;
            text-align: left;
        }
        .logo-img {
            width: 50px;
            height: 58px;
        }
        .header-text {
            padding-left: 8px;
        }
        .header-title-main {
            font-size: 11pt;
            font-weight: bold;
            color: #111;
            margin: 0 0 1px 0;
            letter-spacing: 0.3px;
        }
        .header-title-sub {
            font-size: 8pt;
            font-weight: bold;
            color: #111;
            margin: 0 0 1px 0;
        }
        .header-title-contact {
            font-size: 6.5pt;
            color: #333;
            margin: 0;
        }
        .header-divider {
            border: none;
            border-top: 2px solid #000;
            margin-top: 4px;
            margin-bottom: 3px;
        }
        .header-divider-thin {
            border: none;
            border-top: 0.5px solid #000;
            margin-top: 1px;
            margin-bottom: 6px;
        }

        /* ===== REPORT TITLE ===== */
        .report-title-row {
            width: 100%;
            text-align: center;
            margin-bottom: 6px;
        }
        .report-title-text {
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-decoration: underline;
        }

        /* ===== META INFO ===== */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 7.5pt;
        }
        .meta-table td {
            padding: 1px 0;
            vertical-align: top;
            color: #111;
        }

        /* ===== SUMMARY ===== */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 7.5pt;
        }
        .summary-table td {
            border: 0.75px solid #555;
            padding: 4px 6px;
            text-align: center;
            vertical-align: top;
        }
        .summary-table th {
            border: 0.75px solid #555;
            padding: 4px 6px;
            font-weight: bold;
            background-color: #f0f0f0;
            text-align: center;
            font-size: 7pt;
        }

        /* ===== DATA TABLE ===== */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 10px;
        }
        table.data-table thead th {
            background-color: #e8e8e8;
            border: 0.75px solid #333;
            padding: 3px 4px;
            font-size: 6.5pt;
            font-weight: bold;
            text-align: center;
            color: #111;
        }
        table.data-table tbody td {
            border: 0.75px solid #555;
            padding: 2px 4px;
            vertical-align: middle;
            color: #111;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        table.data-table tfoot td {
            border: 0.75px solid #333;
            padding: 4px 5px;
            font-weight: bold;
            background-color: #e8e8e8;
            font-size: 7.5pt;
        }

        /* ===== TEXT HELPERS ===== */
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .font-bold   { font-weight: bold; }
        .text-sm     { font-size: 6.5pt; color: #444; }

        /* ===== SIGNATURE ===== */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        .sig-table td {
            vertical-align: top;
            font-size: 7.5pt;
            color: #111;
        }
        .sig-name {
            font-weight: bold;
            font-size: 8pt;
            border-bottom: 0.75px solid #111;
            display: inline-block;
            margin-top: 2px;
            padding-bottom: 1px;
        }

        /* ===== FOOTER ===== */
        .footer-line {
            border: none;
            border-top: 0.5px dashed #999;
            margin-top: 10px;
            margin-bottom: 3px;
        }
        .footer-note {
            font-size: 6pt;
            color: #777;
        }

        @media print {
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body>

    {{-- ===== KOP SURAT (sama seperti receipt) ===== --}}
    <table class="header-table">
        <tr>
            <td class="logo-td">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
                @endif
            </td>
            <td class="header-text">
                <div class="header-title-main">RIWAYAT PEMBAYARAN MAHASISWA</div>
                <div class="header-title-sub">Sekolah Tinggi Ilmu Tarbiyah (STIT) Mambaul Hikmah</div>
                <div class="header-title-contact">Alamat : Jl. Raya Tegalwangi RT 13 / RW 05 Tegalwangi - Talang - Tegal 52193 &nbsp;|&nbsp; Telp : 0813-9375-0612 &nbsp;|&nbsp; Website : www.stitmambaulhikmah.ac.id</div>
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>
    <div class="header-divider-thin"></div>

    {{-- ===== JUDUL LAPORAN ===== --}}
    <div class="report-title-row">
        <span class="report-title-text">Laporan Rekapitulasi Riwayat Pembayaran</span>
    </div>

    {{-- ===== META INFO ===== --}}
    @php
        $sortedByDate = $payments->filter(fn($p) => $p->payment_date)->sortBy('payment_date');
        $firstDate    = $sortedByDate->first()?->payment_date;
        $lastDate     = $sortedByDate->last()?->payment_date;

        $totalTagihan = $payments->sum('amount');
        $totalDibayar = $payments->sum('paid_amount');

        $cashTotal     = $payments->filter(fn($p) => strtolower($p->payment_method ?? '') === 'tunai')->sum('paid_amount');
        $transferTotal = $payments->filter(fn($p) => strtolower($p->payment_method ?? '') === 'transfer')->sum('paid_amount');
        $vaTotal       = $payments->filter(fn($p) => in_array(strtolower($p->payment_method ?? ''), ['virtual account', 'va', 'midtrans']))->sum('paid_amount');
        $cashCount     = $payments->filter(fn($p) => strtolower($p->payment_method ?? '') === 'tunai')->count();
        $transferCount = $payments->filter(fn($p) => strtolower($p->payment_method ?? '') === 'transfer')->count();
        $vaCount       = $payments->filter(fn($p) => in_array(strtolower($p->payment_method ?? ''), ['virtual account', 'va', 'midtrans']))->count();
    @endphp

    <table class="meta-table">
        <tr>
            <td style="width:12%;">Dicetak Oleh</td>
            <td style="width:38%;">: <strong>{{ $user->name }}</strong> ({{ strtoupper($user->role ?? 'Admin') }})</td>
            <td style="width:12%;">Tanggal Cetak</td>
            <td style="width:38%;">: <strong>{{ now()->translatedFormat('d F Y') }}</strong>, Pukul {{ now()->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td>Jumlah Transaksi</td>
            <td>: <strong>{{ $payments->count() }} transaksi</strong></td>
            <td>Periode Data</td>
            <td>:
                @if($firstDate)
                    <strong>{{ $firstDate->format('d/m/Y') }}</strong>
                    @if($lastDate && $firstDate->format('Y-m-d') !== $lastDate->format('Y-m-d'))
                        s/d <strong>{{ $lastDate->format('d/m/Y') }}</strong>
                    @endif
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    {{-- ===== RINGKASAN PER METODE ===== --}}
    <table class="summary-table">
        <thead>
            <tr>
                <th>Total Dibayar</th>
                <th>Cash / Tunai</th>
                <th>Transfer Bank</th>
                <th>Virtual Account</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($cashTotal, 0, ',', '.') }} <br><span class="text-sm">({{ $cashCount }} transaksi)</span></td>
                <td>Rp {{ number_format($transferTotal, 0, ',', '.') }} <br><span class="text-sm">({{ $transferCount }} transaksi)</span></td>
                <td>Rp {{ number_format($vaTotal, 0, ',', '.') }} <br><span class="text-sm">({{ $vaCount }} transaksi)</span></td>
            </tr>
        </tbody>
    </table>

    {{-- ===== TABEL DATA (sort by payment_date desc) ===== --}}
    @php $no = 1; $sumTagihan = 0; $sumDibayar = 0; @endphp

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:5%;">Tgl Bayar</th>
                <th style="width:5%;">Invoice</th>
                <th style="width:5%;">NIM / Prodi</th>
                <th style="width:5%;">Nama Mahasiswa</th>
                <th style="width:5%;">Jenis Pembayaran</th>
                <th style="width:5%;">Smt</th>
                <th style="width:5%;">Tagihan (Rp)</th>
                <th style="width:5%;">Dibayar (Rp)</th>
                <th style="width:5%;">Status</th>
                <th style="width:5%;">Metode</th>
                <th style="width:5%;">Diterima Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $p)
            @php
                $sumTagihan += $p->amount;
                $sumDibayar += $p->paid_amount;

                $statusLabel = match($p->status) {
                    'paid'      => 'LUNAS',
                    'partial'   => 'CICILAN',
                    'cancelled' => 'BATAL',
                    default     => 'BELUM',
                };
                $methodLower = strtolower($p->payment_method ?? '');
                if ($methodLower === 'tunai') {
                    $methodLabel = 'Cash/Tunai';
                } elseif ($methodLower === 'transfer') {
                    $methodLabel = 'Transfer';
                } elseif (in_array($methodLower, ['virtual account', 'va', 'midtrans'])) {
                    $methodLabel = 'VA Midtrans';
                } elseif ($methodLower === 'qris') {
                    $methodLabel = 'QRIS';
                } else {
                    $methodLabel = $p->payment_method ?? '-';
                }
            @endphp
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td class="text-center" style="font-size: 6.5pt;">{{ $p->payment_date ? $p->payment_date->format('d/m/Y') : '-' }}</td>
                <td style="font-family: 'Courier New', monospace; font-size: 6pt;">{{ $p->invoice_number }}</td>
                <td style="font-size: 6.5pt;">
                    <span style="font-family: 'Courier New', monospace;">{{ $p->mahasiswa?->nim ?? '-' }}</span>
                    <br><span class="text-sm">{{ $p->mahasiswa?->prodi?->nama ?? '' }}</span>
                </td>
                <td>
                    <span class="font-bold" style="font-size: 7pt;">{{ $p->mahasiswa?->user?->name ?? '-' }}</span>
                </td>
                <td style="font-size: 7pt;">{{ $p->paymentType?->name ?? '-' }}</td>
                <td class="text-center">{{ $p->paymentType?->semester ?? '-' }}</td>
                <td class="text-right" style="font-size: 7pt;">{{ number_format($p->amount, 0, ',', '.') }}</td>
                <td class="text-right font-bold" style="font-size: 7pt;">{{ number_format($p->paid_amount, 0, ',', '.') }}</td>
                <td class="text-center font-bold" style="font-size: 6.5pt;">{{ $statusLabel }}</td>
                <td class="text-center" style="font-size: 6.5pt;">{{ $methodLabel }}</td>
                <td style="font-size: 6.5pt;">
                    @if($p->confirmedBy)
                        <span class="font-bold">{{ $p->confirmedBy->name }}</span>
                        <br><span class="text-sm">{{ strtoupper($p->confirmedBy->role ?? '') }}</span>
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding: 15px; font-style: italic; color: #555;">
                    Tidak ada data riwayat pembayaran.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">TOTAL KESELURUHAN :</td>
                <td class="text-right" style="font-size: 7pt;">Rp {{ number_format($sumTagihan, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: 7pt;">Rp {{ number_format($sumDibayar, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    {{-- ===== TANDA TANGAN ===== --}}
    <table class="sig-table">
        <tr>
            <td style="width:70%; font-size: 6.5pt; color: #555; font-style: italic;">

            <td style="width:30%; text-align: center;">
                <div>Tegal, {{ now()->translatedFormat('d F Y') }}</div>
                <div style="margin-top: 2px; margin-bottom: 45px;">Bagian Keuangan &amp; Administrasi,</div>
                <div>
                    <span class="sig-name">{{ $user->name }}</span>
                </div>
                <div style="font-size: 6.5pt; color: #444; margin-top: 3px;">NIP/NIDN. ..................................</div>
            </td>
        </tr>
    </table>

    <div class="footer-line"></div>
    <div class="footer-note">Dicetak oleh: {{ $user->name }} &mdash; {{ now()->format('d/m/Y H:i') }} WIB &mdash; SIAKADMAWA STIT Mambaul Hikmah</div>

</body>
</html>
