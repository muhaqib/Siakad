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
        // 2. Chart: Tren Mingguan Pembayaran (Online vs Offline)
        // -------------------------------------------------------------
        $weeklyData = [];
        $totalOnlineCount = 0;
        $totalOfflineCount = 0;
        $totalOnlineAmount = 0;
        $totalOfflineAmount = 0;

        // Ambil 8 minggu terakhir hingga minggu ini
        for ($i = 7; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek(); // Senin 00:00:00
            $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();     // Minggu 23:59:59

            $weekShortLabel = 'Mg '.(8 - $i);
            $weekDateLabel = $startOfWeek->format('d M').' - '.$endOfWeek->format('d M');

            $historiesQuery = PaymentHistory::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->whereIn('action', ['confirmed', 'partial_payment'])
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
                })
                ->with('payment');

            $histories = $historiesQuery->get();

            $onlineCount = 0;
            $offlineCount = 0;
            $onlineAmount = 0;
            $offlineAmount = 0;

            foreach ($histories as $h) {
                $payment = $h->payment;
                $isOnline = false;

                if ($payment) {
                    $method = strtolower($payment->payment_method ?? '');
                    $notes = strtolower($h->notes ?? '');
                    if (! empty($payment->midtrans_order_id) || in_array($method, ['midtrans', 'qris', 'virtual account', 'transfer']) || str_contains($notes, 'midtrans')) {
                        $isOnline = true;
                    }
                }

                if ($isOnline) {
                    $onlineCount++;
                    $onlineAmount += (float) $h->amount;
                } else {
                    $offlineCount++;
                    $offlineAmount += (float) $h->amount;
                }
            }

            $totalOnlineCount += $onlineCount;
            $totalOfflineCount += $offlineCount;
            $totalOnlineAmount += $onlineAmount;
            $totalOfflineAmount += $offlineAmount;

            $weeklyData[] = [
                'week_short' => $weekShortLabel,
                'date_range' => $weekDateLabel,
                'online_count' => $onlineCount,
                'offline_count' => $offlineCount,
                'online_amount' => $onlineAmount,
                'offline_amount' => $offlineAmount,
                'total_count' => $onlineCount + $offlineCount,
            ];
        }

        $weeklyPayments = [
            'weeks' => $weeklyData,
            'summary' => [
                'total_online_count' => $totalOnlineCount,
                'total_offline_count' => $totalOfflineCount,
                'total_online_amount' => $totalOnlineAmount,
                'total_offline_amount' => $totalOfflineAmount,
                'total_transaksi' => $totalOnlineCount + $totalOfflineCount,
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
