<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizRoomQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['quiz_room_id', 'question_order', 'question_text', 'image_path', 'seconds_limit', 'points'];

    public function room() { return $this->belongsTo(QuizRoom::class, 'quiz_room_id'); }
    public function options() { return $this->hasMany(QuizRoomOption::class)->orderBy('sort_order'); }
    public function answers() { return $this->hasMany(QuizRoomAnswer::class); }
}
