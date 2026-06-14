<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageSectionItem extends Model
{
    protected $fillable = [
        'homepage_section_id',
        'item_key',
        'label',
        'title',
        'subtitle',
        'description',
        'accent_text',
        'badge_text',
        'image_path',
        'icon_svg',
        'url',
        'sort_order',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(HomepageSection::class, 'homepage_section_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
