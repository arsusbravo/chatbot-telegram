<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bot extends Model
{
    protected $fillable = ['name', 'telegram_token', 'telegram_username', 'system_prompt', 'image_prompt', 'negative_prompt', 'is_active', 'avatar_url'];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}