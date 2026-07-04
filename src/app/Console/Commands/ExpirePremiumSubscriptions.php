<?php

namespace App\Console\Commands;

use App\Models\UserPremium;
use Illuminate\Console\Command;

class ExpirePremiumSubscriptions extends Command
{
    protected $signature = 'premium:expire';

    protected $description = 'Mark expired premium subscriptions and remove premium role when needed.';

    public function handle(): int
    {
        $expired = UserPremium::query()
            ->with('user')
            ->where('status', 'active')
            ->where('ends_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expired as $premium) {
            $premium->update(['status' => 'expired']);
            $count += 1;

            $user = $premium->user;

            if ($user && ! $user->activePremium()->exists() && method_exists($user, 'removeRole')) {
                try {
                    $user->removeRole('premium');
                } catch (\Throwable) {
                    // Role may not exist in older local databases.
                }
            }
        }

        $this->info($count . ' premium subscription(s) expired.');

        return self::SUCCESS;
    }
}
