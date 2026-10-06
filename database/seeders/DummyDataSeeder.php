<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\JadwalKuliah;
use App\Models\KehadiranDosen;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Materi;
use App\Models\Nilai;
use App\Models\PaymentHistory;
use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\Prodi;
use App\Models\Ruangan;
use App\Models\StudentPayment;
use App\Models\TahunAkademik;
use App\Models\Tugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $taAktif = TahunAkademik::where('is_active', true)->first();
        if (! $taAktif) {
            $taAktif = TahunAkademik::firstOrCreate(
                ['tahun' => '2026/2027', 'semester' => 'Ganjil'],
                [
                    'is_active' => true,
                    'tanggal_mulai' => '2026-09-01',
                    'tanggal_selesai' => '2027-01-31',
                ]
            );
        }

        $fakultas = Fakultas::firstOrCreate(['nama' => 'Fakultas Tarbiyah']);
        $prodiPai = Prodi::firstOrCreate(['nama' => 'Pendidikan Agama Islam'], ['fakultas_id' => $fakultas->id]);
        $prodiPba = Prodi::firstOrCreate(['nama' => 'Pendidikan Bahasa Arab'], ['fakultas_id' => $fakultas->id]);

        $superadmin = User::where('role', 'superadmin')->first();

        // ==========================================
        // 1. RUANGAN
        // ==========================================
        $this->command->info('🏫 Seeding Ruangan...');
        $ruanganList = [
            ['kode' => 'TB-01', 'nama' => 'Ruang Kuliah Tarbiyah 1', 'kapasitas' => 40, 'gedung' => 'Gedung Tarbiyah', 'lantai' => 1, 'fasilitas' => 'AC, Proyektor, Sound System, Whiteboard'],
            ['kode' => 'TB-02', 'nama' => 'Ruang Kuliah Tarbiyah 2', 'kapasitas' => 40, 'gedung' => 'Gedung Tarbiyah', 'lantai' => 1, 'fasilitas' => 'AC, Proyektor, Whiteboard'],
            ['kode' => 'TB-03', 'nama' => 'Ruang Kuliah Tarbiyah 3', 'kapasitas' => 35, 'gedung' => 'Gedung Tarbiyah', 'lantai' => 2, 'fasilitas' => 'AC, Proyektor, Smart TV'],
            ['kode' => 'TB-04', 'nama' => 'Ruang Kuliah Tarbiyah 4', 'kapasitas' => 35, 'gedung' => 'Gedung Tarbiyah', 'lantai' => 2, 'fasilitas' => 'AC, Proyektor, Whiteboard'],
            ['kode' => 'LAB-01', 'nama' => 'Laboratorium Komputer & Bahasa', 'kapasitas' => 30, 'gedung' => 'Gedung Utama', 'lantai' => 1, 'fasilitas' => '30 PC Core i5, Headset, LAN, AC'],
            ['kode' => 'AULA', 'nama' => 'Aula Utama KH. Mambaul Hikmah', 'kapasitas' => 150, 'gedung' => 'Gedung Utama', 'lantai' => 2, 'fasilitas' => 'Sound System Besar, 2 Proyektor, Podium'],
        ];

        foreach ($ruanganList as $r) {
            Ruangan::updateOrCreate(
                ['kode_ruangan' => $r['kode']],
                [
                    'nama_ruangan' => $r['nama'],
                    'kapasitas' => $r['kapasitas'],
                    'gedung' => $r['gedung'],
                    'lantai' => $r['lantai'],
                    'fasilitas' => $r['fasilitas'],
                    'is_active' => true,
                    'fakultas_id' => $fakultas->id,
                ]
            );
        }

        // ==========================================
        // 2. MATA KULIAH
        // ==========================================
        $this->command->info('📚 Seeding Mata Kuliah...');
        $mkPaiData = [
            ['kode_mk' => 'PAI101', 'nama_mk' => 'Studi Al-Qur\'an', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'PAI102', 'nama_mk' => 'Studi Hadits', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'PAI103', 'nama_mk' => 'Fiqih Ibadah', 'sks' => 3, 'semester' => 1],
            ['kode_mk' => 'PAI104', 'nama_mk' => 'Bahasa Arab Dasar', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'PAI105', 'nama_mk' => 'Ilmu Pendidikan Islam', 'sks' => 3, 'semester' => 1],
            ['kode_mk' => 'PAI106', 'nama_mk' => 'Sejarah Peradaban Islam', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'PAI107', 'nama_mk' => 'Pancasila & Kewarganegaraan', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'PAI108', 'nama_mk' => 'Bahasa Indonesia Akademik', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'PAI109', 'nama_mk' => 'Akhlak Tasawuf', 'sks' => 2, 'semester' => 1],
        ];

        $mkPbaData = [
            ['kode_mk' => 'PBA101', 'nama_mk' => 'Muhadatsah I', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'PBA102', 'nama_mk' => 'Qira\'ah I', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'PBA103', 'nama_mk' => 'Kitabah I', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'PBA104', 'nama_mk' => 'Nahwu Dasar', 'sks' => 3, 'semester' => 1],
            ['kode_mk' => 'PBA105', 'nama_mk' => 'Sharaf Dasar', 'sks' => 3, 'semester' => 1],
            ['kode_mk' => 'PBA106', 'nama_mk' => 'Studi Al-Qur\'an & Hadits', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'PBA107', 'nama_mk' => 'Pengantar Linguistik Arab', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'PBA108', 'nama_mk' => 'Bahasa Indonesia Akademik', 'sks' => 2, 'semester' => 1],
        ];

        $mkPaiModels = [];
        foreach ($mkPaiData as $item) {
            $mkPaiModels[] = MataKuliah::updateOrCreate(
                ['kode_mk' => $item['kode_mk']],
                [
                    'nama_mk' => $item['nama_mk'],
                    'sks' => $item['sks'],
                    'semester' => $item['semester'],
                    'prodi_id' => $prodiPai->id,
                ]
            );
        }

        $mkPbaModels = [];
        foreach ($mkPbaData as $item) {
            $mkPbaModels[] = MataKuliah::updateOrCreate(
                ['kode_mk' => $item['kode_mk']],
                [
                    'nama_mk' => $item['nama_mk'],
                    'sks' => $item['sks'],
                    'semester' => $item['semester'],
                    'prodi_id' => $prodiPba->id,
                ]
            );
        }

        // ==========================================
        // 3. KELAS & DOSEN PENGAMPU
        // ==========================================
        $this->command->info('👥 Seeding Kelas...');
        $dosenList = Dosen::with('user')->get();
        if ($dosenList->isEmpty()) {
            $this->command->error('Tidak ada dosen! Jalankan DatabaseSeeder utama terlebih dahulu.');

            return;
        }

        // Cari dosen kunci
        $dosenPaPai = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen20@mh.com'))->first() ?? $dosenList->first();
        $dosenPaPba = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen7@mh.com'))->first() ?? $dosenList->first();
        $dosen1 = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen1@mh.com'))->first() ?? $dosenList->first();
        $dosen2 = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen2@mh.com'))->first() ?? $dosenList->first();
        $dosen3 = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen3@mh.com'))->first() ?? $dosenList->first();
        $dosen4 = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen4@mh.com'))->first() ?? $dosenList->first();
        $dosen5 = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen5@mh.com'))->first() ?? $dosenList->first();
        $dosen6 = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen6@mh.com'))->first() ?? $dosenList->first();
        $dosen8 = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen8@mh.com'))->first() ?? $dosenList->first();
        $dosen10 = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen10@mh.com'))->first() ?? $dosenList->first();
        $dosen11 = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen11@mh.com'))->first() ?? $dosenList->first();
        $dosen12 = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen12@mh.com'))->first() ?? $dosenList->first();
        $dosen13 = Dosen::whereHas('user', fn ($q) => $q->where('email', 'dosen13@mh.com'))->first() ?? $dosenList->first();

        // Petakan MK ke Dosen
        $kelasConfigPai = [
            'PAI101' => ['dosen' => $dosen1, 'kelas' => 'A'],
            'PAI102' => ['dosen' => $dosen2, 'kelas' => 'A'],
            'PAI103' => ['dosen' => $dosenPaPai, 'kelas' => 'A'], // Fiqih Ibadah diampu PA PAI
            'PAI104' => ['dosen' => $dosen4, 'kelas' => 'A'],
            'PAI105' => ['dosen' => $dosen5, 'kelas' => 'A'],
            'PAI106' => ['dosen' => $dosen6, 'kelas' => 'A'],
            'PAI107' => ['dosen' => $dosen8, 'kelas' => 'A'],
            'PAI108' => ['dosen' => $dosen10, 'kelas' => 'A'],
            'PAI109' => ['dosen' => $dosen11, 'kelas' => 'A'],
        ];

        $kelasPaiModels = [];
        foreach ($mkPaiModels as $mk) {
            $conf = $kelasConfigPai[$mk->kode_mk] ?? ['dosen' => $dosenPaPai, 'kelas' => 'A'];
            $kelasPaiModels[] = Kelas::updateOrCreate(
                [
                    'mata_kuliah_id' => $mk->id,
                    'nama_kelas' => $conf['kelas'],
                    'tahun_akademik_id' => $taAktif->id,
                ],
                [
                    'dosen_id' => $conf['dosen']->id,
                    'kapasitas' => 40,
                    'is_closed' => false,
                ]
            );
        }

        $kelasConfigPba = [
            'PBA101' => ['dosen' => $dosenPaPba, 'kelas' => 'A'], // Muhadatsah diampu PA PBA
            'PBA102' => ['dosen' => $dosen12, 'kelas' => 'A'],
            'PBA103' => ['dosen' => $dosen13, 'kelas' => 'A'],
            'PBA104' => ['dosen' => $dosenPaPba, 'kelas' => 'A'],
            'PBA105' => ['dosen' => $dosen12, 'kelas' => 'A'],
            'PBA106' => ['dosen' => $dosen13, 'kelas' => 'A'],
            'PBA107' => ['dosen' => $dosenPaPba, 'kelas' => 'A'],
            'PBA108' => ['dosen' => $dosen10, 'kelas' => 'A'],
        ];

        $kelasPbaModels = [];
        foreach ($mkPbaModels as $mk) {
            $conf = $kelasConfigPba[$mk->kode_mk] ?? ['dosen' => $dosenPaPba, 'kelas' => 'A'];
            $kelasPbaModels[] = Kelas::updateOrCreate(
                [
                    'mata_kuliah_id' => $mk->id,
                    'nama_kelas' => $conf['kelas'],
                    'tahun_akademik_id' => $taAktif->id,
                ],
                [
                    'dosen_id' => $conf['dosen']->id,
                    'kapasitas' => 30,
                    'is_closed' => false,
                ]
            );
        }

        // ==========================================
        // 4. JADWAL KULIAH (TERMASUK HARI INI)
        // ==========================================
        $this->command->info('🗓️ Seeding Jadwal Kuliah...');
        $namaHariMap = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $todayName = $namaHariMap[now()->dayOfWeek];
        // Jika hari ini Minggu (hari libur), arahkan simulasi aktif ke Senin agar selalu ada kelas
        $activeClassDay = $todayName === 'Minggu' ? 'Senin' : $todayName;

        // Jadwalkan kelas PAI
        $jadwalPaiBlueprint = [
            0 => ['hari' => $activeClassDay, 'jam_mulai' => '07:30', 'jam_selesai' => '09:10', 'ruangan' => 'TB-01'], // Fiqih Ibadah (PA PAI) hari ini!
            1 => ['hari' => $activeClassDay, 'jam_mulai' => '09:30', 'jam_selesai' => '11:10', 'ruangan' => 'TB-01'], // Studi Al-Quran hari ini!
            2 => ['hari' => 'Senin', 'jam_mulai' => '07:30', 'jam_selesai' => '09:10', 'ruangan' => 'TB-02'],
            3 => ['hari' => 'Senin', 'jam_mulai' => '09:30', 'jam_selesai' => '11:10', 'ruangan' => 'TB-02'],
            4 => ['hari' => 'Selasa', 'jam_mulai' => '13:00', 'jam_selesai' => '15:30', 'ruangan' => 'TB-01'],
            5 => ['hari' => 'Rabu', 'jam_mulai' => '07:30', 'jam_selesai' => '09:10', 'ruangan' => 'TB-03'],
            6 => ['hari' => 'Kamis', 'jam_mulai' => '09:30', 'jam_selesai' => '11:10', 'ruangan' => 'TB-03'],
            7 => ['hari' => 'Jumat', 'jam_mulai' => '07:30', 'jam_selesai' => '09:10', 'ruangan' => 'TB-01'],
            8 => ['hari' => 'Sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '09:40', 'ruangan' => 'TB-02'],
        ];

        // Pastikan kelas Fiqih Ibadah (kelasPaiModels index 2) ada di activeClassDay
        $jadwalKuliahModels = [];
        foreach ($kelasPaiModels as $idx => $kelas) {
            $bp = $jadwalPaiBlueprint[$idx] ?? ['hari' => 'Senin', 'jam_mulai' => '13:00', 'jam_selesai' => '14:40', 'ruangan' => 'TB-01'];
            // Jika kelas Fiqih Ibadah, pastikan hari = activeClassDay
            if ($kelas->mataKuliah->kode_mk === 'PAI103') {
                $bp['hari'] = $activeClassDay;
                $bp['jam_mulai'] = '07:30';
                $bp['jam_selesai'] = '10:00';
            }

            $jadwal = JadwalKuliah::firstOrCreate(
                [
                    'kelas_id' => $kelas->id,
                    'hari' => $bp['hari'],
                    'jam_mulai' => $bp['jam_mulai'],
                ],
                [
                    'jam_selesai' => $bp['jam_selesai'],
                    'ruangan' => $bp['ruangan'],
                ]
            );
            $jadwalKuliahModels[] = $jadwal;
        }

        // Jadwal kelas PBA
        $jadwalPbaBlueprint = [
            0 => ['hari' => $activeClassDay, 'jam_mulai' => '10:15', 'jam_selesai' => '11:55', 'ruangan' => 'LAB-01'], // Muhadatsah (PA PBA) hari ini!
            1 => ['hari' => 'Senin', 'jam_mulai' => '10:15', 'jam_selesai' => '11:55', 'ruangan' => 'TB-04'],
            2 => ['hari' => 'Selasa', 'jam_mulai' => '07:30', 'jam_selesai' => '09:10', 'ruangan' => 'TB-04'],
            3 => ['hari' => 'Rabu', 'jam_mulai' => '09:30', 'jam_selesai' => '12:00', 'ruangan' => 'LAB-01'],
            4 => ['hari' => 'Kamis', 'jam_mulai' => '07:30', 'jam_selesai' => '10:00', 'ruangan' => 'TB-04'],
            5 => ['hari' => 'Jumat', 'jam_mulai' => '09:30', 'jam_selesai' => '11:10', 'ruangan' => 'TB-04'],
            6 => ['hari' => 'Sabtu', 'jam_mulai' => '10:00', 'jam_selesai' => '11:40', 'ruangan' => 'LAB-01'],
            7 => ['hari' => 'Sabtu', 'jam_mulai' => '13:00', 'jam_selesai' => '14:40', 'ruangan' => 'TB-04'],
        ];

        foreach ($kelasPbaModels as $idx => $kelas) {
            $bp = $jadwalPbaBlueprint[$idx] ?? ['hari' => 'Senin', 'jam_mulai' => '13:00', 'jam_selesai' => '14:40', 'ruangan' => 'TB-04'];
            if ($kelas->mataKuliah->kode_mk === 'PBA101') {
                $bp['hari'] = $activeClassDay;
            }

            $jadwal = JadwalKuliah::firstOrCreate(
                [
                    'kelas_id' => $kelas->id,
                    'hari' => $bp['hari'],
                    'jam_mulai' => $bp['jam_mulai'],
                ],
                [
                    'jam_selesai' => $bp['jam_selesai'],
                    'ruangan' => $bp['ruangan'],
                ]
            );
            $jadwalKuliahModels[] = $jadwal;
        }

        // ==========================================
        // 5. KRS & KRS DETAIL MAHASISWA
        // ==========================================
        $this->command->info('📝 Seeding KRS & Enrolment Mahasiswa...');
        $mahasiswaPai = Mahasiswa::where('prodi_id', $prodiPai->id)->get();
        $mahasiswaPba = Mahasiswa::where('prodi_id', $prodiPba->id)->get();

        foreach ($mahasiswaPai as $mhs) {
            $mhs->update(['is_krs_unlocked' => true]);

            $krs = Krs::firstOrCreate(
                [
                    'mahasiswa_id' => $mhs->id,
                    'tahun_akademik_id' => $taAktif->id,
                ],
                [
                    'status' => 'approved',
                    'catatan' => 'KRS Semester 1 telah disetujui Dosen Pembimbing Akademik.',
                ]
            );

            foreach ($kelasPaiModels as $kelas) {
                KrsDetail::firstOrCreate([
                    'krs_id' => $krs->id,
                    'kelas_id' => $kelas->id,
                ]);
            }
        }

        foreach ($mahasiswaPba as $mhs) {
            $mhs->update(['is_krs_unlocked' => true]);

            $krs = Krs::firstOrCreate(
                [
                    'mahasiswa_id' => $mhs->id,
                    'tahun_akademik_id' => $taAktif->id,
                ],
                [
                    'status' => 'approved',
                    'catatan' => 'KRS Semester 1 telah disetujui Dosen Pembimbing Akademik.',
                ]
            );

            foreach ($kelasPbaModels as $kelas) {
                KrsDetail::firstOrCreate([
                    'krs_id' => $krs->id,
                    'kelas_id' => $kelas->id,
                ]);
            }
        }

        // ==========================================
        // 6. PERTEMUAN & PRESENSI MAHASISWA
        // ==========================================
        $this->command->info('📅 Seeding Pertemuan & Presensi Kuliah...');
        $materiTitles = [
            1 => ['judul' => 'Pengantar Perkuliahan & Kontrak Belajar', 'desc' => 'Penyampaian RPS, bobot penilaian, tata tertib, dan gambaran umum silabus.'],
            2 => ['judul' => 'Kajian Konseptual dan Landasan Teori', 'desc' => 'Mendiskusikan definisi, ruang lingkup, dan dalil-dalil pokok.'],
            3 => ['judul' => 'Metodologi dan Pendekatan Analisis', 'desc' => 'Membedah instrumen analisis dan kaidah-kaidah aplikatif.'],
            4 => ['judul' => 'Studi Kasus Kontekstual', 'desc' => 'Analisis komparatif isu-isu kontemporer dalam masyarakat.'],
            5 => ['judul' => 'Pendalaman Materi & Diskusi Tematik', 'desc' => 'Presentasi makalah kelompok dan sesi tanya jawab interaktif.'],
            6 => ['judul' => 'Evaluasi Tengah Semester & Review', 'desc' => 'Review materi pertemuan 1-5 dan persiapan UTS.'],
        ];

        foreach ($jadwalKuliahModels as $jadwal) {
            $isTodayClass = ($jadwal->hari === $activeClassDay);
            $maxMeetings = $isTodayClass ? 6 : 5;

            for ($p = 1; $p <= $maxMeetings; $p++) {
                // Tentukan tanggal: pertemuan terakhir jika hari ini adalah hari kelas, pakai tanggal hari ini
                if ($p === $maxMeetings && $isTodayClass) {
                    $tanggalPertemuan = now()->toDateString();
                } else {
                    $tanggalPertemuan = now()->subWeeks($maxMeetings - $p + 1)->toDateString();
                }

                $meta = $materiTitles[$p] ?? ['judul' => "Pertemuan ke-{$p}", 'desc' => 'Pembahasan lanjutan materi perkuliahan.'];

                $pertemuan = Pertemuan::firstOrCreate(
                    [
                        'jadwal_kuliah_id' => $jadwal->id,
                        'pertemuan_ke' => $p,
                    ],
                    [
                        'tanggal' => $tanggalPertemuan,
                        'materi' => $meta['judul'],
                        'status' => Pertemuan::STATUS_TERLAKSANA,
                    ]
                );

                // Buat materi LMS untuk pertemuan 1 & 2
                if ($p <= 2) {
                    Materi::firstOrCreate(
                        [
                            'pertemuan_id' => $pertemuan->id,
                            'judul' => 'Slide Presentasi: '.$meta['judul'],
                        ],
                        [
                            'deskripsi' => $meta['desc'],
                            'file_name' => 'materi_pertemuan_'.$p.'.pdf',
                            'file_size' => 1024 * 1024 * 2, // 2MB
                            'file_type' => 'application/pdf',
                            'urutan' => 1,
                        ]
                    );
                }

                // Presensi mahasiswa untuk kelas ini
                $enrolledMahasiswa = Mahasiswa::whereHas('krs', function ($q) use ($jadwal) {
                    $q->where('status', 'approved')
                        ->whereHas('krsDetail', fn ($q2) => $q2->where('kelas_id', $jadwal->kelas_id));
                })->get();

                foreach ($enrolledMahasiswa as $idx => $mhs) {
                    // Distribusi status presensi
                    $roll = ($idx + $p) % 20;
                    if ($roll == 18) {
                        $statusPresensi = Presensi::STATUS_SAKIT;
                        $keteranganPresensi = 'Izin sakit, surat terlampir';
                    } elseif ($roll == 19) {
                        $statusPresensi = Presensi::STATUS_IZIN;
                        $keteranganPresensi = 'Izin keperluan keluarga mendesak';
                    } else {
                        $statusPresensi = Presensi::STATUS_HADIR;
                        $keteranganPresensi = null;
                    }

                    Presensi::firstOrCreate(
                        [
                            'pertemuan_id' => $pertemuan->id,
                            'mahasiswa_id' => $mhs->id,
                        ],
                        [
                            'status' => $statusPresensi,
                            'waktu_presensi' => Carbon::parse($jadwal->jam_mulai)->addMinutes(rand(2, 10))->format('H:i:s'),
                            'keterangan' => $keteranganPresensi,
                        ]
                    );
                }
            }
        }

        // ==========================================
        // 7. NILAI MAHASISWA
        // ==========================================
        $this->command->info('📊 Seeding Nilai Mahasiswa...');
        foreach ($kelasPaiModels as $kelas) {
            $students = Mahasiswa::whereHas('krs.krsDetail', fn ($q) => $q->where('kelas_id', $kelas->id))->get();
            foreach ($students as $mhs) {
                $nilaiAngka = rand(75, 96);
                $nilaiHuruf = match (true) {
                    $nilaiAngka >= 85 => 'A',
                    $nilaiAngka >= 80 => 'A-',
                    $nilaiAngka >= 75 => 'B+',
                    $nilaiAngka >= 70 => 'B',
                    default => 'B-',
                };

                Nilai::updateOrCreate(
                    [
                        'mahasiswa_id' => $mhs->id,
                        'kelas_id' => $kelas->id,
                    ],
                    [
                        'nilai_angka' => $nilaiAngka,
                        'nilai_huruf' => $nilaiHuruf,
                    ]
                );
            }
        }

        // ==========================================
        // 8. TUGAS KULIAH
        // ==========================================
        $this->command->info('📋 Seeding Tugas Kuliah...');
        foreach ([$kelasPaiModels[0], $kelasPaiModels[2]] as $kelas) {
            Tugas::firstOrCreate(
                [
                    'kelas_id' => $kelas->id,
                    'judul' => 'Tugas Makalah: Kajian Bab 1 & 2',
                ],
                [
                    'deskripsi' => 'Susun makalah kelompok maksimal 15 halaman dengan format APA Style. Dikumpulkan dalam format PDF.',
                    'deadline' => now()->addDays(10),
                    'max_file_size' => 5,
                    'allowed_extensions' => 'pdf,docx',
                    'is_active' => true,
                ]
            );
        }

        // ==========================================
        // 9. KEHADIRAN DOSEN (RIWAYAT & HARI INI)
        // ==========================================
        $this->command->info('🧑‍🏫 Seeding Kehadiran Dosen (Riwayat & Hari Ini)...');

        // Riwayat 14 hari ke belakang untuk para dosen
        for ($day = 14; $day >= 1; $day--) {
            $pastDate = now()->subDays($day);
            // Lewati hari Minggu
            if ($pastDate->isSunday()) {
                continue;
            }

            foreach ([$dosenPaPai, $dosenPaPba, $dosen1, $dosen2, $dosen4] as $idx => $dosen) {
                if (! $dosen) {
                    continue;
                }

                $statusDosen = ($day === 3 && $idx === 2) ? KehadiranDosen::STATUS_IZIN : KehadiranDosen::STATUS_HADIR;

                KehadiranDosen::firstOrCreate(
                    [
                        'dosen_id' => $dosen->id,
                        'tanggal' => $pastDate->toDateString(),
                        'jadwal_kuliah_id' => null,
                    ],
                    [
                        'jam_masuk' => $statusDosen === KehadiranDosen::STATUS_HADIR ? '07:'.sprintf('%02d', rand(10, 25)).':00' : null,
                        'jam_keluar' => $statusDosen === KehadiranDosen::STATUS_HADIR ? '15:'.sprintf('%02d', rand(30, 55)).':00' : null,
                        'status' => $statusDosen,
                        'keterangan' => $statusDosen === KehadiranDosen::STATUS_IZIN ? 'Menghadiri undangan seminar regional' : null,
                    ]
                );
            }
        }

        // Kehadiran Dosen HARI INI (untuk simulasi live):
        // 1. Dosen PA PAI (dosen20@mh.com): Sudah absen masuk pagi ini, BELUM absen keluar!
        if ($dosenPaPai) {
            KehadiranDosen::updateOrCreate(
                [
                    'dosen_id' => $dosenPaPai->id,
                    'tanggal' => now()->toDateString(),
                    'jadwal_kuliah_id' => null,
                ],
                [
                    'jam_masuk' => '07:15:00',
                    'jam_keluar' => null,
                    'status' => KehadiranDosen::STATUS_HADIR,
                    'keterangan' => null,
                ]
            );
        }

        // 2. Dosen PA PBA (dosen7@mh.com): Sudah absen masuk & sudah absen keluar hari ini!
        if ($dosenPaPba) {
            KehadiranDosen::updateOrCreate(
                [
                    'dosen_id' => $dosenPaPba->id,
                    'tanggal' => now()->toDateString(),
                    'jadwal_kuliah_id' => null,
                ],
                [
                    'jam_masuk' => '07:10:00',
                    'jam_keluar' => '15:45:00',
                    'status' => KehadiranDosen::STATUS_HADIR,
                    'keterangan' => null,
                ]
            );
        }

        // 3. Dosen 4 (dosen4@mh.com): Sakit hari ini
        if ($dosen4) {
            KehadiranDosen::updateOrCreate(
                [
                    'dosen_id' => $dosen4->id,
                    'tanggal' => now()->toDateString(),
                    'jadwal_kuliah_id' => null,
                ],
                [
                    'jam_masuk' => null,
                    'jam_keluar' => null,
                    'status' => KehadiranDosen::STATUS_SAKIT,
                    'keterangan' => 'Demam tinggi dan flu, istirahat dokter 1 hari.',
                ]
            );
        }

        // 4. Dosen 1 (dosen1@mh.com): Belum absen hari ini (hapus jika ada agar bisa dites)
        if ($dosen1) {
            KehadiranDosen::where('dosen_id', $dosen1->id)
                ->whereDate('tanggal', now()->toDateString())
                ->delete();
        }

        // ==========================================
        // 10. PEMBAYARAN MAHASISWA & RIWAYAT
        // ==========================================
        $this->command->info('💳 Seeding Status Pembayaran Mahasiswa...');
        $allStudents = Mahasiswa::all();
        $confirmedByUserId = $superadmin?->id;

        foreach ($allStudents as $idx => $mhs) {
            $payments = StudentPayment::where('mahasiswa_id', $mhs->id)->get();

            foreach ($payments as $payment) {
                // Bayar Pendaftaran & Semester 1 untuk ~75% mahasiswa
                $isRegistration = str_contains($payment->paymentType?->code ?? '', 'registration');
                $isSem1 = str_contains($payment->paymentType?->code ?? '', 'semester_1');

                if (($isRegistration || $isSem1) && ($idx % 4 !== 0)) {
                    $paymentDate = now()->subDays(rand(5, 30));

                    $payment->update([
                        'paid_amount' => $payment->amount,
                        'status' => 'paid',
                        'payment_method' => $idx % 2 === 0 ? 'cash' : 'midtrans_qris',
                        'payment_date' => $paymentDate,
                        'confirmed_at' => $paymentDate->copy()->addMinutes(15),
                        'confirmed_by' => $confirmedByUserId,
                        'notes' => 'Pembayaran lunas terverifikasi.',
                    ]);

                    PaymentHistory::updateOrCreate(
                        [
                            'student_payment_id' => $payment->id,
                            'action' => 'paid',
                        ],
                        [
                            'old_status' => 'unpaid',
                            'new_status' => 'paid',
                            'amount' => $payment->amount,
                            'notes' => 'Pembayaran lunas via Kasir/Bank.',
                            'performed_by' => $confirmedByUserId,
                            'created_at' => $paymentDate,
                        ]
                    );
                }
            }
        }

        $this->command->newLine();
        $this->command->info('🎉 Dummy data lengkap berhasil dibuat dan siap digunakan!');
    }
}
