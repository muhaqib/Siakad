<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_conversation_logs', function (Blueprint $table) {
            $table->id();
            
            // User & session tracking
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->string('session_id')->nullable()->index(); // Group conversations
            
            // Question & Answer
            $table->text('question');
            $table->text('answer');
            $table->text('context_summary')->nullable(); // Summary of context sent
            
            // Performance metrics
            $table->integer('response_time_ms'); // Response time in milliseconds
            $table->string('model_used', 50); // gemini, qwen, etc.
            $table->string('provider', 50); // gemini, bytez, groq
            
            // Quality metrics
            $table->boolean('guard_applied')->default(false);
            $table->json('guard_issues')->nullable(); // Which guards triggered
            $table->boolean('was_retry')->default(false);
            
            // User feedback (optional)
            $table->enum('feedback', ['helpful', 'not_helpful', 'incorrect'])->nullable();
            $table->text('feedback_note')->nullable();
            
            $table->timestamps();
            
            // Indexes for analytics
            $table->index('created_at');
            $table->index(['mahasiswa_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversation_logs');
    }
};
