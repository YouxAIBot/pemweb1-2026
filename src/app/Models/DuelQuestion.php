<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuelQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'duel_session_id',
        'question_order',
        'question_type',
        'prompt',
        'question_text',
        'options',
        'correct_answer',
        'explanation',
        'difficulty',
        'source',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(DuelSession::class, 'duel_session_id');
    }

    public function answers()
    {
        return $this->hasMany(DuelAnswer::class);
    }
}
