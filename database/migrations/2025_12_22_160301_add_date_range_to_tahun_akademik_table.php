<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahun_akademik', function (Blueprint $table) {
            $table->date('tanggal_mulai')->nullable()->after('is_active');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            $table->date('tanggal_krs_mulai')->nullable()->after('tanggal_selesai');
            $table->date('tanggal_krs_selesai')->nullable()->after('tanggal_krs_mulai');
        });
    }

    public function down(): void
    {
        Schema::table('tahun_akademik', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_mulai',
                'tanggal_selesai',
                'tanggal_krs_mulai',
                'tanggal_krs_selesai',
            ]);
        });
    }
};
