<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PremiumPayment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_PAID = 'paid';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Verifikasi',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_REJECTED => 'Ditolak',
        self::STATUS_EXPIRED => 'Kedaluwarsa',
        self::STATUS_PAID => 'Dibayar via Gateway',
    ];

    protected $fillable = [
        'user_id',
        'premium_package_id',
        'payment_code',
        'payment_method',
        'amount',
        'payment_proof',
        'payment_status',
        'paid_at',
        'verified_by',
        'verified_at',
        'rejected_at',
        'note',
        'gateway',
        'gateway_order_id',
        'gateway_transaction_id',
        'gateway_status',
        'gateway_response',
        'snap_token',
        'snap_redirect_url',
    ];

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
        'gateway_response' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(PremiumPackage::class, 'premium_package_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function formattedAmount(): string
    {
        return 'Rp' . number_format((int) $this->amount, 0, ',', '.');
    }

    public function isPending(): bool
    {
        return $this->payment_status === self::STATUS_PENDING;
    }
}
