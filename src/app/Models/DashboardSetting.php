<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_text',
        'welcome_text',
        'adventure_text',
        'language_title',
        'ability_title',
        'dashboard_title',
        'dashboard_subtitle',
        'part_button_label',
        'theme',
        'animations_enabled',
        'settings',
    ];

    protected $casts = [
        'animations_enabled' => 'boolean',
        'settings' => 'array',
    ];

    public static function current(): self
    {
        return static::query()->first() ?? new static([
            'brand_text' => 'YoLearning',
            'welcome_text' => 'Selamat datang',
            'adventure_text' => 'Ayo mulai petualanganmu',
            'language_title' => 'Pilih bahasa untuk dipelajari',
            'ability_title' => 'Seberapa jauh Anda memahami bahasa ini?',
            'dashboard_title' => 'Petualangan belajar kamu',
            'dashboard_subtitle' => 'Pilih bagian, lanjutkan progress, dan naikkan level bahasa sesuai akunmu.',
            'part_button_label' => 'Buka Bagian',
            'theme' => 'discord_dark',
            'animations_enabled' => true,
        ]);
    }
}
