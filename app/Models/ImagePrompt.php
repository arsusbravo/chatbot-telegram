<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagePrompt extends Model
{
    protected $fillable = ['label', 'type', 'prompt', 'negative_prompt'];
}
