<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Ad extends Model
{
    use HasFactory;

    public const PLACEMENTS = [
        'level_entry' => 'Sebelum Masuk Level',
        'level_exit' => 'Setelah Selesai Level',
    ];

    protected $fillable = [
        'title',
        'description',
        'placement',
        'video_path',
        'video_url',
        'target_url',
        'duration_seconds',
        'is_active',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActiveForPlacement($query, string $placement)
    {
        return $query
            ->where('placement', $placement)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function impressions(): HasMany
    {
        return $this->hasMany(AdImpression::class);
    }

    public function publicVideoUrl(): ?string
    {
        if ($this->video_url) {
            return $this->video_url;
        }

        if (! $this->video_path) {
            return null;
        }

        return Storage::disk('public')->url($this->video_path);
    }
}
