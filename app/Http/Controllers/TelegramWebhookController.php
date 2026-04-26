<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $message = $request->input('message');

        if (!$message || !isset($message['text'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = $message['chat']['id'];
        $text = $message['text'];

        // Show "typing..." while AI thinks
        $this->sendTypingAction($chatId);

        // Get AI response
        $reply = $this->getAiResponse($text);

        $this->sendMessage($chatId, $reply);

        return response()->json(['ok' => true]);
    }

    private function getAiResponse(string $userMessage): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openrouter.key'),
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => config('services.openrouter.model'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are Sara, a warm and caring girlfriend. You are sweet, supportive, and love to chat. Keep your replies conversational and not too long.',
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage,
                ],
            ],
        ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content') ?? 'Sorry, I couldn\'t think of a reply 🥺';
        }

        Log::error('OpenRouter error:', $response->json() ?? []);
        return 'Sorry, I\'m having a moment... try again? 💕';
    }

    private function sendMessage(int $chatId, string $text): void
    {
        Http::post('https://api.telegram.org/bot' . config('services.telegram.bot_token') . '/sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }

    private function sendTypingAction(int $chatId): void
    {
        Http::post('https://api.telegram.org/bot' . config('services.telegram.bot_token') . '/sendChatAction', [
            'chat_id' => $chatId,
            'action' => 'typing',
        ]);
    }
}