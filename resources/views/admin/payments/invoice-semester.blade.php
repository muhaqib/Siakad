<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tagihan Semester {{ $semesterNumber }} - {{ $mahasiswa->nim }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 12mm 10mm 12mm;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #222;
            font-size: 9pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* ── Header ── */
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
            width: 62px;
            text-align: left;
        }
        .logo-img {
            width: 56px;
            height: 56px;
        }
        .header-text {
            padding-left: 8px;
            text-align: left;
        }
        .header-title-main {
            font-size: 12pt;
            font-weight: bold;
            color: #222;
            margin: 0 0 1px 0;
            letter-spacing: 0.3px;
        }
        .header-title-sub {
            font-size: 9pt;
            color: #222;
            margin: 0 0 1px 0;
        }
        .header-title-contact {
            font-size: 7pt;
            color: #333;
            margin: 0;
        }
        .header-divider {
            border-bottom: 2px solid #000;
            margin-top: 4px;
            margin-bottom: 10px;
        }

        /* ── Meta Info ── */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-table td {
            padding: 2px 0;
            font-size: 9pt;
            vertical-align: top;
            color: #222;
        }
        .meta-label {
            width: 22%;
            font-weight: normal;
        }
        .meta-value {
            width: 28%;
        }

        /* ── Title ── */
        .invoice-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 8px 0 4px 0;
            letter-spacing: 1px;
            text-decoration: underline;
        }
        .invoice-subtitle {
            text-align: center;
            font-size: 9pt;
            color: #444;
            margin-bottom: 14px;
        }

        /* ── Summary Box ── */
        .summary-box {
            border: 1px solid #333;
            padding: 8px 12px;
            margin-bottom: 14px;
            background: #fafafa;
        }
        .summary-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-box td {
            padding: 2.5px 0;
            font-size: 9pt;
        }
        .summary-label { width: 35%; }
        .summary-sep { width: 3%; text-align: center; }
        .summary-val { font-weight: bold; }

        /* ── Monthly Table ── */
        .monthly-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .monthly-table th {
            background-color: #e8e8e8;
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 9pt;
            font-weight: bold;
            color: #222;
            text-align: center;
        }
        .monthly-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 9pt;
            color: #222;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }

        .status-lunas {
            font-weight: bold;
            color: #000;
        }
        .status-belum {
            font-weight: bold;
            color: #000;
        }

        /* ── Footer / Signature ── */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .sig-table td {
            vertical-align: top;
            font-size: 9pt;
        }
        .sig-name {
            font-weight: bold;
            font-size: 9pt;
            color: #222;
            border-bottom: 1px solid #222;
            display: inline-block;
            margin-top: 2px;
            padding-bottom: 1px;
        }
        .footer-divider {
            border-top: 1px dashed #aaa;
            margin-top: 16px;
            padding-top: 4px;
        }
        .footer-note {
            font-size: 7pt;
            color: #777;
        }
    </style>
