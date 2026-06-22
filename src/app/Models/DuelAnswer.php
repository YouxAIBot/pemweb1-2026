<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuelAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'duel_session_id',
        'duel_question_id',
        'user_id',
        'selected_answer',
        'is_correct',
        'score_awarded',
        'answer_time_ms',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(DuelSession::class, 'duel_session_id');
    }

    public function question()
    {
        return $this->belongsTo(DuelQuestion::class, 'duel_question_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
