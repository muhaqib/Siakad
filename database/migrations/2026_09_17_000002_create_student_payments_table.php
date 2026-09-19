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
        Schema::create('student_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('payment_type_id')->constrained('payment_types')->onDelete('restrict');
            $table->foreignId('tahun_akademik_id')->nullable()->constrained('tahun_akademik')->onDelete('set null');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', ['unpaid', 'paid', 'cancelled', 'refunded'])->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->date('payment_date')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'payment_type_id']);
            $table->index(['status', 'mahasiswa_id']);
            $table->index(['status', 'payment_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_payments');
    }
};
