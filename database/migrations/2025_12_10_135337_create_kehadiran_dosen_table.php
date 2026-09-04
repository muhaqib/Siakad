<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kehadiran_dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('dosen')->onDelete('cascade');
            $table->foreignId('jadwal_kuliah_id')->nullable()->constrained('jadwal_kuliah')->nullOnDelete();
            $table->foreignId('pertemuan_id')->nullable()->constrained('pertemuan')->nullOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'tugas', 'alpa'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->string('bukti_file')->nullable();
            $table->timestamps();
            
            $table->index(['dosen_id', 'tanggal']);
            $table->unique(['dosen_id', 'jadwal_kuliah_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadiran_dosen');
    }
};
