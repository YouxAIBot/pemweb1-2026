<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuelPlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'duel_session_id',
        'user_id',
        'score',
        'correct_count',
        'wrong_count',
        'total_time_ms',
        'result',
        'joined_at',
        'finished_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(DuelSession::class, 'duel_session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
