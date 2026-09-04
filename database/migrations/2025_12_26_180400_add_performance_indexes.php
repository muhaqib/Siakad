<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Performance indexes for frequently queried columns.
     * These indexes significantly improve query performance for common operations.
     */
    public function up(): void
    {
        // KRS table indexes
        Schema::table('krs', function (Blueprint $table) {
            // Index for status filtering (pending, approved, rejected, draft)
            $table->index('status', 'krs_status_index');
            
            // Composite index for mahasiswa + tahun akademik lookup
            $table->unique(['mahasiswa_id', 'tahun_akademik_id'], 'krs_mahasiswa_tahun_unique');
        });

        // KRS Detail table indexes
        Schema::table('krs_detail', function (Blueprint $table) {
            // Prevent duplicate kelas in same KRS
            $table->unique(['krs_id', 'kelas_id'], 'krs_detail_krs_kelas_unique');
        });

        // Nilai table indexes
        Schema::table('nilai', function (Blueprint $table) {
            // Prevent duplicate nilai for same mahasiswa + kelas
            $table->unique(['mahasiswa_id', 'kelas_id'], 'nilai_mahasiswa_kelas_unique');
        });

        // Kelas table indexes
        Schema::table('kelas', function (Blueprint $table) {
            // Index for mata kuliah lookup
            $table->index('mata_kuliah_id', 'kelas_mata_kuliah_index');
            
            // Index for dosen lookup
            $table->index('dosen_id', 'kelas_dosen_index');
        });

        // Mahasiswa table indexes
        Schema::table('mahasiswa', function (Blueprint $table) {
            // Index for angkatan filtering
            $table->index('angkatan', 'mahasiswa_angkatan_index');
            
            // Index for prodi lookup (fakultas scoping)
            $table->index('prodi_id', 'mahasiswa_prodi_index');
        });

        // Dosen table - add index for prodi if exists
        if (Schema::hasColumn('dosen', 'prodi_id')) {
            Schema::table('dosen', function (Blueprint $table) {
                $table->index('prodi_id', 'dosen_prodi_index');
            });
        }

        // Pertemuan table indexes
        Schema::table('pertemuan', function (Blueprint $table) {
            // Index for jadwal lookup
            $table->index('jadwal_kuliah_id', 'pertemuan_jadwal_index');
            
            // Index for date range queries
            $table->index('tanggal', 'pertemuan_tanggal_index');
        });

        // Jadwal Kuliah indexes
        if (Schema::hasTable('jadwal_kuliah')) {
            Schema::table('jadwal_kuliah', function (Blueprint $table) {
                $table->index('kelas_id', 'jadwal_kuliah_kelas_index');
                $table->index('hari', 'jadwal_kuliah_hari_index');
            });
        }

        // Activity Log indexes for better log querying
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index('user_id', 'activity_logs_user_index');
                $table->index('created_at', 'activity_logs_created_at_index');
            });
        }

        // AI Conversation Logs indexes
        if (Schema::hasTable('ai_conversation_logs')) {
            Schema::table('ai_conversation_logs', function (Blueprint $table) {
                $table->index('mahasiswa_id', 'ai_logs_mahasiswa_index');
                $table->index('created_at', 'ai_logs_created_at_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->dropIndex('krs_status_index');
            $table->dropUnique('krs_mahasiswa_tahun_unique');
        });

        Schema::table('krs_detail', function (Blueprint $table) {
            $table->dropUnique('krs_detail_krs_kelas_unique');
        });

        Schema::table('nilai', function (Blueprint $table) {
            $table->dropUnique('nilai_mahasiswa_kelas_unique');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropIndex('kelas_mata_kuliah_index');
            $table->dropIndex('kelas_dosen_index');
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropIndex('mahasiswa_angkatan_index');
            $table->dropIndex('mahasiswa_prodi_index');
        });

        if (Schema::hasColumn('dosen', 'prodi_id')) {
            Schema::table('dosen', function (Blueprint $table) {
                $table->dropIndex('dosen_prodi_index');
            });
        }

        Schema::table('pertemuan', function (Blueprint $table) {
            $table->dropIndex('pertemuan_jadwal_index');
            $table->dropIndex('pertemuan_tanggal_index');
        });

        if (Schema::hasTable('jadwal_kuliah')) {
            Schema::table('jadwal_kuliah', function (Blueprint $table) {
                $table->dropIndex('jadwal_kuliah_kelas_index');
                $table->dropIndex('jadwal_kuliah_hari_index');
            });
        }

        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropIndex('activity_logs_user_index');
                $table->dropIndex('activity_logs_created_at_index');
            });
        }

        if (Schema::hasTable('ai_conversation_logs')) {
            Schema::table('ai_conversation_logs', function (Blueprint $table) {
                $table->dropIndex('ai_logs_mahasiswa_index');
                $table->dropIndex('ai_logs_created_at_index');
            });
        }
    }
};
