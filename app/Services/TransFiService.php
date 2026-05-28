<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransFiService
{
    private string $endpoint;
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        $this->endpoint     = config('services.transfi.endpoint');
        $this->clientId     = config('services.transfi.client_id');
        $this->clientSecret = config('services.transfi.client_secret');
    }

    /**
     * Create (or reuse) a TransFi individual user and return the UX- ID.
     */
    public function ensureTransFiUser(TelegramUser $user): ?string
    {
        if ($user->transfi_user_id) {
            return $user->transfi_user_id;
        }

        $nameParts = explode(' ', $user->first_name ?? 'User', 2);
        $firstName = $nameParts[0];
        $lastName  = $nameParts[1] ?? 'User';

        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->withHeaders(['MID' => config('services.transfi.mid')])
            ->post("{$this->endpoint}/v3/users/individual", [
                'firstName'          => $firstName,
                'lastName'           => $lastName,
                'date'               => '01-01-1990',
                'email'              => $user->email,
                'phone'              => '0000000000',
                'phoneCode'          => '+62',
                'country'            => 'ID',
                'countryOfResidence' => 'ID',
                'address'            => [
                    'addressLine1' => 'Jl. Jenderal Sudirman No. 1',
                    'city'         => 'Jakarta Pusat',
                    'state'        => 'DKI Jakarta',
                    'postCode'     => '10220',
                    'country'      => 'ID',
                ],
            ]);

        Log::info('TransFi createUser response', $response->json() ?? []);

        if (!$response->successful()) {
            Log::error('TransFi createUser error', $response->json() ?? []);
            return null;
        }

        $data = $response->json();

        // Try common field paths for the UX- ID
        $transfiUserId = $data['data']['userId']
            ?? $data['data']['id']
            ?? $data['userId']
            ?? $data['id']
            ?? null;

        if (!$transfiUserId) {
            Log::error('TransFi createUser: could not find userId in response', $data);
            return null;
        }

        $user->update(['transfi_user_id' => $transfiUserId]);

        return $transfiUserId;
    }

    /**
     * Create a hosted payment order and return the checkout URL.
     * Returns ['invoice_url' => string, 'payment_id' => string] or null on failure.
     */
    public function createInvoice(TelegramUser $user, array $package): ?array
    {
        $payment = Payment::create([
            'telegram_user_id' => $user->id,
            'package_name'     => $package['name'],
            'credits'          => $package['credits'],
            'price_usd'        => $package['price'],
        ]);

        $botUsername = $user->lastBotUsername() ?? config('app.name');

        // Confirmed: TransFi uses Basic auth (username:password from Settings → Integration tab)
        $body = [
            'orderType'          => 'payin',
            'purposeCode'        => 'software_export_or_development',
            'userId'             => $this->ensureTransFiUser($user),
            'partnerId'          => (string) $payment->id,
            'source'             => [
                'currency' => config('services.transfi.source_currency', 'USDT'),
                'amount'   => (string) $package['price'],
                // TODO: CONFIRM paymentType/paymentCode for crypto source payments
                // Example for fiat: 'paymentType' => 'bank_transfer', 'paymentCode' => 'swift'
            ],
            'destination'        => [
                'currency' => config('services.transfi.dest_currency', 'USDT'),
            ],
            'successRedirectUrl' => 'https://t.me/' . $botUsername,
            'failureRedirectUrl' => 'https://t.me/' . $botUsername,
            'customerMetaData'   => [
                'customerId' => (string) $user->id,
            ],
        ];

        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->withHeaders(['MID' => config('services.transfi.mid')])
            ->post("{$this->endpoint}/v3/orders", $body);

        if ($response->successful()) {
            $data = $response->json();

            // Confirmed: response structure is { "status": "success", "data": { "orderId": "...", "payUrl": "..." } }
            $invoiceUrl        = $data['data']['payUrl']    ?? null;
            $providerInvoiceId = $data['data']['orderId']   ?? null;

            if (!$invoiceUrl) {
                Log::error('TransFi: could not extract payUrl from response', $data);
                $payment->update(['status' => 'failed']);
                return null;
            }

            $payment->update(['provider_invoice_id' => $providerInvoiceId]);

            return [
                'invoice_url' => $invoiceUrl,
                'payment_id'  => $payment->id,
            ];
        }

        Log::error('TransFi invoice error:', $response->json() ?? []);
        $payment->update(['status' => 'failed']);

        return null;
    }

    public function verifySignature(string $payload, string $signature): bool
    {
        $secret = config('services.transfi.webhook_secret');

        // Confirmed: HMAC-SHA256 of the raw request body, compared against X-Transfi-Hmac-Hash header
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
