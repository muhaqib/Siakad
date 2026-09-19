<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\PaymentType;
use App\Models\Prodi;
use App\Models\StudentPayment;
use App\Services\PaymentAccessService;
use App\Services\PaymentService;
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

        // Tagihan yang harus dibayar saat ini (unpaid payments)
        $pendingPaymentsQuery = StudentPayment::with(['mahasiswa.user', 'mahasiswa.prodi', 'paymentType'])
            ->where('status', 'unpaid')
            ->orderBy('id', 'asc');

        if ($fakultasId) {
            $pendingPaymentsQuery->forFakultas($fakultasId);
        }

        if (! empty($filters['prodi_id'])) {
            $pendingPaymentsQuery->whereHas('mahasiswa', fn ($q) => $q->where('prodi_id', $filters['prodi_id']));
        }

        $pendingPayments = $pendingPaymentsQuery->limit(10)->get();

        // Attach sequential prerequisite info without N+1 query
        $mahasiswaIds = $pendingPayments->pluck('mahasiswa_id')->unique();
        $unpaidPaymentsByMhs = StudentPayment::with('paymentType')
            ->whereIn('mahasiswa_id', $mahasiswaIds)
            ->unpaid()
            ->get()
            ->groupBy('mahasiswa_id');

        $pendingPayments->each(function ($p) use ($unpaidPaymentsByMhs) {
            $mhsUnpaid = $unpaidPaymentsByMhs->get($p->mahasiswa_id, collect());
            $pOrder = $p->getSequenceOrder();
            $prereq = $mhsUnpaid->filter(fn ($up) => $up->id !== $p->id && $up->getSequenceOrder() < $pOrder)
                ->sortBy(fn ($up) => $up->getSequenceOrder())
                ->first();
            $p->unpaid_prereq = $prereq;
            $p->is_ready = ($prereq === null);
        });

        // Recent transactions
        $recentPaymentsQuery = StudentPayment::with(['mahasiswa.user', 'mahasiswa.prodi', 'paymentType', 'confirmedBy'])
            ->orderBy('updated_at', 'desc')
            ->limit(10);

        if ($fakultasId) {
            $recentPaymentsQuery->forFakultas($fakultasId);
        }
        $recentPayments = $recentPaymentsQuery->get();

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
            'pendingPayments',
            'recentPayments',
            'fakultasList',
            'prodiList',
            'angkatanList',
            'paymentTypes',
            'filters',
            'user'
        ));
    }
}
