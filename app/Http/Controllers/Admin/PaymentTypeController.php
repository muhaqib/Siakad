<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentTypeRequest;
use App\Http\Requests\Admin\UpdatePaymentTypeRequest;
use App\Models\PaymentType;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentTypeController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    public function index(): View
    {
        $this->authorizeSuperAdmin();

        $paymentTypes = PaymentType::withCount('payments')
            ->orderBy('id')
            ->get();

        return view('admin.payments.types.index', compact('paymentTypes'));
    }

    public function create(): View
    {
        $this->authorizeSuperAdmin();

        return view('admin.payments.types.create');
    }

    public function store(StorePaymentTypeRequest $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validated();
        $data['code'] = strtoupper(trim($data['code']));

        if ($data['category'] !== 'semester') {
            $data['semester'] = null;
        }

        $paymentType = PaymentType::create($data);

        $this->activityLogService->log(
            action: "Menambahkan jenis pembayaran baru: {$paymentType->name} ({$paymentType->code}).",
            model: $paymentType,
            changes: ['new' => $paymentType->toArray()]
        );

        return redirect()
            ->route('admin.payment-types.index')
            ->with('success', "Jenis pembayaran {$paymentType->name} berhasil ditambahkan.");
    }

    public function edit(PaymentType $paymentType): View
    {
        $this->authorizeSuperAdmin();

        return view('admin.payments.types.edit', compact('paymentType'));
    }

    public function update(UpdatePaymentTypeRequest $request, PaymentType $paymentType): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validated();

        if (isset($validated['category']) && $validated['category'] !== 'semester') {
            $validated['semester'] = null;
        }

        $old = $paymentType->toArray();
        $paymentType->update($validated);

        $this->activityLogService->log(
            action: "Mengubah konfigurasi jenis pembayaran {$paymentType->name}.",
            model: $paymentType,
            changes: ['old' => $old, 'new' => $paymentType->fresh()->toArray()]
        );

        return redirect()
            ->route('admin.payment-types.index')
            ->with('success', "Jenis pembayaran {$paymentType->name} berhasil diperbarui.");
    }

    public function destroy(PaymentType $paymentType): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        if ($paymentType->payments()->exists()) {
            return redirect()
                ->route('admin.payment-types.index')
                ->with('error', "Jenis pembayaran {$paymentType->name} tidak dapat dihapus karena sudah memiliki data tagihan mahasiswa terkait. Silakan nonaktifkan status jenis pembayaran ini.");
        }

        $name = $paymentType->name;
        $code = $paymentType->code;
        $paymentType->delete();

        $this->activityLogService->log(
            action: "Menghapus jenis pembayaran: {$name} ({$code}).",
            model: $paymentType
        );

        return redirect()
            ->route('admin.payment-types.index')
            ->with('success', "Jenis pembayaran {$name} berhasil dihapus.");
    }

    protected function authorizeSuperAdmin(): void
    {
        if (! Auth::user()?->isSuperAdmin()) {
            abort(403, 'Hanya Superadmin yang berwenang mengelola jenis pembayaran.');
        }
    }
}
