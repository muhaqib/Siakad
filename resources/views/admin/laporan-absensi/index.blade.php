<x-app-layout>
    <x-slot name="header">Laporan Absensi Bulanan</x-slot>

    <div class="report-tabs flex flex-wrap gap-3 mb-6" aria-label="Jenis laporan">
        @foreach (['dosen' => 'Laporan Dosen', 'mahasiswa' => 'Laporan Mahasiswa'] as $value => $label)
            <a href="{{ route('admin.laporan-absensi.index', ['jenis' => $value, 'periode' => $periode]) }}" @if($jenis === $value) aria-current="page" @endif class="px-4 py-2 rounded-lg font-medium {{ $jenis === $value ? 'bg-siakad-primary text-white' : 'bg-white text-gray-700 border border-gray-300 dark:bg-gray-800 dark:text-gray-200' }}">{{ $label }}</a>
        @endforeach
    </div>
    <div class="report-toolbar flex flex-wrap items-end justify-between gap-4 mb-4">
        <form method="GET" action="{{ route('admin.laporan-absensi.index') }}" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <div>
                <label for="periode" class="block text-sm font-medium dark:text-gray-200 mb-2">Bulan laporan</label>
                <input id="periode" name="periode" type="month" value="{{ $periode }}" required class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                @error('periode') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            @unless ($isDosen)
                <div>
                    <label for="semester" class="block text-sm font-medium dark:text-gray-200 mb-2">Semester</label>
                    <select id="semester" name="semester" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                        <option value="">Semua Semester</option>
                        @foreach (range(1, 14) as $nomorSemester)
                            <option value="{{ $nomorSemester }}" @selected((string) $semester === (string) $nomorSemester)>Semester {{ $nomorSemester }}</option>
                        @endforeach
                    </select>
                    @error('semester') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="prodi_id" class="block text-sm font-medium dark:text-gray-200 mb-2">Program studi</label>
                    <select id="prodi_id" name="prodi_id" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                        <option value="">Semua Prodi</option>
                        @foreach ($prodiList as $prodi)
                            <option value="{{ $prodi->id }}" @selected((string) $prodiId === (string) $prodi->id)>{{ $prodi->nama }}</option>
                        @endforeach
                    </select>
                    @error('prodi_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endunless
            <button type="submit" class="px-4 py-2 rounded-lg bg-siakad-primary text-white font-medium">Tampilkan</button>
        </form>
        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-800 font-medium">Cetak / Simpan PDF</button>
    </div>
    <p class="report-info text-sm text-slate-500 dark:text-gray-400 mb-4">
        @if ($periodeTerakhir)
            Data {{ $jenis }} terakhir tersedia pada {{ \Carbon\CarbonImmutable::createFromFormat('!Y-m', $periodeTerakhir)->locale('id')->translatedFormat('F Y') }}.
            @if ($periode !== $periodeTerakhir)
                <a href="{{ route('admin.laporan-absensi.index', ['jenis' => $jenis, 'periode' => $periodeTerakhir, 'semester' => $semester, 'prodi_id' => $prodiId]) }}" class="underline">Lihat bulan terakhir</a>
            @endif
        @else
            Belum ada catatan absensi {{ $jenis }} untuk filter ini.
        @endif
    </p>

    <div class="report-preview overflow-x-auto rounded-xl bg-slate-200 p-4 md:p-6 dark:bg-gray-900">
        <article id="attendance-report" aria-label="Laporan {{ $title }}" class="mx-auto bg-white text-black shadow-lg">
            <div class="report-heading border-b-2 border-black bg-gray-100 p-6 text-center">
                <h2 class="text-2xl font-extrabold uppercase">ABSENSI {{ strtoupper($jenis) }} STIT MAMBAUL HIKMAH</h2>
                @if ($tahunAjaran !== '')
                    <p class="text-base font-bold mt-2">Tahun Ajaran {{ $tahunAjaran }}</p>
                @endif
            </div>
            <div class="p-5">
                <div class="flex justify-between gap-8 font-semibold mb-5">
                    <div class="flex flex-col gap-1">
                        <p>Bulan <span class="inline-block px-3">:</span> {{ $bulan->locale('id')->translatedFormat('F Y') }}</p>
                        @unless ($isDosen)
                            <p>Semester <span class="inline-block px-3">:</span> {{ $semester ?: 'Semua Semester' }}</p>
                        @endunless
                    </div>
                    @unless ($isDosen)
                        <p>Prodi <span class="inline-block px-3">:</span> {{ $prodiNama }}</p>
                    @endunless
                </div>
                <table class="attendance-grid w-full border-collapse text-center">
                    <caption class="sr-only">Rekap {{ strtolower($title) }} {{ $bulan->locale('id')->translatedFormat('F Y') }}</caption>
                    <colgroup>
                        <col class="number-column">
                        <col class="name-column">
                        @foreach (range(1, $bulan->daysInMonth + 3) as $column)
                            <col>
                        @endforeach
                    </colgroup>
                    <thead>
                        <tr>
                            <th rowspan="2" scope="col">NO</th>
                            <th rowspan="2" scope="col">NAMA</th>
                            <th colspan="{{ $bulan->daysInMonth }}" scope="colgroup">TANGGAL</th>
                            <th colspan="3" scope="colgroup">KET</th>
                        </tr>
                        <tr>
                            @foreach (range(1, $bulan->daysInMonth) as $day)
                                <th scope="col">{{ $day }}</th>
                            @endforeach
                            @foreach (['S', 'I', 'A'] as $code)
                                <th scope="col">{{ $code }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <th scope="row" class="text-left font-medium">
                                    {{ $row['name'] }}
                                    <span class="block text-[9px] font-normal text-gray-600">{{ $row['identity'] }}</span>
                                </th>
                                @foreach (range(1, $bulan->daysInMonth) as $day)
                                    <td>{{ $row['days'][$day] ?? '' }}</td>
                                @endforeach
                                @foreach (['sakit', 'izin', 'alpa'] as $status)
                                    <td class="bg-gray-50">{{ $row['counts'][$status] ?? 0 }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ $bulan->daysInMonth + 5 }}" class="py-6">Belum ada {{ strtolower($title) }} pada bulan ini untuk filter yang dipilih.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="flex flex-col gap-1 text-xs text-gray-600 mt-4">
                    <p>H = Hadir · S = Sakit · I = Izin · A = Alpa{{ $isDosen ? ' · T = Tugas Luar' : '' }}. Kolom kosong berarti belum ada catatan.</p>
                    <p>Status berbeda pada tanggal yang sama dipisahkan dengan /. KET menghitung jumlah catatan sakit, izin, dan alpa, termasuk beberapa pertemuan pada hari yang sama.</p>
                    <p>Total: {{ $rows->count() }} orang · {{ $records->count() }} catatan · Dicetak {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </article>
    </div>
    <style>
        #attendance-report { min-width: 1150px; }
        #attendance-report .attendance-grid { table-layout: fixed; font-size: 11px; }
        #attendance-report .attendance-grid th, #attendance-report .attendance-grid td { border: 1px solid #111; padding: 5px 2px; overflow-wrap: anywhere; }
        #attendance-report .attendance-grid thead { background: #e9eaed; }
        #attendance-report .attendance-grid tbody tr { height: 36px; }
        #attendance-report .number-column { width: 34px; }
        #attendance-report .name-column { width: 200px; }
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            html, body, body > div { background: white !important; color: black !important; }
            aside, header, nav, .report-toolbar, .report-tabs, .report-info, main > :not(.report-preview) { display: none !important; }
            body > div, main, main > .report-preview { margin: 0 !important; padding: 0 !important; overflow: visible !important; }
            body > div > div { margin: 0 !important; }
            #attendance-report { min-width: 0; width: 100%; box-shadow: none; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            #attendance-report .report-heading { padding: 12px; }
            #attendance-report h2 { font-size: 20px; }
            #attendance-report .attendance-grid { font-size: 8px; }
            #attendance-report .name-column { width: 160px; }
            #attendance-report .number-column { width: 25px; }
            #attendance-report thead { display: table-header-group; }
            #attendance-report tr { break-inside: avoid; }
        }
    </style>
</x-app-layout>
