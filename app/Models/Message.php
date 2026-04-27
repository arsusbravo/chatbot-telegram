<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['telegram_user_id', 'bot_id', 'role', 'content'];

    public function telegramUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class);
    }

    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }
}