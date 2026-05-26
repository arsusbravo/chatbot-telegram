<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\TransFiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransFiWebhookController extends Controller
{
    public function handle(Request $request, TransFiService $service): JsonResponse
    {
        // Confirmed: TransFi sends the signature in the X-Transfi-Hmac-Hash header
        $signature = $request->header('X-Transfi-Hmac-Hash') ?? '';
        $payload   = $request->getContent();

        if (!$signature || !$service->verifySignature($payload, $signature)) {
            Log::warning('TransFi webhook: invalid or missing signature');
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $data = $request->all();

        Log::info('TransFi webhook received:', $data);

        // Confirmed webhook payload structure:
        // { "entityId": "<transfi-orderId>", "entityType": "order", "status": "...",
        //   "order": { "orderId": "...", "customerOrderId": "<our partnerId>", ... } }
        $status         = $data['status'] ?? null;
        $transfiOrderId = $data['entityId'] ?? null;
        $ourPaymentId   = $data['order']['customerOrderId'] ?? null;

        // Look up our Payment record by the TransFi orderId we stored, with fallback to partnerId
        $payment = null;
        if ($transfiOrderId) {
            $payment = Payment::where('provider_invoice_id', $transfiOrderId)->first();
        }
        if (!$payment && $ourPaymentId) {
            $payment = Payment::find($ourPaymentId);
        }

        if (!$payment) {
            Log::warning('TransFi webhook: payment not found', [
                'entityId'        => $transfiOrderId,
                'customerOrderId' => $ourPaymentId,
            ]);
            return response()->json(['ok' => true]);
        }

        // Guard against double-crediting before updating status
        $alreadyPaid = $payment->isPaid();

        $payment->update(['status' => $status]);

        // Confirmed success statuses:
        // - 'completed' → gaming/payin final success (triggers balance top-up)
        // - 'asset_settled' → crypto payin received (intermediate confirm)
        if (!$alreadyPaid && in_array($status, ['completed', 'asset_settled'])) {
            $payment->telegramUser->increment('paid_credits', $payment->credits);

            Log::info("TransFi: added {$payment->credits} credits to user {$payment->telegram_user_id}");
        }

        return response()->json(['ok' => true]);
    }
}
