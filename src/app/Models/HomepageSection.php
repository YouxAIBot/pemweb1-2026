<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomepageSection extends Model
{
    protected $fillable = [
        'section_key',
        'name',
        'kicker',
        'title',
        'description',
        'primary_button_label',
        'primary_button_url',
        'secondary_button_label',
        'secondary_button_url',
        'image_path',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(HomepageSectionItem::class)->orderBy('sort_order');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
