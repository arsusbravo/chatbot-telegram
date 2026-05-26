<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TransFiWebhookController;

Route::post('/transfi/webhook', [TransFiWebhookController::class, 'handle']);
Route::post('/telegram/webhook/{token}', [TelegramWebhookController::class, 'handle']);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
