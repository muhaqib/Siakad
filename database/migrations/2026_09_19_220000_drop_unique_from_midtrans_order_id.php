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
        Schema::table('student_payments', function (Blueprint $table) {
            // Drop unique constraint to allow multi-bill payment under a single Midtrans transaction
            $table->dropUnique('student_payments_midtrans_order_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_payments', function (Blueprint $table) {
            $table->unique('midtrans_order_id', 'student_payments_midtrans_order_id_unique');
        });
    }
};
