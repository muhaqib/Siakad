<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\PaymentHistory;
use App\Models\PaymentType;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Services\PaymentAccessService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentDashboardController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentAccessService $paymentAccessService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $fakultasId = $user->isSuperAdmin() ? $request->get('fakultas_id') : $user->fakultas_id;

        $filters = [
            'fakultas_id' => $fakultasId,
            'prodi_id' => $request->get('prodi_id'),
            'angkatan' => $request->get('angkatan'),
            'semester' => $request->get('semester'),
            'payment_type_id' => $request->get('payment_type_id'),
        ];

        $stats = $this->paymentService->getStatistics($fakultasId, $filters);

        $activeTahun = TahunAkademik::where('is_active', true)->first();

        // -------------------------------------------------------------
        // 1. Chart: Pembayaran Terselesaikan Pada Semester Ini (Seluruh Mahasiswa)
        // -------------------------------------------------------------
        $mahasiswaQuery = Mahasiswa::query()->where('status', 'aktif');
        if ($fakultasId) {
            $mahasiswaQuery->whereHas('prodi', fn ($q) => $q->where('fakultas_id', $fakultasId));
        }
        if (! empty($filters['prodi_id'])) {
            $mahasiswaQuery->where('prodi_id', $filters['prodi_id']);
        }
        if (! empty($filters['angkatan'])) {
            $mahasiswaQuery->where('angkatan', $filters['angkatan']);
        }

        $students = $mahasiswaQuery->with([
            'payments.paymentType',
            'prodi',
        ])->get();

        $lunasCount = 0;
        $partialCount = 0;
        $unpaidCount = 0;
        $totalNominalLunas = 0;
        $totalNominalTunggakan = 0;
        $prodiBreakdown = [];

        foreach ($students as $student) {
            $targetSem = ! empty($filters['semester'])
                ? (int) $filters['semester']
                : $this->paymentAccessService->determineStudentSemester($student, $activeTahun);

            // Cari tagihan target semester mahasiswa ini
            $payment = $student->payments->first(function ($p) use ($targetSem) {
                if (! $p->paymentType) {
                    return false;
                }
                // Jika semester 1 dan biaya pendaftaran belum lunas, jadikan prioritas
                if ($targetSem === 1 && $p->paymentType->category === 'registration' && ! $p->isPaid()) {
                    return true;
                }

                return $p->paymentType->category === 'semester' && $p->paymentType->semester == $targetSem;
            });

            if (! $payment) {
                $payment = $student->payments->first(function ($p) use ($targetSem) {
                    return $p->paymentType && $p->paymentType->semester == $targetSem;
                });
            }

            $prodiName = $student->prodi?->nama ?? 'Lainnya';
            if (! isset($prodiBreakdown[$prodiName])) {
                $prodiBreakdown[$prodiName] = [
                    'lunas' => 0,
                    'cicilan' => 0,
                    'belum_lunas' => 0,
                    'total' => 0,
                ];
            }
            $prodiBreakdown[$prodiName]['total']++;

            if ($payment) {
                if ($payment->isPaid()) {
                    $lunasCount++;
                    $totalNominalLunas += (float) $payment->paid_amount;
                    $prodiBreakdown[$prodiName]['lunas']++;
                } elseif ($payment->isPartial()) {
                    $partialCount++;
                    $totalNominalLunas += (float) $payment->paid_amount;
                    $totalNominalTunggakan += (float) $payment->remaining_amount;
                    $prodiBreakdown[$prodiName]['cicilan']++;
                } else {
                    $unpaidCount++;
                    $totalNominalTunggakan += (float) $payment->remaining_amount;
                    $prodiBreakdown[$prodiName]['belum_lunas']++;
                }
            } else {
                $unpaidCount++;
                $prodiBreakdown[$prodiName]['belum_lunas']++;
            }
        }

        $totalMhsSemester = $students->count();
        $persenSelesai = $totalMhsSemester > 0 ? round(($lunasCount / $totalMhsSemester) * 100, 1) : 0;

        $semesterCompletion = [
            'total_mahasiswa' => $totalMhsSemester,
            'lunas_count' => $lunasCount,
            'partial_count' => $partialCount,
            'unpaid_count' => $unpaidCount,
            'persen_selesai' => $persenSelesai,
            'total_nominal_lunas' => $totalNominalLunas,
            'total_nominal_tunggakan' => $totalNominalTunggakan,
            'active_semester_label' => $activeTahun ? ($activeTahun->tahun.' '.$activeTahun->semester) : 'Semester Ini',
            'prodi_breakdown' => $prodiBreakdown,
        ];

        // -------------------------------------------------------------
        // 2. Chart: Tren Mingguan Pembayaran (Cash, Transfer, VA / Midtrans)
        // -------------------------------------------------------------
        $weeklyData = [];
        $totalCashCount = 0;
        $totalTransferCount = 0;
        $totalVaCount = 0;
        $totalCashAmount = 0;
        $totalTransferAmount = 0;
        $totalVaAmount = 0;

        $validActions = [
            'confirmed',
            'partial_payment',
            'midtrans_settlement',
            'midtrans_capture',
            'midtrans_partial_settlement',
        ];

        // Ambil 8 minggu terakhir hingga minggu ini
        for ($i = 7; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek(); // Senin 00:00:00
            $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();     // Minggu 23:59:59

            $weekShortLabel = 'Mg '.(8 - $i);
            $weekDateLabel = $startOfWeek->format('d M').' - '.$endOfWeek->format('d M');

            $historiesQuery = PaymentHistory::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->where('amount', '>', 0)
                ->where(function ($q) use ($validActions) {
                    $q->whereIn('action', $validActions)
                        ->orWhere(function ($sub) {
                            $sub->where('action', 'like', 'midtrans_%')
                                ->whereNotIn('action', [
                                    'midtrans_initiated',
                                    'midtrans_expire',
                                    'midtrans_cancel',
                                    'midtrans_deny',
                                    'midtrans_pending',
                                ]);
                        });
                })
                ->whereHas('payment', function ($q) use ($fakultasId, $filters) {
                    if ($fakultasId) {
                        $q->forFakultas($fakultasId);
                    }
                    if (! empty($filters['prodi_id'])) {
                        $q->whereHas('mahasiswa', fn ($mq) => $mq->where('prodi_id', $filters['prodi_id']));
                    }
                    if (! empty($filters['angkatan'])) {
                        $q->whereHas('mahasiswa', fn ($mq) => $mq->where('angkatan', $filters['angkatan']));
                    }
                    if (! empty($filters['semester'])) {
                        $q->whereHas('paymentType', fn ($pq) => $pq->where('semester', $filters['semester']));
                    }
                    if (! empty($filters['payment_type_id'])) {
                        $q->where('payment_type_id', $filters['payment_type_id']);
                    }
                })
                ->with('payment');

            $histories = $historiesQuery->get();

            $cashCount = 0;
            $cashAmount = 0;
            $transferCount = 0;
            $transferAmount = 0;
            $vaCount = 0;
            $vaAmount = 0;

            foreach ($histories as $h) {
                $payment = $h->payment;
                $method = strtolower(trim($payment?->payment_method ?? ''));
                $notes = strtolower($h->notes ?? '');
                $action = strtolower($h->action ?? '');

                $isVa = ! empty($payment?->midtrans_order_id)
                    || ! empty($payment?->midtrans_payment_type)
                    || str_starts_with($action, 'midtrans_')
                    || str_contains($notes, 'midtrans')
                    || str_contains($method, 'midtrans')
                    || str_contains($method, 'virtual account')
                    || in_array($method, ['va', 'qris', 'gopay', 'shopeepay'])
                    || str_contains($method, 'echannel')
                    || str_contains($method, 'mandiri bill');

                $isTransfer = ! $isVa && (
                    str_contains($method, 'transfer')
                    || in_array($method, ['bank', 'bank transfer', 'transfer bank', 'manual_transfer'])
                );

                if ($isVa) {
                    $vaCount++;
                    $vaAmount += (float) $h->amount;
                } elseif ($isTransfer) {
                    $transferCount++;
                    $transferAmount += (float) $h->amount;
                } else {
                    $cashCount++;
                    $cashAmount += (float) $h->amount;
                }
            }

            $totalCashCount += $cashCount;
            $totalTransferCount += $transferCount;
            $totalVaCount += $vaCount;
            $totalCashAmount += $cashAmount;
            $totalTransferAmount += $transferAmount;
            $totalVaAmount += $vaAmount;

            $weeklyData[] = [
                'week_short' => $weekShortLabel,
                'date_range' => $weekDateLabel,
                'cash_count' => $cashCount,
                'cash_amount' => $cashAmount,
                'transfer_count' => $transferCount,
                'transfer_amount' => $transferAmount,
                'va_count' => $vaCount,
                'va_amount' => $vaAmount,
                'online_count' => $vaCount,
                'offline_count' => $cashCount + $transferCount,
                'online_amount' => $vaAmount,
                'offline_amount' => $cashAmount + $transferAmount,
                'total_count' => $cashCount + $transferCount + $vaCount,
                'total_amount' => $cashAmount + $transferAmount + $vaAmount,
            ];
        }

        $weeklyPayments = [
            'weeks' => $weeklyData,
            'summary' => [
                'total_cash_count' => $totalCashCount,
                'total_transfer_count' => $totalTransferCount,
                'total_va_count' => $totalVaCount,
                'total_cash_amount' => $totalCashAmount,
                'total_transfer_amount' => $totalTransferAmount,
                'total_va_amount' => $totalVaAmount,
                'total_online_count' => $totalVaCount,
                'total_offline_count' => $totalCashCount + $totalTransferCount,
                'total_online_amount' => $totalVaAmount,
                'total_offline_amount' => $totalCashAmount + $totalTransferAmount,
                'total_transaksi' => $totalCashCount + $totalTransferCount + $totalVaCount,
                'total_nominal' => $totalCashAmount + $totalTransferAmount + $totalVaAmount,
            ],
        ];

        // Filter options
        $fakultasList = $user->isSuperAdmin() ? Fakultas::orderBy('nama')->get() : collect();
        $prodiQuery = Prodi::query();
        if ($fakultasId) {
            $prodiQuery->where('fakultas_id', $fakultasId);
        }
        $prodiList = $prodiQuery->orderBy('nama')->get();

        $angkatanList = Mahasiswa::distinct()->whereNotNull('angkatan')->pluck('angkatan')->sort()->reverse();
        $paymentTypes = PaymentType::active()->orderBy('id')->get();

        return view('admin.payments.dashboard', compact(
            'stats',
            'semesterCompletion',
            'weeklyPayments',
            'fakultasList',
            'prodiList',
            'angkatanList',
            'paymentTypes',
            'filters',
            'user'
        ));
    }
}
