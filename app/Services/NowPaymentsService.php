<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NowPaymentsService
{
    private string $endpoint;
    private string $apiKey;

    public function __construct()
    {
        $this->endpoint = config('services.nowpayments.endpoint');
        $this->apiKey = config('services.nowpayments.api_key');
    }

    public function createInvoice(TelegramUser $user, array $package): ?array
    {
        $payment = Payment::create([
            'telegram_user_id' => $user->id,
            'package_name' => $package['name'],
            'credits' => $package['credits'],
            'price_usd' => $package['price'],
        ]);

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
        ])->post("{$this->endpoint}/invoice", [
            'price_amount' => $package['price'],
            'price_currency' => 'usd',
            'order_id' => (string) $payment->id,
            'order_description' => "{$package['credits']} chat credits - {$package['name']}",
            'ipn_callback_url' => url('/api/nowpayments/webhook'),
            'success_url' => 'https://t.me/' . ($user->lastBotUsername() ?? 'sara_ai_gf_bot'),
            'cancel_url' => 'https://t.me/' . ($user->lastBotUsername() ?? 'sara_ai_gf_bot'),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $payment->update(['nowpayments_invoice_id' => $data['id']]);

            return [
                'invoice_url' => $data['invoice_url'],
                'payment_id' => $payment->id,
            ];
        }

        Log::error('NOWPayments invoice error:', $response->json() ?? []);
        $payment->update(['status' => 'failed']);

        return null;
    }

    public function verifySignature(string $payload, string $signature): bool
    {
        $secret = config('services.nowpayments.ipn_secret');
        $data = json_decode($payload, true);
        ksort($data);
        $hash = hash_hmac('sha512', json_encode($data, JSON_UNESCAPED_UNICODE), $secret);

        return $hash === $signature;
    }
}