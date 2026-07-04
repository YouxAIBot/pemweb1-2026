<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'native_name',
        'flag_label',
        'description',
        'accent_color',
        'image_path',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(LearningPart::class)->orderBy('sort_order');
    }

    public function letters(): HasMany
    {
        return $this->hasMany(LanguageLetter::class)->orderBy('sort_order');
    }

    public function levels()
    {
        return $this->hasManyThrough(
            LearningLevel::class,
            LearningPart::class,
            'learning_language_id',
            'learning_part_id'
        )->orderBy('learning_levels.sort_order');
    }

    public function questions()
    {
        return LearningQuestion::query()
            ->whereHas('level.part', fn ($query) => $query->where('learning_language_id', $this->id));
    }
}
