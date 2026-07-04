<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_level_id',
        'type',
        'instruction',
        'question_text',
        'audio_path',
        'image_path',
        'correct_answer',
        'explanation',
        'points',
        'time_limit',
        'sort_order',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(LearningLevel::class, 'learning_level_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(LearningQuestionOption::class)->orderBy('sort_order');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserQuestionProgress::class, 'learning_question_id');
    }
}
