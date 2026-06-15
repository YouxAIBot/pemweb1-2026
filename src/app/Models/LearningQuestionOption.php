<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningQuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_question_id',
        'option_text',
        'audio_path',
        'image_path',
        'is_correct',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'settings' => 'array',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(LearningQuestion::class, 'learning_question_id');
    }
}
