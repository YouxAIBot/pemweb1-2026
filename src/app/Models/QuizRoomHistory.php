<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizRoomHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_room_id',
        'user_id',
        'room_code',
        'room_title',
        'final_position',
        'final_score',
        'correct_count',
        'wrong_count',
        'played_at',
    ];

    protected $casts = [
        'played_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(QuizRoom::class, 'quiz_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
