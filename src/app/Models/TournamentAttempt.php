<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'learning_language_id',
        'mode',
        'score',
        'correct_count',
        'total_questions',
        'duration_seconds',
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(LearningLanguage::class, 'learning_language_id');
    }
}
