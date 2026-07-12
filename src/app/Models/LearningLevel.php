<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningLevel extends Model
{
    use HasFactory;

    public const TYPES = [
        'multiple_choice' => 'Pilihan Ganda',
        'word_match' => 'Sambung Kata',
        'sentence_order' => 'Urutkan Kalimat',
        'listening' => 'Listening',
        'reading_story' => 'Reading Story',
        'mixed' => 'Mix',
    ];

    protected $fillable = [
        'learning_part_id',
        'title',
        'slug',
        'type',
        'short_label',
        'description',
        'sort_order',
        'xp_reward',
        'passing_score',
        'is_premium',
        'position_x',
        'position_y',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(LearningPart::class, 'learning_part_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(LearningQuestion::class)->orderBy('sort_order');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserLevelProgress::class, 'learning_level_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? str($this->type)->headline()->toString();
    }
}
