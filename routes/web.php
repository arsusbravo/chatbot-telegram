<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TelegramUserController;
use App\Http\Controllers\WelcomeController;

Route::get('/', WelcomeController::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('bots', BotController::class)->except(['show']);

    Route::get('/telegram-users', [TelegramUserController::class, 'index'])->name('telegram-users.index');
    Route::get('/telegram-users/{telegramUser}', [TelegramUserController::class, 'show'])->name('telegram-users.show');
    Route::put('/telegram-users/{telegramUser}/credits', [TelegramUserController::class, 'updateCredits'])->name('telegram-users.update-credits');
});

require __DIR__.'/settings.php';
