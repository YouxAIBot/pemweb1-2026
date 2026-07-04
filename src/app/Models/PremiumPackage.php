<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PremiumPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_days',
        'benefits',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'benefits' => 'array',
        'is_active' => 'boolean',
        'price' => 'integer',
        'duration_days' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PremiumPayment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserPremium::class);
    }

    public function formattedPrice(): string
    {
        return 'Rp' . number_format((int) $this->price, 0, ',', '.');
    }
}
