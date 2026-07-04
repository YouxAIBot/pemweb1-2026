<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPremium extends Model
{
    use HasFactory;

    protected $table = 'user_premiums';

    protected $fillable = [
        'user_id',
        'premium_package_id',
        'premium_payment_id',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(PremiumPackage::class, 'premium_package_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PremiumPayment::class, 'premium_payment_id');
    }
}
