<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuelSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'learning_language_id',
        'player_one_id',
        'player_two_id',
        'winner_user_id',
        'difficulty',
        'question_count',
        'seconds_per_question',
        'status',
        'settings',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function language()
    {
        return $this->belongsTo(LearningLanguage::class, 'learning_language_id');
    }

    public function playerOne()
    {
        return $this->belongsTo(User::class, 'player_one_id');
    }

    public function playerTwo()
    {
        return $this->belongsTo(User::class, 'player_two_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function players()
    {
        return $this->hasMany(DuelPlayer::class);
    }

    public function questions()
    {
        return $this->hasMany(DuelQuestion::class)->orderBy('question_order');
    }

    public function answers()
    {
        return $this->hasMany(DuelAnswer::class);
    }

    public function hasUser(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return (int) $this->player_one_id === (int) $userId
            || (int) $this->player_two_id === (int) $userId;
    }
}
