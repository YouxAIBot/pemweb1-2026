<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLearningProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'learning_language_id',
        'current_part_id',
        'current_level_id',
        'ability_level',
        'start_level_number',
        'start_part_number',
        'total_xp',
        'streak',
        'onboarding_completed_at',
        'settings',
    ];

    protected $casts = [
        'onboarding_completed_at' => 'datetime',
        'settings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(LearningLanguage::class, 'learning_language_id');
    }

    public function currentPart(): BelongsTo
    {
        return $this->belongsTo(LearningPart::class, 'current_part_id');
    }

    public function currentLevel(): BelongsTo
    {
        return $this->belongsTo(LearningLevel::class, 'current_level_id');
    }
}
