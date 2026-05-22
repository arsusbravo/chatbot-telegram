<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BotController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('bots/Index', [
            'bots' => Bot::withCount('messages')->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('bots/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'telegram_token' => 'required|string|unique:bots,telegram_token',
            'telegram_username' => 'required|string|unique:bots,telegram_username',
            'system_prompt' => 'required|string',
        ]);

        $bot = Bot::create($validated);

        $this->registerWebhook($bot);
        $this->fetchAndStoreAvatar($bot);

        return redirect()->route('bots.index');
    }

    public function edit(Bot $bot): Response
    {
        return Inertia::render('bots/Edit', [
            'bot' => $bot,
        ]);
    }

    public function update(Request $request, Bot $bot): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'telegram_token' => 'required|string|unique:bots,telegram_token,' . $bot->id,
            'telegram_username' => 'required|string|unique:bots,telegram_username,' . $bot->id,
            'system_prompt' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $bot->update($validated);

        if ($bot->wasChanged('telegram_token')) {
            $this->registerWebhook($bot);
            $this->fetchAndStoreAvatar($bot);
        }

        return redirect()->route('bots.index');
    }

    public function destroy(Bot $bot): RedirectResponse
    {
        Storage::disk('public')->delete("avatars/bot_{$bot->id}.jpg");
        $bot->delete();

        return redirect()->route('bots.index');
    }

    public function syncAvatar(Bot $bot): RedirectResponse
    {
        $this->fetchAndStoreAvatar($bot);

        return redirect()->route('bots.edit', $bot)->with('status', 'Avatar synced from Telegram.');
    }

    private function fetchAndStoreAvatar(Bot $bot): void
    {
        try {
            $endpoint = config('services.telegram.endpoint');

            $meResponse = Http::get("{$endpoint}{$bot->telegram_token}/getMe");
            if (!$meResponse->successful()) {
                return;
            }
            $botUserId = $meResponse->json('result.id');

            $photosResponse = Http::get("{$endpoint}{$bot->telegram_token}/getUserProfilePhotos", [
                'user_id' => $botUserId,
                'limit'   => 1,
            ]);
            if (!$photosResponse->successful()) {
                return;
            }

            $photos = $photosResponse->json('result.photos');
            if (empty($photos)) {
                return;
            }

            // Pick largest size (last item in sizes array)
            $sizes   = $photos[0];
            $largest = end($sizes);
            $fileId  = $largest['file_id'];

            $fileResponse = Http::get("{$endpoint}{$bot->telegram_token}/getFile", [
                'file_id' => $fileId,
            ]);
            if (!$fileResponse->successful()) {
                return;
            }

            $filePath = $fileResponse->json('result.file_path');
            $fileUrl  = "https://api.telegram.org/file/bot{$bot->telegram_token}/{$filePath}";

            $imageContents = Http::get($fileUrl)->body();
            if (empty($imageContents)) {
                return;
            }

            $storagePath = "avatars/bot_{$bot->id}.jpg";
            Storage::disk('public')->put($storagePath, $imageContents);

            $bot->update(['avatar_url' => Storage::disk('public')->url($storagePath)]);
        } catch (\Throwable $e) {
            Log::warning("Failed to fetch avatar for bot {$bot->id}: " . $e->getMessage());
        }
    }

    private function registerWebhook(Bot $bot): void
    {
        $endpoint   = config('services.telegram.endpoint');
        $webhookUrl = url("/api/telegram/webhook/{$bot->telegram_token}");

        Http::get("{$endpoint}{$bot->telegram_token}/setWebhook", [
            'url' => $webhookUrl,
        ]);
    }
}
