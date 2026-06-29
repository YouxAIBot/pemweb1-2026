<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizRoomMember extends Model
{
    use HasFactory;

    protected $fillable = ['quiz_room_id', 'user_id', 'score', 'correct_count', 'wrong_count', 'position', 'joined_at', 'finished_at'];

    protected $casts = ['joined_at' => 'datetime', 'finished_at' => 'datetime'];

    public function room() { return $this->belongsTo(QuizRoom::class, 'quiz_room_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
