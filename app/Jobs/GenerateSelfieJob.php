<?php

namespace App\Jobs;

use App\Models\Bot;
use App\Models\TelegramUser;
use App\Services\ImageGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GenerateSelfieJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 200;

    public function __construct(
        public readonly Bot $bot,
        public readonly TelegramUser $user,
        public readonly int $chatId,
        public readonly string $userText,
    ) {}

    public function handle(ImageGenerationService $service): void
    {
        $endpoint = config('services.telegram.endpoint');
        $token    = $this->bot->telegram_token;

        $imageUrl = $service->generateSelfie($this->bot->avatar_url);

        if ($imageUrl) {
            $this->user->messages()->create(['role' => 'user',      'content' => $this->userText,   'bot_id' => $this->bot->id]);
            $this->user->messages()->create(['role' => 'assistant', 'content' => '[selfie photo]',  'bot_id' => $this->bot->id]);
            $this->user->consumeCredit(5);

            Http::post("{$endpoint}{$token}/sendPhoto", [
                'chat_id' => $this->chatId,
                'photo'   => $imageUrl,
            ]);
        } else {
            Http::post("{$endpoint}{$token}/sendMessage", [
                'chat_id' => $this->chatId,
                'text'    => 'Aduh maaf sayang, fotonya gagal nih 😢 Coba lagi ya~',
            ]);
        }
    }
}
