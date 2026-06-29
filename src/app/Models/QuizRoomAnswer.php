<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizRoomAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_room_id', 'quiz_room_question_id', 'quiz_room_option_id', 'user_id',
        'is_correct', 'score_awarded', 'answer_time_ms', 'answered_at',
    ];

    protected $casts = ['is_correct' => 'boolean', 'answered_at' => 'datetime'];

    public function room() { return $this->belongsTo(QuizRoom::class, 'quiz_room_id'); }
    public function question() { return $this->belongsTo(QuizRoomQuestion::class, 'quiz_room_question_id'); }
    public function option() { return $this->belongsTo(QuizRoomOption::class, 'quiz_room_option_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
