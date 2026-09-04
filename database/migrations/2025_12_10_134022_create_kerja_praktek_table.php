<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kerja_praktek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('pembimbing_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->string('nama_perusahaan');
            $table->string('alamat_perusahaan')->nullable();
            $table->string('bidang_usaha')->nullable();
            $table->string('nama_pembimbing_lapangan')->nullable();
            $table->string('jabatan_pembimbing_lapangan')->nullable();
            $table->string('no_telp_pembimbing')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('judul_laporan', 500)->nullable();
            $table->enum('status', [
                'pengajuan',
                'disetujui',
                'ditolak',
                'berlangsung',
                'selesai_kp',
                'penyusunan_laporan',
                'seminar',
                'revisi',
                'selesai',
            ])->default('pengajuan');
            $table->date('tanggal_seminar')->nullable();
            $table->decimal('nilai_perusahaan', 5, 2)->nullable();
            $table->decimal('nilai_pembimbing', 5, 2)->nullable();
            $table->decimal('nilai_seminar', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('nilai_huruf', 5)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('mahasiswa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kerja_praktek');
    }
};
