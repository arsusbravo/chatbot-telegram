<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Message;
use App\Models\TelegramUser;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_bots' => Bot::count(),
                'active_bots' => Bot::where('is_active', true)->count(),
                'total_users' => TelegramUser::count(),
                'total_messages' => Message::count(),
                'messages_today' => Message::whereDate('created_at', Carbon::today())->count(),
            ],
        ]);
    }
}