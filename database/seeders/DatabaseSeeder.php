<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Ruangan;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\PaymentInitializationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(PaymentTypeSeeder::class);

        // ==========================================
        // 1. SUPERADMIN
        // ==========================================
        $superadmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@siakad.test',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);
        $superadmin->assignRole('superadmin');

        // ==========================================
        // 2. TAHUN AKADEMIK (2026/2027 Ganjil)
        // ==========================================
        $taAktif = TahunAkademik::create([
            'tahun' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
            'tanggal_mulai' => '2026-09-24',
            'tanggal_selesai' => '2027-01-23',
        ]);

        // ==========================================
        // 3. FAKULTAS (Fakultas Tarbiyah)
        // ==========================================
        $fakultas = Fakultas::create(['nama' => 'Fakultas Tarbiyah']);

        // Admin Fakultas
        $adminFakultas = User::create([
            'name' => 'Admin Fakultas Tarbiyah',
            'email' => 'admin.tarbiyah@siakad.test',
            'password' => Hash::make('password'),
            'role' => 'admin_fakultas',
            'fakultas_id' => $fakultas->id,
        ]);
        $adminFakultas->assignRole('admin_fakultas');

        // ==========================================
        // 4. PROGRAM STUDI (PAI & PBA)
        // ==========================================
        $prodiPai = Prodi::create([
            'nama' => 'Pendidikan Agama Islam',
            'fakultas_id' => $fakultas->id,
        ]);

        $prodiPba = Prodi::create([
            'nama' => 'Pendidikan Bahasa Arab',
            'fakultas_id' => $fakultas->id,
        ]);

        // ==========================================
        // 5. RUANGAN
        // ==========================================
        Ruangan::create([
            'kode_ruangan' => 'TB-01',
            'nama_ruangan' => 'Ruang Kuliah Tarbiyah 1',
            'kapasitas' => 40,
            'gedung' => 'Gedung Tarbiyah',
            'lantai' => 1,
        ]);

        // ==========================================
        // 6. DOSEN (21 Dosen)
        // ==========================================
        $dosenData = [
            ['name' => 'Dr. Muh Sulton Barmawi, SH., M.Pd.', 'email' => 'dosen1@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Assoc. Prof. Dr. Zainal Abidin, S.Ag., M.Pd, CIQAR,CRIK,CIE', 'email' => 'dosen2@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Dr. Hasan Al-Jufri', 'email' => 'dosen3@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Habib Abbas Alhaddad', 'email' => 'dosen4@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Drs. Imam Maskur, M.Si.', 'email' => 'dosen5@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Azis Nurul Iman, SH., M.Pd.', 'email' => 'dosen6@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Muhammad Muqaddam, M.Pd.', 'email' => 'dosen7@mh.com', 'prodi_id' => $prodiPba->id], // PA PBA
            ['name' => 'Muhammad Nabil Barmawi, M.Pd.', 'email' => 'dosen8@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'M. Isy karima, M.Pd.', 'email' => 'dosen9@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Faizal Faithy Ersady, M.Pd.', 'email' => 'dosen10@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Elok Maulidah, S.Ag., M.Pd.', 'email' => 'dosen11@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Naufal Ali Hibatullah, Lc., M.A.', 'email' => 'dosen12@mh.com', 'prodi_id' => $prodiPba->id],
            ['name' => 'Mukhamad Khasanudin, Lc. M.Pd.', 'email' => 'dosen13@mh.com', 'prodi_id' => $prodiPba->id],
            ['name' => 'Mohammad Salafudin, M.Pd.', 'email' => 'dosen14@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Nur Rohman, M.Pd.', 'email' => 'dosen15@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Budi Prastyo, M.Pd.', 'email' => 'dosen16@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Ahmad Muhammad, M.Pd.', 'email' => 'dosen17@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Muhammad Abdan Syakur, M.Pd.', 'email' => 'dosen18@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Muhammad Aqib, S.Sos., M.Pd.', 'email' => 'dosen19@mh.com', 'prodi_id' => $prodiPai->id],
            ['name' => 'Abdurrozaq Luhur Istighfar, M.Pd.', 'email' => 'dosen20@mh.com', 'prodi_id' => $prodiPai->id], // PA PAI
            ['name' => 'Muhammad Nurzaini, M.Pd.', 'email' => 'dosen21@mh.com', 'prodi_id' => $prodiPai->id],
        ];

        $dosenModels = [];
        foreach ($dosenData as $index => $item) {
            $dosenUser = User::create([
                'name' => $item['name'],
                'email' => $item['email'],
                'password' => Hash::make('dosen123'),
                'role' => 'dosen',
            ]);
            $dosenUser->assignRole('dosen');

            $dosen = Dosen::create([
                'user_id' => $dosenUser->id,
                'nidn' => sprintf('21000000%02d', $index + 1),
                'prodi_id' => $item['prodi_id'],
            ]);

            $dosenModels[$item['name']] = $dosen;
        }

        $paPai = $dosenModels['Abdurrozaq Luhur Istighfar, M.Pd.'] ?? null;
        $paPba = $dosenModels['Muhammad Muqaddam, M.Pd.'] ?? null;

        // ==========================================
        // 7. MAHASISWA
        // ==========================================
        $mahasiswaPai = [
            ['nim' => '266203001', 'nama' => 'Dewi Masyitoh'],
            ['nim' => '266203002', 'nama' => 'Khodijah'],
            ['nim' => '266203003', 'nama' => 'Izza Aulia'],
            ['nim' => '266203004', 'nama' => 'Syarah Maulina Ikramullah'],
            ['nim' => '266203005', 'nama' => 'Nauval Arya Tubagus Awaludin'],
            ['nim' => '266203006', 'nama' => 'Nilnal Muna Ashfiya'],
            ['nim' => '266203007', 'nama' => 'Fatimatuz Zahro'],
            ['nim' => '266203008', 'nama' => 'Latifatunisa'],
            ['nim' => '266203009', 'nama' => 'Abdul Rohman'],
            ['nim' => '266203010', 'nama' => 'Amelia Salsabila'],
            ['nim' => '266203011', 'nama' => 'Indi Refah'],
            ['nim' => '266203012', 'nama' => 'Nurul Aulia'],
            ['nim' => '266203013', 'nama' => 'Muhammadsyarif Robiansyah'],
            ['nim' => '266203014', 'nama' => 'Syifa Mawaddah'],
            ['nim' => '266203015', 'nama' => 'Muchamad Amin'],
            ['nim' => '266203016', 'nama' => 'Aisyah idris Al jufry'],
            ['nim' => '266203017', 'nama' => 'Zahidah alwiah'],
            ['nim' => '266203018', 'nama' => 'Uswatun hasanah'],
            ['nim' => '266203019', 'nama' => "fufu marfu'ah"],
            ['nim' => '266203020', 'nama' => 'Alia Lutfiah'],
            ['nim' => '266203021', 'nama' => 'Alfian Ahmad Rizal Fauzi'],
            ['nim' => '266203022', 'nama' => 'Wildan Asyaoqi Mubarok'],
            ['nim' => '266203023', 'nama' => 'Dudun muhamadun'],
            ['nim' => '266203024', 'nama' => 'M baqri an nagib'],
            ['nim' => '266203025', 'nama' => 'Putri Balqis'],
            ['nim' => '266203026', 'nama' => 'Lela Nurlaela'],
            ['nim' => '266203027', 'nama' => 'Jamilah'],
        ];

        $mahasiswaPba = [
            ['nim' => '266204001', 'nama' => 'Nabil Maulana Said'],
            ['nim' => '266204002', 'nama' => 'Muhammad Khusni Mubarok'],
            ['nim' => '266204003', 'nama' => 'Ahmad Baehaki'],
            ['nim' => '266204004', 'nama' => 'Agung Nur Rosyid'],
            ['nim' => '266204005', 'nama' => 'Milchan Firdian Kholadani'],
            ['nim' => '266204006', 'nama' => 'Namira Adzani'],
            ['nim' => '266204007', 'nama' => 'Lia Solikhah'],
            ['nim' => '266204008', 'nama' => 'Siti Mariska'],
            ['nim' => '266204009', 'nama' => 'Burhan Muqoffa'],
            ['nim' => '266204010', 'nama' => 'Nabilatus Saadah'],
            ['nim' => '266204011', 'nama' => 'Umar Abdulloh Al Atas'],
            ['nim' => '266204012', 'nama' => 'Muhammad Afifuddin'],
        ];

        $initService = app(PaymentInitializationService::class);

        foreach ($mahasiswaPai as $m) {
            $user = User::create([
                'name' => $m['nama'],
                'email' => "{$m['nim']}@gmail.com",
                'password' => Hash::make($m['nim']),
                'role' => 'mahasiswa',
            ]);
            $user->assignRole('mahasiswa');

            $mhs = Mahasiswa::create([
                'user_id' => $user->id,
                'nim' => $m['nim'],
                'prodi_id' => $prodiPai->id,
                'angkatan' => '2026',
                'dosen_pa_id' => $paPai?->id,
                'status' => 'aktif',
                'is_krs_unlocked' => false,
            ]);

            $initService->initializeStudentPayments($mhs, $taAktif);
        }

        foreach ($mahasiswaPba as $m) {
            $user = User::create([
                'name' => $m['nama'],
                'email' => "{$m['nim']}@gmail.com",
                'password' => Hash::make($m['nim']),
                'role' => 'mahasiswa',
            ]);
            $user->assignRole('mahasiswa');

            $mhs = Mahasiswa::create([
                'user_id' => $user->id,
                'nim' => $m['nim'],
                'prodi_id' => $prodiPba->id,
                'angkatan' => '2026',
                'dosen_pa_id' => $paPba?->id,
                'status' => 'aktif',
                'is_krs_unlocked' => false,
            ]);

            $initService->initializeStudentPayments($mhs, $taAktif);
        }

        // ==========================================
        // OUTPUT
        // ==========================================
        $this->command->newLine();
        $this->command->info('✅ Database seeded successfully!');
        $this->command->newLine();
        $this->command->info('📋 Login Credentials:');
        $this->command->table(
            ['Role', 'Email / NIM', 'Password', 'Keterangan'],
            [
                ['Superadmin', 'superadmin@siakad.test', 'password', 'Akses Semua Fitur'],
                ['Admin Fakultas', 'admin.tarbiyah@siakad.test', 'password', 'Fakultas Tarbiyah'],
                ['Dosen (PA PAI)', 'dosen20@mh.com', 'dosen123', 'Abdurrozaq Luhur Istighfar, M.Pd.'],
                ['Dosen (PA PBA)', 'dosen7@mh.com', 'dosen123', 'Muhammad Muqaddam, M.Pd.'],
                ['Mahasiswa PAI', '266203001@gmail.com', '266203001', 'Dewi Masyitoh'],
                ['Mahasiswa PBA', '266204011@gmail.com', '266204011', 'Umar Abdulloh Al Atas'],
            ]
        );
        $this->command->newLine();
        $this->command->info('Total Mahasiswa: '.(count($mahasiswaPai) + count($mahasiswaPba)).' (PAI: '.count($mahasiswaPai).', PBA: '.count($mahasiswaPba).')');
        $this->command->info('Total Dosen: '.count($dosenData));
    }
}
