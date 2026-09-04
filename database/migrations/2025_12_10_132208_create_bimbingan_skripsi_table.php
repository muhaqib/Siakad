<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bimbingan_skripsi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skripsi_id')->constrained('skripsi')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('dosen')->onDelete('cascade');
            $table->date('tanggal_bimbingan');
            $table->text('catatan_mahasiswa')->nullable(); // Progress dari mahasiswa
            $table->text('catatan_dosen')->nullable();     // Feedback dari dosen
            $table->string('file_dokumen')->nullable();    // Upload dokumen
            $table->enum('status', ['menunggu', 'disetujui', 'revisi'])->default('menunggu');
            $table->timestamps();
            
            $table->index(['skripsi_id', 'tanggal_bimbingan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bimbingan_skripsi');
    }
};
