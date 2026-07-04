<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_language_id', 'owner_id', 'code', 'title', 'description', 'status',
        'current_question_order', 'started_at', 'finished_at', 'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function language() { return $this->belongsTo(LearningLanguage::class, 'learning_language_id'); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function questions() { return $this->hasMany(QuizRoomQuestion::class)->orderBy('question_order'); }
    public function members() { return $this->hasMany(QuizRoomMember::class); }
    public function answers() { return $this->hasMany(QuizRoomAnswer::class); }
    public function histories() { return $this->hasMany(QuizRoomHistory::class); }

    public function isOwner(User|int $user): bool
    {
        $id = $user instanceof User ? $user->id : $user;
        return (int) $this->owner_id === (int) $id;
    }
}
