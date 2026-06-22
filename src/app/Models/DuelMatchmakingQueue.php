<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuelMatchmakingQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'learning_language_id',
        'duel_session_id',
        'difficulty',
        'status',
        'matched_at',
    ];

    protected $casts = [
        'matched_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function session()
    {
        return $this->belongsTo(DuelSession::class, 'duel_session_id');
    }
}
