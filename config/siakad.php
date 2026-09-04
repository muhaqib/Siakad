<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Role Sistem SIAKAD
    |--------------------------------------------------------------------------
    |
    | Role yang digunakan untuk akses sistem. Jangan ubah sembarangan karena
    | berpengaruh ke middleware dan policy.
    |
    */

    'roles' => [
        'admin',
        'dosen',
        'mahasiswa',
    ],


    /*
    |--------------------------------------------------------------------------
    | Label Semester
    |--------------------------------------------------------------------------
    */

    'semester_labels' => [
        1 => 'Ganjil',
        2 => 'Genap',
    ],


    /*
    |--------------------------------------------------------------------------
    | Status KRS
    |--------------------------------------------------------------------------
    |
    | draft     → mahasiswa masih mengedit
    | pending   → menunggu persetujuan admin/dosen PA
    | approved  → disetujui & final
    | rejected  → ditolak, mahasiswa harus revisi
    |
    */

    'krs_status' => [
        'draft',
        'pending',
        'approved',
        'rejected',
    ],


    /*
    |--------------------------------------------------------------------------
    | Konversi Nilai
    |--------------------------------------------------------------------------
    |
    | Konversi nilai huruf berdasarkan range nilai angka.
    | Kamu bisa panggil dari service:
    | config('siakad.nilai_konversi')
    |
    */

    'nilai_konversi' => [
        ['min' => 85, 'max' => 100, 'huruf' => 'A',  'bobot' => 4.00],
        ['min' => 80, 'max' => 84,  'huruf' => 'A-', 'bobot' => 3.75],
        ['min' => 75, 'max' => 79,  'huruf' => 'B+', 'bobot' => 3.50],
        ['min' => 70, 'max' => 74,  'huruf' => 'B',  'bobot' => 3.00],
        ['min' => 65, 'max' => 69,  'huruf' => 'C+', 'bobot' => 2.50],
        ['min' => 60, 'max' => 64,  'huruf' => 'C',  'bobot' => 2.00],
        ['min' => 55, 'max' => 59,  'huruf' => 'D',  'bobot' => 1.00],
        ['min' => 0,  'max' => 54,  'huruf' => 'E',  'bobot' => 0.00],
    ],


    /*
    |--------------------------------------------------------------------------
    | Status Nilai
    |--------------------------------------------------------------------------
    */

    'nilai_status' => [
        'draft',        // nilai boleh diedit dosen
        'submitted',    // menunggu verifikasi
        'final',        // terkunci, tidak bisa diubah
    ],


    /*
    |--------------------------------------------------------------------------
    | Aturan Batas SKS Mahasiswa
    |--------------------------------------------------------------------------
    |
    | Digunakan jika nanti kau ingin sistem KRS otomatis menghitung SKS
    | berdasarkan IPS semester sebelumnya.
    |
    */

    'maks_sks' => [
        'default' => 24,
        'ips_rules' => [
            ['min' => 3.51, 'max' => 4.00, 'sks' => 24],
            ['min' => 3.01, 'max' => 3.50, 'sks' => 22],
            ['min' => 2.51, 'max' => 3.00, 'sks' => 20],
            ['min' => 2.00, 'max' => 2.50, 'sks' => 18],
            ['min' => 0.00, 'max' => 1.99, 'sks' => 14],
        ]
    ],


    /*
    |--------------------------------------------------------------------------
    | Kapasitas Kelas Default
    |--------------------------------------------------------------------------
    */

    'kelas_kapasitas_default' => 40,


    /*
    |--------------------------------------------------------------------------
    | Pagination Default
    |--------------------------------------------------------------------------
    */

    'pagination' => 15,


    /*
    |--------------------------------------------------------------------------
    | Format Tanggal Akademik
    |--------------------------------------------------------------------------
    */

    'format_tanggal' => 'd-m-Y',


    /*
    |--------------------------------------------------------------------------
    | General Settings
    |--------------------------------------------------------------------------
    */

    'app_name' => 'SIAKAD Universitas',
    'version'  => '1.0.0',

];
