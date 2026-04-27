<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramUser extends Model
{
    protected $fillable = ['telegram_id', 'first_name', 'username', 'free_messages_left', 'paid_credits'];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function canChat(): bool
    {
        return $this->free_messages_left > 0 || $this->paid_credits > 0;
    }

    public function consumeCredit(): void
    {
        if ($this->free_messages_left > 0) {
            $this->decrement('free_messages_left');
        } else {
            $this->decrement('paid_credits');
        }
    }
}