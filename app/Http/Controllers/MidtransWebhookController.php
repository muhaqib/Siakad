<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService,
    ) {}

    /**
     * Terima dan proses notifikasi pembayaran dari Midtrans.
     *
     * Endpoint: POST /midtrans/notification
     * Route ini dikecualikan dari CSRF middleware karena dipanggil server Midtrans.
     *
     * Alur:
     * 1. Terima payload JSON dari Midtrans
     * 2. Verifikasi signature_key untuk keamanan
     * 3. Update status pembayaran di database
     * 4. Kirim notifikasi ke mahasiswa
     * 5. Return 200 OK agar Midtrans tidak retry
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans webhook received', [
            'order_id' => $payload['order_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'fraud_status' => $payload['fraud_status'] ?? null,
            'gross_amount' => $payload['gross_amount'] ?? null,
        ]);

        try {
            $payment = $this->midtransService->processNotification($payload);

            return response()->json([
                'status' => 'ok',
                'payment_id' => $payment->id,
                'new_status' => $payment->status,
            ]);
        } catch (ModelNotFoundException) {
            // Order ID tidak ditemukan — mungkin dari transaksi lain / test
            Log::warning('Midtrans webhook: order not found', ['order_id' => $payload['order_id'] ?? null]);

            // Return 200 agar Midtrans tidak terus retry
            return response()->json(['status' => 'order_not_found'], 200);
        } catch (\RuntimeException $e) {
            // Signature tidak valid
            Log::error('Midtrans webhook security error: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook unexpected error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return 500 agar Midtrans tahu ada masalah dan akan retry
            return response()->json(['status' => 'error', 'message' => 'Internal server error.'], 500);
        }
    }
}
