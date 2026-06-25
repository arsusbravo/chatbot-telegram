<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateSelfieJob;
use App\Models\Bot;
use App\Models\ImagePrompt;
use App\Models\Message;
use App\Models\TelegramUser;
use App\Services\TransFiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
                'first_name'          => $from['first_name'] ?? null,
                'username'            => $from['username'] ?? null,
                'free_messages_left'  => 10,
                'paid_credits'        => 0,
            ]
        );

        if ($text === '/start') {
            Cache::forget("selfie_confirmed_{$user->telegram_id}");
            $this->sendChatAction($bot, $chatId, 'typing');
            $this->sendMessage($bot, $chatId, __('messages.start_greeting'));
            return response()->json(['ok' => true]);
        }

        if (str_starts_with($text, '/buy')) {
            $this->sendPackageOptions($bot, $chatId);
            return response()->json(['ok' => true]);
        }

        // Email collection for payment
        $emailPendingKey = "payment_email_pending_{$user->telegram_id}";
        if (Cache::has($emailPendingKey)) {
            if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
                $this->sendMessage($bot, $chatId, __('messages.payment_email_invalid'));
                return response()->json(['ok' => true]);
            }

            $packageIndex = Cache::pull($emailPendingKey);
            $packages     = config('payment.packages');

            $user->update(['email' => $text]);

            $this->sendMessage($bot, $chatId, __('messages.payment_email_saved'));
            $this->sendChatAction($bot, $chatId, 'typing');

            $service = app(\App\Services\TransFiService::class);
            $invoice = $service->createInvoice($user, $packages[$packageIndex]);

            if ($invoice) {
                $pkg  = $packages[$packageIndex];
                $msg  = __('messages.payment_success', [
                    'name'    => $pkg['name'],
                    'credits' => $pkg['credits'],
                    'url'     => $invoice['invoice_url'],
                ]);
            } else {
                $msg = __('messages.payment_email_error');
            }

            $this->sendMessage($bot, $chatId, $msg);
            return response()->json(['ok' => true]);
        }

        // Selfie / nude request handling
        $selfieType = $this->getSelfieRequestType($text);
        if ($selfieType && $bot->avatar_url) {
            if (!$user->canChat(5)) {
                $this->sendPackageOptions($bot, $chatId, __('messages.selfie_no_credits'));
                return response()->json(['ok' => true]);
            }

            $lockKey = "selfie_pending_{$user->telegram_id}";

            if (Cache::has($lockKey)) {
                $this->sendMessage($bot, $chatId, __('messages.selfie_already_pending'));
                return response()->json(['ok' => true]);
            }

            $promptRow      = ImagePrompt::where('type', $selfieType)->inRandomOrder()->first();
            $imagePrompt    = $promptRow?->prompt;
            $negativePrompt = $promptRow?->negative_prompt;

            // First-time (or cache-cleared) confirmation gate
            if (!Cache::has("selfie_confirmed_{$user->telegram_id}")) {
                Cache::put("selfie_pending_confirm_{$user->telegram_id}", [
                    'text'            => $text,
                    'image_prompt'    => $imagePrompt,
                    'negative_prompt' => $negativePrompt,
                    'type'            => $selfieType,
                ], now()->addMinutes(10));
                $this->sendSelfieConfirmation($bot, $chatId);
                return response()->json(['ok' => true]);
            }

            // Already confirmed → dispatch immediately
            Cache::put($lockKey, true, now()->addMinutes(5));

            Log::info('Selfie dispatch', [
                'type'  => $selfieType,
                'label' => $promptRow?->label ?? '(none)',
            ]);

            $waitingMessages = __('messages.selfie_waiting');
            $this->sendMessage($bot, $chatId, $waitingMessages[array_rand($waitingMessages)]);
            $this->sendChatAction($bot, $chatId, 'upload_photo');

            GenerateSelfieJob::dispatch($bot, $user, $chatId, $text, $imagePrompt, $negativePrompt, $selfieType);

            return response()->json(['ok' => true]);
        }

        if (!$user->canChat()) {
            $this->sendPackageOptions($bot, $chatId, __('messages.chat_no_credits'));
            return response()->json(['ok' => true]);
        }

        $user->messages()->create([
            'role'    => 'user',
            'content' => $text,
            'bot_id'  => $bot->id,
        ]);

        $this->sendChatAction($bot, $chatId, 'typing');

        $reply = $this->getAiResponse($user, $bot);

        $user->messages()->create([
            'role'    => 'assistant',
            'content' => $reply,
            'bot_id'  => $bot->id,
        ]);

        $user->consumeCredit();

        $this->sendMessage($bot, $chatId, $reply);

        return response()->json(['ok' => true]);
    }

    private function getSelfieRequestType(string $text): ?string
    {
        $lower = mb_strtolower($text);

        $nudeKeywords = __('messages.nude_keywords');
        if (is_array($nudeKeywords)) {
            foreach ($nudeKeywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return 'nude';
                }
            }
        }

        $selfieKeywords = __('messages.selfie_keywords');
        if (is_array($selfieKeywords)) {
            foreach ($selfieKeywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return 'selfie';
                }
            }
        }

        return null;
    }

    private function sendPackageOptions(Bot $bot, int $chatId, string $prefix = ''): void
    {
        $packages = config('payment.packages');
        $text     = $prefix . __('messages.package_header');

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

    private function sendSelfieConfirmation(Bot $bot, int $chatId): void
    {
        Http::post(config('services.telegram.endpoint') . "{$bot->telegram_token}/sendMessage", [
            'chat_id'      => $chatId,
            'text'         => __('messages.selfie_confirm'),
            'reply_markup' => json_encode(['inline_keyboard' => [[
                ['text' => __('messages.selfie_confirm_yes'), 'callback_data' => 'selfie:confirm'],
                ['text' => __('messages.selfie_confirm_no'),  'callback_data' => 'selfie:decline'],
            ]]]),
        ]);
    }

    private function handleCallback(Bot $bot, array $callback): void
    {
        $chatId     = $callback['message']['chat']['id'];
        $from       = $callback['from'];
        $data       = $callback['data'] ?? '';
        $callbackId = $callback['id'];

        // Selfie confirmation callbacks
        if (str_starts_with($data, 'selfie:')) {
            Http::post(config('services.telegram.endpoint') . "{$bot->telegram_token}/answerCallbackQuery", [
                'callback_query_id' => $callbackId,
            ]);

            $user = TelegramUser::firstOrCreate(
                ['telegram_id' => $from['id']],
                [
                    'first_name' => $from['first_name'] ?? null,
                    'username'   => $from['username'] ?? null,
                ]
            );

            $pendingKey = "selfie_pending_confirm_{$user->telegram_id}";
            $pending    = Cache::get($pendingKey);
            Cache::forget($pendingKey);

            if ($data === 'selfie:confirm' && $pending) {
                Cache::put("selfie_confirmed_{$user->telegram_id}", true, now()->addDays(30));

                $lockKey = "selfie_pending_{$user->telegram_id}";
                if (!Cache::has($lockKey)) {
                    Cache::put($lockKey, true, now()->addMinutes(5));

                    $pendingType = $pending['type'] ?? 'selfie';

                    Log::info('Selfie dispatch (from confirm)', [
                        'type'             => $pendingType,
                        'image_prompt_raw' => $pending['image_prompt'],
                    ]);

                    $waitingMessages = __('messages.selfie_waiting');
                    $this->sendMessage($bot, $chatId, $waitingMessages[array_rand($waitingMessages)]);
                    $this->sendChatAction($bot, $chatId, 'upload_photo');

                    GenerateSelfieJob::dispatch($bot, $user, $chatId, $pending['text'], $pending['image_prompt'], $pending['negative_prompt'], $pendingType);
                }
                return;
            }

            if ($data === 'selfie:decline' && $pending) {
                if ($user->canChat()) {
                    $user->messages()->create([
                        'role'    => 'user',
                        'content' => $pending['text'],
                        'bot_id'  => $bot->id,
                    ]);
                    $this->sendChatAction($bot, $chatId, 'typing');
                    $reply = $this->getAiResponse($user, $bot);
                    $user->messages()->create([
                        'role'    => 'assistant',
                        'content' => $reply,
                        'bot_id'  => $bot->id,
                    ]);
                    $user->consumeCredit();
                    $this->sendMessage($bot, $chatId, $reply);
                } else {
                    $this->sendPackageOptions($bot, $chatId, __('messages.chat_no_credits'));
                }
            }

            return;
        }

        // Payment callbacks
        Http::post(config('services.telegram.endpoint') . "{$bot->telegram_token}/answerCallbackQuery", [
            'callback_query_id' => $callbackId,
            'text'              => __('messages.payment_creating'),
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

        // If user has no email yet, ask for it and hold the package selection in cache
        if (!$user->email) {
            Cache::put("payment_email_pending_{$user->telegram_id}", $packageIndex, now()->addMinutes(15));
            $this->sendMessage($bot, $chatId, __('messages.payment_email_request'));
            return;
        }

        $this->sendChatAction($bot, $chatId, 'typing');

        $service = app(TransFiService::class);
        $invoice = $service->createInvoice($user, $packages[$packageIndex]);

        if ($invoice) {
            $pkg  = $packages[$packageIndex];
            $text = __('messages.payment_success', [
                'name'    => $pkg['name'],
                'credits' => $pkg['credits'],
                'url'     => $invoice['invoice_url'],
            ]);
        } else {
            $text = __('messages.payment_error');
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
        ])->timeout(90)->post(config('services.openrouter.endpoint') . '/completions', [
            'model'    => config('services.openrouter.model'),
            'messages' => $messages,
        ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content') ?? __('messages.ai_confused');
        }

        if ($response->status() === 429) {
            return __('messages.ai_tired');
        }

        if ($response->status() === 402) {
            return __('messages.ai_unavailable');
        }

        Log::error('OpenRouter error:', $response->json() ?? []);
        return __('messages.ai_error');
    }

    private function sendMessage(Bot $bot, int $chatId, string $text): void
    {
        Http::post(config('services.telegram.endpoint') . "{$bot->telegram_token}/sendMessage", [
            'chat_id' => $chatId,
            'text'    => $text,
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
