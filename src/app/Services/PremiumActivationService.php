<?php

namespace App\Services;

use App\Models\PremiumPayment;
use App\Models\User;
use App\Models\UserPremium;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class PremiumActivationService
{
    public function approve(PremiumPayment $payment, ?User $verifier = null, ?string $note = null): UserPremium
    {
        return DB::transaction(function () use ($payment, $verifier, $note) {
            $payment = PremiumPayment::query()
                ->with(['package', 'user'])
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->payment_status === PremiumPayment::STATUS_APPROVED) {
                return UserPremium::query()
                    ->where('premium_payment_id', $payment->id)
                    ->latest('ends_at')
                    ->firstOrFail();
            }

            $package = $payment->package;
            $user = $payment->user;
            $durationDays = max((int) ($package?->duration_days ?? 30), 1);

            $latestActive = UserPremium::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->orderByDesc('ends_at')
                ->first();

            $startsAt = $latestActive?->ends_at?->greaterThan(now())
                ? $latestActive->ends_at->copy()
                : now();

            $subscription = UserPremium::create([
                'user_id' => $user->id,
                'premium_package_id' => $package?->id,
                'premium_payment_id' => $payment->id,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addDays($durationDays),
                'status' => 'active',
            ]);

            $payment->update([
                'payment_status' => PremiumPayment::STATUS_APPROVED,
                'paid_at' => $payment->paid_at ?? now(),
                'verified_by' => $verifier?->id,
                'verified_at' => now(),
                'rejected_at' => null,
                'note' => $note ?: $payment->note,
            ]);

            if (method_exists($user, 'assignRole')) {
                Role::firstOrCreate(['name' => 'premium', 'guard_name' => 'web']);
                $user->assignRole('premium');
            }

            return $subscription;
        });
    }

    public function reject(PremiumPayment $payment, ?User $verifier = null, ?string $note = null): PremiumPayment
    {
        return DB::transaction(function () use ($payment, $verifier, $note) {
            $payment = PremiumPayment::query()->lockForUpdate()->findOrFail($payment->id);

            $payment->update([
                'payment_status' => PremiumPayment::STATUS_REJECTED,
                'verified_by' => $verifier?->id,
                'verified_at' => now(),
                'rejected_at' => now(),
                'note' => $note ?: 'Pembayaran ditolak oleh admin.',
            ]);

            return $payment;
        });
    }
}
