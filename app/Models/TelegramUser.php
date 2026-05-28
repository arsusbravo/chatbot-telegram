<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramUser extends Model
{
    protected $fillable = ['telegram_id', 'first_name', 'username', 'email', 'transfi_user_id', 'free_messages_left', 'paid_credits'];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function canChat(int $cost = 1): bool
    {
        return ($this->free_messages_left + $this->paid_credits) >= $cost;
    }

    public function consumeCredit(int $cost = 1): void
    {
        $fromFree = min($this->free_messages_left, $cost);
        $fromPaid = $cost - $fromFree;

        if ($fromFree > 0) {
            $this->decrement('free_messages_left', $fromFree);
        }
        if ($fromPaid > 0) {
            $this->decrement('paid_credits', $fromPaid);
        }
    }

    public function lastBotUsername(): ?string
    {
        $lastMessage = $this->messages()->whereNotNull('bot_id')->latest()->first();
        return $lastMessage?->bot?->telegram_username;
    }
}