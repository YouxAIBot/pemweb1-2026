<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    protected $fillable = [
        'site_name',
        'brand_text',
        'brand_initial',
        'brand_logo_path',
        'meta_title',
        'meta_description',
        'footer_left',
        'footer_right',
        'cursor_glow_enabled',
        'cursor_glow_size',
    ];

    protected $casts = [
        'cursor_glow_enabled' => 'boolean',
        'cursor_glow_size' => 'integer',
    ];
}
