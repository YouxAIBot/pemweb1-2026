<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLevelProgress extends Model
{
    use HasFactory;

    protected $table = 'user_level_progress';

    protected $fillable = [
        'user_id',
        'learning_level_id',
        'status',
        'best_score',
        'attempts',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(LearningLevel::class, 'learning_level_id');
    }
}
