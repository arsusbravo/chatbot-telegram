<?php

namespace App\Jobs;

use App\Models\Bot;
use App\Models\Message;
use App\Models\TelegramUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(
        public readonly Bot $bot,
        public readonly TelegramUser $user,
        public readonly int $chatId,
        public readonly string $userText,
    ) {}

    public function handle(): void
    {
        $this->user->messages()->create([
            'role'    => 'user',
            'content' => $this->userText,
            'bot_id'  => $this->bot->id,
        ]);

        $reply = $this->getAiResponse();

        $this->user->messages()->create([
            'role'    => 'assistant',
            'content' => $reply,
            'bot_id'  => $this->bot->id,
        ]);

        $this->user->consumeCredit();

        $endpoint = config('services.telegram.endpoint');
        Http::post("{$endpoint}{$this->bot->telegram_token}/sendMessage", [
            'chat_id' => $this->chatId,
            'text'    => $reply,
        ]);
    }

    private function getAiResponse(): string
    {
        $history = $this->user->messages()
            ->where('bot_id', $this->bot->id)
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
            ['role' => 'system', 'content' => $this->bot->system_prompt],
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
}
