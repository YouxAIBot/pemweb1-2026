<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LanguageLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_language_id',
        'symbol',
        'reading',
        'example_word',
        'example_translation',
        'audio_path',
        'audio_url',
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

    public function language(): BelongsTo
    {
        return $this->belongsTo(LearningLanguage::class, 'learning_language_id');
    }

    public function publicAudioUrl(): ?string
    {
        if ($this->audio_url) {
            return $this->audio_url;
        }

        if (! $this->audio_path) {
            return null;
        }

        return Storage::disk('public')->url($this->audio_path);
    }
}
