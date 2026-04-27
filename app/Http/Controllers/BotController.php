<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        // Auto-register webhook
        $this->registerWebhook($bot);

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

        // Re-register webhook if token changed
        if ($bot->wasChanged('telegram_token')) {
            $this->registerWebhook($bot);
        }

        return redirect()->route('bots.index');
    }

    public function destroy(Bot $bot): RedirectResponse
    {
        $bot->delete();

        return redirect()->route('bots.index');
    }

    private function registerWebhook(Bot $bot): void
    {
        $endpoint = config('services.telegram.endpoint');
        $webhookUrl = url("/api/telegram/webhook/{$bot->telegram_token}");

        Http::get("{$endpoint}{$bot->telegram_token}/setWebhook", [
            'url' => $webhookUrl,
        ]);
    }
}