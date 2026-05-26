<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'provider_invoice_id',
        'provider_payment_id',
        'package_name',
        'credits',
        'price_usd',
        'status',
    ];

    public function telegramUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class);
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['completed', 'success', 'confirmed', 'finished']);
    }
}