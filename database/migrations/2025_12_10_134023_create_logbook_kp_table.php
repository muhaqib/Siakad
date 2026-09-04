<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_kp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kerja_praktek_id')->constrained('kerja_praktek')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->text('kegiatan');
            $table->text('catatan_pembimbing')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'revisi'])->default('pending');
            $table->timestamps();
            
            $table->index(['kerja_praktek_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_kp');
    }
};
