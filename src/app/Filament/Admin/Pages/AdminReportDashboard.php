<?php

namespace App\Filament\Admin\Pages;

use App\Models\AdImpression;
use App\Models\PremiumPayment;
use App\Models\QuizRoomHistory;
use App\Models\TournamentAttempt;
use App\Models\UserLevelProgress;
use App\Models\UserPremium;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AdminReportDashboard extends Page
{
    protected static ?string $navigationGroup = 'MONETIZATION';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Laporan Admin';

    protected static ?string $title = 'Laporan Admin';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.admin.pages.admin-report-dashboard';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->hasRole('super_admin') || $user->email === 'admin@admin.com'));
    }

    public function getPaymentStatsProperty(): array
    {
        return [
            'pending' => PremiumPayment::query()->where('payment_status', PremiumPayment::STATUS_PENDING)->count(),
            'approved' => PremiumPayment::query()->whereIn('payment_status', [PremiumPayment::STATUS_APPROVED, PremiumPayment::STATUS_PAID])->count(),
            'rejected' => PremiumPayment::query()->where('payment_status', PremiumPayment::STATUS_REJECTED)->count(),
            'revenue' => PremiumPayment::query()
                ->whereIn('payment_status', [PremiumPayment::STATUS_APPROVED, PremiumPayment::STATUS_PAID])
                ->sum('amount'),
        ];
    }

    public function getPremiumStatsProperty(): array
    {
        return [
            'active' => UserPremium::query()->active()->count(),
            'expired' => UserPremium::query()->where('status', 'expired')->count(),
            'expiring_soon' => UserPremium::query()
                ->where('status', 'active')
                ->whereBetween('ends_at', [now(), now()->addDays(7)])
                ->count(),
        ];
    }

    public function getActivityStatsProperty(): array
    {
        return [
            'levels_today' => UserLevelProgress::query()
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'levels_week' => UserLevelProgress::query()
                ->where('status', 'completed')
                ->where('completed_at', '>=', now()->subDays(7))
                ->count(),
            'tournament_attempts' => TournamentAttempt::query()
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'quiz_histories' => QuizRoomHistory::query()
                ->where('played_at', '>=', now()->subDays(7))
                ->count(),
        ];
    }

    public function getAdStatsProperty()
    {
        return AdImpression::query()
            ->select('placement', DB::raw('COUNT(*) as total'))
            ->groupBy('placement')
            ->orderByDesc('total')
            ->get();
    }

    public function getRecentPaymentsProperty()
    {
        return PremiumPayment::query()
            ->with(['user:id,name,email', 'package:id,name'])
            ->latest()
            ->take(8)
            ->get();
    }
}
