<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Message;
use App\Models\TelegramUser;
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

        $message = $request->input('message');

        if (!$message || !isset($message['text'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = $message['chat']['id'];
        $text = $message['text'];
        $from = $message['from'];

        $user = TelegramUser::firstOrCreate(
            ['telegram_id' => $from['id']],
            [
                'first_name' => $from['first_name'] ?? null,
                'username' => $from['username'] ?? null,
            ]
        );

        // Check credits
        if (!$user->canChat()) {
            $this->sendMessage($bot, $chatId, "Maaf sayang, pesan gratismu sudah habis 😢\nKalau mau lanjut ngobrol, kamu bisa beli kredit di website kami 💕");
            return response()->json(['ok' => true]);
        }

        // Store user message
        $user->messages()->create([
            'role' => 'user',
            'content' => $text,
            'bot_id' => $bot->id,
        ]);

        $this->sendTypingAction($bot, $chatId);

        $reply = $this->getAiResponse($user, $bot);

        $user->messages()->create([
            'role' => 'assistant',
            'content' => $reply,
            'bot_id' => $bot->id,
        ]);

        // Consume credit only on successful reply
        $user->consumeCredit();

        $this->sendMessage($bot, $chatId, $reply);

        return response()->json(['ok' => true]);
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
                'role' => $msg->role,
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
            'model' => config('services.openrouter.model'),
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
            'text' => $text,
        ]);
    }

    private function sendTypingAction(Bot $bot, int $chatId): void
    {
        Http::post(config('services.telegram.endpoint') . "{$bot->telegram_token}/sendChatAction", [
            'chat_id' => $chatId,
            'action' => 'typing',
        ]);
    }
}