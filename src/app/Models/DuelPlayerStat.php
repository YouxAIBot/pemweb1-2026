<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuelPlayerStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'learning_language_id',
        'rating',
        'rank_label',
        'matches',
        'wins',
        'losses',
        'draws',
        'total_score',
        'best_score',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function language()
    {
        return $this->belongsTo(LearningLanguage::class, 'learning_language_id');
    }

    public static function rankFromRating(int $rating): string
    {
        return match (true) {
            $rating >= 1800 => 'Diamond',
            $rating >= 1500 => 'Platinum',
            $rating >= 1300 => 'Gold',
            $rating >= 1150 => 'Silver',
            default => 'Bronze',
        };
    }
}
