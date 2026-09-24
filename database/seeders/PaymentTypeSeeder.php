<?php

namespace Database\Seeders;

use App\Models\PaymentType;
use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'code' => 'registration',
                'name' => 'Pendaftaran Mahasiswa Baru',
                'category' => 'registration',
                'semester' => null,
                'description' => 'Biaya pendaftaran dan administrasi awal masuk mahasiswa baru STIT Mambaul Hikmah.',
                'default_amount' => 1925000,
                'is_active' => true,
            ],
            [
                'code' => 'semester_1',
                'name' => 'Pembayaran Kuliah Semester 1',
                'category' => 'semester',
                'semester' => 1,
                'description' => 'Biaya SPP semester 1.',
                'default_amount' => 1200000,
                'is_active' => true,
            ],
            [
                'code' => 'semester_2',
                'name' => 'Pembayaran Kuliah Semester 2',
                'category' => 'semester',
                'semester' => 2,
                'description' => 'Biaya SPP semester 2.',
                'default_amount' => 1200000,
                'is_active' => true,
            ],
            [
                'code' => 'semester_3',
                'name' => 'Pembayaran Kuliah Semester 3',
                'category' => 'semester',
                'semester' => 3,
                'description' => 'Biaya SPP semester 3.',
                'default_amount' => 1200000,
                'is_active' => true,
            ],
            [
                'code' => 'semester_4',
                'name' => 'Pembayaran Kuliah Semester 4',
                'category' => 'semester',
                'semester' => 4,
                'description' => 'Biaya SPP semester 4.',
                'default_amount' => 1200000,
                'is_active' => true,
            ],
            [
                'code' => 'semester_5',
                'name' => 'Pembayaran Kuliah Semester 5',
                'category' => 'semester',
                'semester' => 5,
                'description' => 'Biaya SPP semester 5.',
                'default_amount' => 1200000,
                'is_active' => true,
            ],
            [
                'code' => 'semester_6',
                'name' => 'Pembayaran Kuliah Semester 6',
                'category' => 'semester',
                'semester' => 6,
                'description' => 'Biaya SPP semester 6.',
                'default_amount' => 1200000,
                'is_active' => true,
            ],
            [
                'code' => 'semester_7',
                'name' => 'Pembayaran Kuliah Semester 7',
                'category' => 'semester',
                'semester' => 7,
                'description' => 'Biaya SPP semester 7.',
                'default_amount' => 1200000,
                'is_active' => true,
            ],
            [
                'code' => 'semester_8',
                'name' => 'Pembayaran Kuliah Semester 8',
                'category' => 'semester',
                'semester' => 8,
                'description' => 'Biaya SPP semester 8.',
                'default_amount' => 1200000,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            PaymentType::firstOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
