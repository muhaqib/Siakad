<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skripsi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('pembimbing1_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->foreignId('pembimbing2_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->string('judul', 500);
            $table->text('abstrak')->nullable();
            $table->string('bidang_kajian', 100)->nullable();
            $table->enum('status', [
                'pengajuan',      // Mahasiswa mengajukan judul
                'review',         // Dalam review pembimbing/admin
                'ditolak',        // Judul ditolak, perlu revisi
                'diterima',       // Judul diterima, mulai bimbingan
                'bimbingan',      // Dalam proses bimbingan
                'seminar_proposal', // Seminar proposal
                'penelitian',     // Dalam penelitian
                'seminar_hasil',  // Seminar hasil
                'sidang',         // Sidang akhir
                'revisi',         // Revisi pasca sidang
                'selesai',        // Skripsi selesai
            ])->default('pengajuan');
            $table->date('tanggal_pengajuan')->nullable();
            $table->date('tanggal_acc_judul')->nullable();
            $table->date('tanggal_seminar_proposal')->nullable();
            $table->date('tanggal_seminar_hasil')->nullable();
            $table->date('tanggal_sidang')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('nilai_huruf', 5)->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('mahasiswa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skripsi');
    }
};
