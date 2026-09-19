<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentTypeController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    public function index()
    {
        $this->authorizeSuperAdmin();

        $paymentTypes = PaymentType::orderBy('id')->get();

        return view('admin.payments.types.index', compact('paymentTypes'));
    }

    public function edit(PaymentType $paymentType)
    {
        $this->authorizeSuperAdmin();

        return view('admin.payments.types.edit', compact('paymentType'));
    }

    public function update(Request $request, PaymentType $paymentType)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_amount' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $old = $paymentType->toArray();
        $paymentType->update($validated);

        $this->activityLogService->log(
            action: "Mengubah konfigurasi jenis pembayaran {$paymentType->name}.",
            model: $paymentType,
            changes: ['old' => $old, 'new' => $paymentType->fresh()->toArray()]
        );

        return redirect()->route('admin.payment-types.index')->with('success', "Jenis pembayaran {$paymentType->name} berhasil diperbarui.");
    }

    protected function authorizeSuperAdmin(): void
    {
        if (! Auth::user()->isSuperAdmin()) {
            abort(403, 'Hanya Superadmin yang berwenang mengelola jenis pembayaran.');
        }
    }
}
