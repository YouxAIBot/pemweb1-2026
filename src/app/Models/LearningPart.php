<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_language_id',
        'title',
        'slug',
        'subtitle',
        'description',
        'badge_text',
        'image_path',
        'level_number',
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

    public function levels(): HasMany
    {
        return $this->hasMany(LearningLevel::class)->orderBy('sort_order');
    }
}
