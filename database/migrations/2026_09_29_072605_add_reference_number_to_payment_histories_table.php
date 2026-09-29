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
        Schema::table('payment_histories', function (Blueprint $table) {
            $table->string('reference_number', 100)->nullable()->after('amount');
            $table->index('reference_number');
        });

        // Backfill existing payment histories
        DB::table('payment_histories')->orderBy('id')->chunk(100, function ($histories) {
            foreach ($histories as $history) {
                $ref = null;
                if (! empty($history->notes) && preg_match('/Ref:\s*([^\s|]+)/i', $history->notes, $matches)) {
                    $ref = trim($matches[1]);
                }

                if (! $ref) {
                    $payment = DB::table('student_payments')->where('id', $history->student_payment_id)->first();
                    if ($payment) {
                        $ref = ! empty($payment->midtrans_order_id) ? $payment->midtrans_order_id : $payment->invoice_number;
                    }
                }

                if (! $ref) {
                    $ref = 'HIST-'.str_pad($history->id, 6, '0', STR_PAD_LEFT);
                }

                DB::table('payment_histories')->where('id', $history->id)->update([
                    'reference_number' => $ref,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_histories', function (Blueprint $table) {
            $table->dropIndex(['reference_number']);
            $table->dropColumn('reference_number');
        });
    }
};
