<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\PaymentHistory;
use App\Models\PaymentType;
use App\Models\StudentPayment;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;

class PaymentInitializationService
{
    /**
     * Initialize payment obligations for a student (Registration and Semester 1-8).
     *
     * @return int Number of payment records newly created
     */
    public function initializeStudentPayments(Mahasiswa $mahasiswa, ?TahunAkademik $tahunAkademik = null): int
    {
        $tahunAkademik = $tahunAkademik ?? TahunAkademik::where('is_active', true)->first();
        $activePaymentTypes = PaymentType::active()->orderBy('id')->get();

        if ($activePaymentTypes->isEmpty()) {
            return 0;
        }

        $createdCount = 0;

        DB::transaction(function () use ($mahasiswa, $tahunAkademik, $activePaymentTypes, &$createdCount) {
            $year = date('Y');
            $nim = preg_replace('/[^A-Za-z0-9]/', '', $mahasiswa->nim);

            foreach ($activePaymentTypes as $paymentType) {
                $invoiceNumber = "INV/{$year}/{$nim}/".strtoupper($paymentType->code);

                // Prevent duplicate by checking mahasiswa_id and payment_type_id
                $exists = StudentPayment::where('mahasiswa_id', $mahasiswa->id)
                    ->where('payment_type_id', $paymentType->id)
                    ->exists();

                if (! $exists) {
                    $payment = StudentPayment::create([
                        'mahasiswa_id' => $mahasiswa->id,
                        'payment_type_id' => $paymentType->id,
                        'tahun_akademik_id' => $tahunAkademik?->id,
                        'invoice_number' => $invoiceNumber,
                        'amount' => $paymentType->default_amount,
                        'paid_amount' => 0,
                        'status' => 'unpaid',
                        'notes' => "Kewajiban {$paymentType->name}",
                    ]);

                    PaymentHistory::create([
                        'student_payment_id' => $payment->id,
                        'action' => 'created',
                        'old_status' => null,
                        'new_status' => 'unpaid',
                        'amount' => $payment->amount,
                        'notes' => 'Tagihan dibuat otomatis oleh sistem saat inisialisasi.',
                        'performed_by' => auth()->id(),
                    ]);

                    $createdCount++;
                }
            }
        });

        return $createdCount;
    }
}