</head>
<body>
    <!-- Header Kop Surat -->
    <table class="header-table">
        <tr>
            <td class="logo-td">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
                @endif
            </td>
            <td class="header-text">
                <div class="header-title-main">TAGIHAN PEMBAYARAN PERKULIAHAN</div>
                <div class="header-title-main" style="font-size: 10pt;">{{ $institutionName ?? 'Sekolah Tinggi Ilmu Tarbiyah (STIT) Mambaul Hikmah' }}</div>
                <div class="header-title-contact">Alamat : {{ $institutionAddress ?? 'Jl. Raya Tegalwangi RT 13 / RW 05 Tegalwangi - Talang - Tegal 52193' }} | Telp : 0813-9375-0612 | Website : www.stitmambaulhikmah.ac.id</div>
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>

    <!-- Judul -->
    <div class="invoice-title">RINCIAN TAGIHAN SEMESTER {{ $semesterNumber }}</div>
    <div class="invoice-subtitle">Tahun Akademik {{ $tahunAkademik ?? '-' }} &mdash; Dicetak: {{ $tanggalCetak }}</div>

    <!-- Info Mahasiswa -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">NIM</td>
            <td class="meta-value">: <strong>{{ $mahasiswa->nim }}</strong></td>
            <td class="meta-label">Program Studi</td>
            <td class="meta-value">: {{ $mahasiswa->prodi?->jenjang ?? 'S1' }} - {{ $mahasiswa->prodi?->nama }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nama Mahasiswa</td>
            <td class="meta-value">: <strong>{{ strtoupper($mahasiswa->user->name) }}</strong></td>
            <td class="meta-label">Angkatan</td>
            <td class="meta-value">: {{ $mahasiswa->angkatan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Semester Berjalan</td>
            <td class="meta-value">: Semester {{ $semesterNumber }}</td>
            <td class="meta-label">No. Invoice</td>
            <td class="meta-value">: {{ $payment->invoice_number ?? '-' }}</td>
        </tr>
    </table>

    <!-- Ringkasan Pembayaran -->
    <div class="summary-box">
        <table>
            <tr>
                <td class="summary-label">Total Tagihan Semester {{ $semesterNumber }}</td>
                <td class="summary-sep">:</td>
                <td class="summary-val">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="summary-label">Skema Angsuran (6 Bulan)</td>
                <td class="summary-sep">:</td>
                <td class="summary-val">Rp {{ number_format($tagihanPerBulan, 0, ',', '.') }} / bulan</td>
            </tr>
            <tr>
                <td class="summary-label">Total Telah Dibayar</td>
                <td class="summary-sep">:</td>
                <td class="summary-val" style="color: #065f46;">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="summary-label">Sisa Kekurangan</td>
                <td class="summary-sep">:</td>
                <td class="summary-val" style="color: {{ $sisaKekurangan > 0 ? '#b91c1c' : '#065f46' }};">Rp {{ number_format($sisaKekurangan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="summary-label">Status Pembayaran</td>
                <td class="summary-sep">:</td>
                <td class="summary-val">
                    <strong>{{ $statusPembayaran }}</strong>
                    @php
                        $lunasCount = collect($monthlyBreakdown)->where('lunas', true)->count();
                    @endphp
                    ({{ $lunasCount }} dari {{ $jumlahBulan }} bulan lunas)
                </td>
            </tr>
        </table>
    </div>

    <!-- Tabel Tagihan Per Bulan -->
    <table class="monthly-table">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 24%;">Bulan</th>
                <th style="width: 20%;">Tagihan / Bulan (Rp)</th>
                <th style="width: 20%;">Telah Dibayar (Rp)</th>
                <th style="width: 18%;">Kekurangan (Rp)</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyBreakdown as $index => $month)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $month['label'] }}</td>
                    <td class="text-right">{{ number_format($month['tagihan'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($month['dibayar'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($month['kekurangan'], 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($month['lunas'])
                            <span style="font-weight: bold; color: #047857;">✓ LUNAS</span>
                        @elseif($month['dibayar'] > 0)
                            <span style="font-weight: bold; color: #1d4ed8;">SEBAGIAN</span>
                        @else
                            <span style="font-weight: bold; color: #b91c1c;">BELUM BAYAR</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f0f0;">
                <td colspan="2" class="text-center text-bold">TOTAL</td>
                <td class="text-right text-bold">{{ number_format($totalTagihan, 0, ',', '.') }}</td>
                <td class="text-right text-bold">{{ number_format($totalDibayar, 0, ',', '.') }}</td>
                <td class="text-right text-bold">{{ number_format($sisaKekurangan, 0, ',', '.') }}</td>
                <td class="text-center text-bold">{{ $statusPembayaran }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Terbilang -->
    @if($totalDibayar > 0)
        <div style="font-size: 8.5pt; font-style: italic; color: #222; margin: 4px 0 6px 0;">
            Terbilang telah dibayar: # {{ $terbilang }} Rupiah #
        </div>
    @endif

    <!-- Catatan -->
    <div style="font-size: 8pt; color: #333; margin: 10px 0 4px 0; padding: 6px 8px; border: 1px solid #ccc; background: #fefefe;">
        <strong>Keterangan:</strong><br>
        &bull; Semester ini dibagi menjadi {{ $jumlahBulan }} bulan angsuran ({{ $monthlyBreakdown[0]['label'] }} s.d. {{ $monthlyBreakdown[count($monthlyBreakdown) - 1]['label'] }}).<br>
        &bull; Pembayaran per bulan: <strong>Rp {{ number_format($tagihanPerBulan, 0, ',', '.') }}</strong>.<br>
        &bull; Pembayaran dianggap lunas per bulan jika akumulasi pembayaran mencukupi tagihan bulan tersebut.<br>
        @if($sisaKekurangan > 0)
            &bull; Mohon segera melunasi kekurangan tagihan sebesar <strong>Rp {{ number_format($sisaKekurangan, 0, ',', '.') }}</strong>.
        @else
            &bull; Seluruh tagihan semester ini telah <strong>LUNAS</strong>. Terima kasih.
        @endif
    </div>

    <!-- Tanda Tangan -->
    <table class="sig-table">
        <tr>
            <td style="width: 50%;">
                <div style="margin-left: 30px;">
                    <div>Mengetahui,</div>
                    <div style="height: 50px;"></div>
                    <div>
                        <span class="sig-name">{{ $adminName ?? 'Bagian Keuangan' }}</span>
                    </div>
                    <div style="font-size: 7.5pt; color: #333; margin-top: 2px;">
                        Bagian Keuangan {{ $institutionShortName ?? 'STIT Mambaul Hikmah' }}
                    </div>
                </div>
            </td>
            <td style="width: 50%; text-align: right; padding-right: 20px; vertical-align: top;">
                <div>{{ $kota ?? 'Tegal' }}, {{ $tanggalCetak }}</div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer-divider"></div>
    <div class="footer-note">
        Catatan: Dokumen ini dicetak otomatis oleh SIAKADMAWA dan merupakan bukti tagihan yang sah.
        Untuk pembayaran, silakan menghubungi Bagian Keuangan atau melakukan transfer ke rekening yang telah ditentukan.
    </div>
</body>
</html>
