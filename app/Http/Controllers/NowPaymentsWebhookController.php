<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\NowPaymentsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NowPaymentsWebhookController extends Controller
{
    public function handle(Request $request, NowPaymentsService $service): JsonResponse
    {
        $signature = $request->header('x-nowpayments-sig');
        $payload = $request->getContent();

        if (!$signature || !$service->verifySignature($payload, $signature)) {
            Log::warning('NOWPayments: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $data = $request->all();
        $status = $data['payment_status'] ?? null;
        $orderId = $data['order_id'] ?? null;

        Log::info('NOWPayments webhook:', $data);

        if (!$orderId) {
            return response()->json(['ok' => true]);
        }

        $payment = Payment::find($orderId);

        if (!$payment) {
            Log::warning("NOWPayments: payment not found for order {$orderId}");
            return response()->json(['ok' => true]);
        }

        // Avoid double-crediting
        $alreadyPaid = $payment->isPaid();

        $payment->update([
            'nowpayments_payment_id' => $data['payment_id'] ?? null,
            'status' => $status,
        ]);

        // Add credits when confirmed or finished (only once)
        if (!$alreadyPaid && in_array($status, ['confirmed', 'finished'])) {
            $payment->telegramUser->increment('paid_credits', $payment->credits);

            Log::info("Added {$payment->credits} credits to user {$payment->telegram_user_id}");
        }

        return response()->json(['ok' => true]);
    }
}