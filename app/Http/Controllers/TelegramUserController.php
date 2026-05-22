<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TelegramUserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('telegram-users/Index', [
            'users' => TelegramUser::withCount('messages')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function show(TelegramUser $telegramUser): Response
    {
        return Inertia::render('telegram-users/Show', [
            'telegramUser' => $telegramUser,
        ]);
    }

    public function updateCredits(Request $request, TelegramUser $telegramUser): RedirectResponse
    {
        $validated = $request->validate([
            'free_messages_left' => 'required|integer|min:0',
            'paid_credits' => 'required|integer|min:0',
        ]);

        $telegramUser->update($validated);

        return back();
    }
}