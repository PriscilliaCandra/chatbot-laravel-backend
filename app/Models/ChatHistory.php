<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'user_message',
        'ai_response'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
