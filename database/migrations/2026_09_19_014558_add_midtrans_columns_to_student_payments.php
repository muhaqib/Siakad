<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom Midtrans ke tabel student_payments.
     * Semua kolom nullable agar tidak merusak data yang sudah ada.
     */
    public function up(): void
    {
        Schema::table('student_payments', function (Blueprint $table) {
            // ID order unik yang dikirim ke Midtrans (format: INV-{id}-{timestamp})
            $table->string('midtrans_order_id')->nullable()->unique()->after('invoice_number');

            // Snap Token yang dikembalikan Midtrans untuk memicu popup pembayaran
            $table->string('midtrans_token', 500)->nullable()->after('midtrans_order_id');

            // Metode pembayaran yang dipilih pengguna di Snap (gopay, bca_va, mandiri_va, dll)
            $table->string('midtrans_payment_type')->nullable()->after('midtrans_token');

            // Index untuk mempercepat pencarian berdasarkan order_id dari webhook
            $table->index('midtrans_order_id');
        });
    }

    /**
     * Rollback: hapus kolom Midtrans.
     */
    public function down(): void
    {
        Schema::table('student_payments', function (Blueprint $table) {
            $table->dropIndex(['midtrans_order_id']);
            $table->dropColumn(['midtrans_order_id', 'midtrans_token', 'midtrans_payment_type']);
        });
    }
};
