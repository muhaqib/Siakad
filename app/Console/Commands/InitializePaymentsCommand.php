<?php

namespace App\Console\Commands;

use App\Models\Mahasiswa;
use App\Services\PaymentInitializationService;
use Illuminate\Console\Command;

class InitializePaymentsCommand extends Command
{
    protected $signature = 'payments:initialize
                            {--fakultas= : Filter by Fakultas ID}
                            {--prodi= : Filter by Prodi ID}
                            {--angkatan= : Filter by Angkatan year}
                            {--force : Force initialization without confirmation}';

    protected $description = 'Initialize payment obligations (Registration and Semester 1-8) for students';

    public function handle(PaymentInitializationService $initializationService): int
    {
        $query = Mahasiswa::with(['user', 'prodi.fakultas']);

        if ($fakultasId = $this->option('fakultas')) {
            $query->whereHas('prodi', fn ($q) => $q->where('fakultas_id', $fakultasId));
        }

        if ($prodiId = $this->option('prodi')) {
            $query->where('prodi_id', $prodiId);
        }

        if ($angkatan = $this->option('angkatan')) {
            $query->where('angkatan', $angkatan);
        }

        $totalStudents = $query->count();

        if ($totalStudents === 0) {
            $this->warn('Tidak ada data mahasiswa yang cocok dengan kriteria filter.');

            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$totalStudents} mahasiswa untuk inisialisasi pembayaran.");

        if (! $this->option('force') && ! $this->confirm('Apakah Anda yakin ingin melanjutkan inisialisasi tagihan pembayaran?')) {
            $this->info('Inisialisasi dibatalkan.');

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalStudents);
        $bar->start();

        $totalCreated = 0;

        $query->chunk(100, function ($students) use ($initializationService, $bar, &$totalCreated) {
            foreach ($students as $student) {
                $created = $initializationService->initializeStudentPayments($student);
                $totalCreated += $created;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Selesai! {$totalCreated} tagihan pembayaran baru berhasil dibuat untuk {$totalStudents} mahasiswa.");

        return Command::SUCCESS;
    }
}
