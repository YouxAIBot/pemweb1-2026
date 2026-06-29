<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizRoomOption extends Model
{
    use HasFactory;

    protected $fillable = ['quiz_room_question_id', 'answer_text', 'image_path', 'is_correct', 'sort_order'];

    protected $casts = ['is_correct' => 'boolean'];

    public function question() { return $this->belongsTo(QuizRoomQuestion::class, 'quiz_room_question_id'); }
}
