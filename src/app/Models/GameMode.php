<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameMode extends Model
{
    use HasFactory;

    public const STATUSES = [
        'active' => 'Aktif',
        'coming_soon' => 'Segera Hadir',
        'locked' => 'Terkunci',
    ];

    protected $fillable = [
        'key',
        'title',
        'subtitle',
        'description',
        'icon_label',
        'route_name',
        'button_label',
        'status',
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

    public function isPlayable(): bool
    {
        return $this->is_active && $this->status === 'active' && filled($this->route_name);
    }
}
