<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'cuti', 'do', 'lulus'])->default('aktif')->after('angkatan');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->boolean('is_closed')->default(false)->after('kapasitas');
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('is_closed');
        });
    }
};
