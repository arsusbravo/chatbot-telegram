<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Message;
use App\Models\TelegramUser;
use App\Services\ImageGenerationService;
use App\Services\NowPaymentsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, string $token): JsonResponse
    {
        $bot = Bot::where('telegram_token', $token)->where('is_active', true)->first();

        if (!$bot) {
            return response()->json(['ok' => false], 404);
        }

        $callback = $request->input('callback_query');
        if ($callback) {
            $this->handleCallback($bot, $callback);
            return response()->json(['ok' => true]);
        }

        $message = $request->input('message');

        if (!$message || !isset($message['text'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = $message['chat']['id'];
        $text   = $message['text'];
        $from   = $message['from'];

        $user = TelegramUser::firstOrCreate(
            ['telegram_id' => $from['id']],
            [
                'first_name' => $from['first_name'] ?? null,
                'username'   => $from['username'] ?? null,
            ]
        );

        if (str_starts_with($text, '/buy')) {
            $this->sendPackageOptions($bot, $chatId);
            return response()->json(['ok' => true]);
        }

        // Selfie request handling
        if ($this->isSelfieRequest($text) && $bot->avatar_url) {
            if (!$user->canChat(5)) {
                $this->sendPackageOptions($bot, $chatId,
                    "Mau selfie? Kreditnya habis dulu nih sayang 😢\n");
                return response()->json(['ok' => true]);
            }

            $this->sendMessage($bot, $chatId, 'Bentar ya sayang, lagi dandan dulu buat foto 📸✨');
            $this->sendChatAction($bot, $chatId, 'upload_photo');

            $imageUrl = app(ImageGenerationService::class)->generateSelfie($bot->avatar_url);

            if ($imageUrl) {
                $user->messages()->create(['role' => 'user',      'content' => $text,           'bot_id' => $bot->id]);
                $user->messages()->create(['role' => 'assistant', 'content' => '[selfie photo]', 'bot_id' => $bot->id]);
                $user->consumeCredit(5);
                $this->sendPhoto($bot, $chatId, $imageUrl);
            } else {
                $this->sendMessage($bot, $chatId,
                    'Aduh maaf sayang, fotonya gagal nih 😢 Coba lagi ya~');
            }

            return response()->json(['ok' => true]);
        }

        if (!$user->canChat()) {
            $this->sendPackageOptions($bot, $chatId, "Maaf sayang, aku ga bisa terus chat lagi 😢. Bapak ku marah-marah.\nKatanya kalau mau lanjut ngobrol suruh pilih paket buat lanjut 💕\n");
            return response()->json(['ok' => true]);
        }

        $user->messages()->create([
            'role'   => 'user',
            'content' => $text,
            'bot_id' => $bot->id,
        ]);

        $this->sendChatAction($bot, $chatId, 'typing');

        $reply = $this->getAiResponse($user, $bot);

        $user->messages()->create([
            'role'   => 'assistant',
            'content' => $reply,
            'bot_id' => $bot->id,
        ]);

        $user->consumeCredit();

        $this->sendMessage($bot, $chatId, $reply);

        return response()->json(['ok' => true]);
    }

    private function isSelfieRequest(string $text): bool
    {
        $keywords = [
            'selfie', 'foto', 'photo', 'gambar', 'kirim foto', 'send pic',
            'send photo', 'tunjukkan', 'tunjukkin', 'lihat foto', 'minta foto',
            'kirim gambar', 'fotonya', 'photonya', 'pic', 'picture', 'potret',
        ];

        $lower = mb_strtolower($text);
        foreach ($keywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function sendPackageOptions(Bot $bot, int $chatId, string $prefix = ''): void
    {
        $packages = config('payment.packages');
        $text     = $prefix . "💎 Pilih paket kredit chat:\n";

        $buttons = [];
        foreach ($packages as $i => $pkg) {
            $label     = "{$pkg['name']} — {$pkg['credits']} kredit — \${$pkg['price']}";
            $buttons[] = [['text' => $label, 'callback_data' => "buy:{$i}"]];
        }

        Http::post(config('services.telegram.endpoint') . "{$bot->telegram_token}/sendMessage", [
            'chat_id'      => $chatId,
            'text'         => $text,
            'reply_markup' => json_encode([
                'inline_keyboard' => $buttons,
            ]),
        ]);
    }

    private function handleCallback(Bot $bot, array $callback): void
    {
        $chatId     = $callback['message']['chat']['id'];
        $from       = $callback['from'];
        $data       = $callback['data'] ?? '';
        $callbackId = $callback['id'];

        Http::post(config('services.telegram.endpoint') . "{$bot->telegram_token}/answerCallbackQuery", [
            'callback_query_id' => $callbackId,
            'text'              => 'Sedang buat link pembayaran...',
        ]);

        if (!str_starts_with($data, 'buy:')) {
            return;
        }

        $packageIndex = (int) str_replace('buy:', '', $data);
        $packages     = config('payment.packages');

        if (!isset($packages[$packageIndex])) {
            return;
        }

        $user = TelegramUser::firstOrCreate(
            ['telegram_id' => $from['id']],
            [
                'first_name' => $from['first_name'] ?? null,
                'username'   => $from['username'] ?? null,
            ]
        );

        $this->sendChatAction($bot, $chatId, 'typing');

        $service = app(NowPaymentsService::class);
        $invoice = $service->createInvoice($user, $packages[$packageIndex]);

        if ($invoice) {
            $pkg  = $packages[$packageIndex];
            $text = "✅ Paket {$pkg['name']} ({$pkg['credits']} kredit)\n\n";
            $text .= "💳 Bayar di sini sayang:\n{$invoice['invoice_url']}\n\n";
            $text .= "Setelah bayar, kreditmu otomatis ditambahkan ya~ 💕";
        } else {
            $text = "⚠️ Maaf, pembayaran lagi gangguan. Coba lagi nanti ya sayang~";
        }

        $this->sendMessage($bot, $chatId, $text);
    }

    private function getAiResponse(TelegramUser $user, Bot $bot): string
    {
        $history = $user->messages()
            ->where('bot_id', $bot->id)
            ->latest()
            ->take(20)
            ->get()
            ->reverse()
            ->map(fn (Message $msg) => [
                'role'    => $msg->role,
                'content' => $msg->content,
            ])
            ->values()
            ->toArray();

        $messages = [
            ['role' => 'system', 'content' => $bot->system_prompt],
            ...$history,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openrouter.key'),
        ])->post(config('services.openrouter.endpoint') . '/completions', [
            'model'    => config('services.openrouter.model'),
            'messages' => $messages,
        ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content') ?? 'Maaf, aku lagi bingung... coba lagi ya 🥺';
        }

        if ($response->status() === 429) {
            return 'Sayang, aku lagi capek banget nih 😴 Coba chat aku lagi nanti ya~';
        }

        if ($response->status() === 402) {
            return 'Maaf sayang, aku lagi nggak bisa ngobrol sekarang 💔 Nanti aku kabarin lagi ya~';
        }

        Log::error('OpenRouter error:', $response->json() ?? []);
        return 'Aduh, ada yang error nih... coba lagi ya sayang 💕';
    }

    private function sendMessage(Bot $bot, int $chatId, string $text): void
    {
        Http::post(config('services.telegram.endpoint') . "{$bot->telegram_token}/sendMessage", [
            'chat_id' => $chatId,
            'text'    => $text,
        ]);
    }

    private function sendPhoto(Bot $bot, int $chatId, string $photoUrl): void
    {
        Http::post(config('services.telegram.endpoint') . "{$bot->telegram_token}/sendPhoto", [
            'chat_id' => $chatId,
            'photo'   => $photoUrl,
        ]);
    }

    private function sendChatAction(Bot $bot, int $chatId, string $action): void
    {
        Http::post(config('services.telegram.endpoint') . "{$bot->telegram_token}/sendChatAction", [
            'chat_id' => $chatId,
            'action'  => $action,
        ]);
    }
}
