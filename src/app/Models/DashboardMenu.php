<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'url',
        'icon_label',
        'menu_group',
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
}
