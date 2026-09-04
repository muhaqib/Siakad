<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_submission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->string('file_path', 500);
            $table->string('file_name');
            $table->text('catatan')->nullable(); // Catatan dari mahasiswa
            $table->dateTime('submitted_at');
            $table->decimal('nilai', 5, 2)->nullable(); // Nilai dari dosen (0-100)
            $table->text('feedback')->nullable(); // Feedback dari dosen
            $table->dateTime('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // 1 submission per mahasiswa per tugas
            $table->unique(['tugas_id', 'mahasiswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_submission');
    }
};
