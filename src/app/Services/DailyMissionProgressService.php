<?php

namespace App\Services;

use App\Models\DashboardDailyMission;
use App\Models\User;
use App\Models\UserDailyMissionProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DailyMissionProgressService
{
    public function missionsForUser(User $user): Collection
    {
        if (! Schema::hasTable('dashboard_daily_missions')) {
            return $this->fallbackMissions();
        }

        $missions = DashboardDailyMission::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        if ($missions->isEmpty()) {
            return $this->fallbackMissions();
        }

        if (! Schema::hasTable('user_daily_mission_progress')) {
            return $missions->map(function (DashboardDailyMission $mission) {
                $mission->progress_value = 0;
                $mission->is_completed = false;

                return $mission;
            });
        }

        $today = now()->toDateString();

        return $missions->map(function (DashboardDailyMission $mission) use ($user, $today) {
            $progress = UserDailyMissionProgress::firstOrCreate([
                'user_id' => $user->id,
                'dashboard_daily_mission_id' => $mission->id,
                'mission_date' => $today,
            ], [
                'progress_value' => 0,
            ]);

            $target = max((int) $mission->target, 1);
            $value = min((int) $progress->progress_value, $target);

            if ($value >= $target && ! $progress->completed_at) {
                $progress->forceFill([
                    'completed_at' => now(),
                ])->save();
            }

            $mission->progress_value = $value;
            $mission->progress_record = $progress;
            $mission->is_completed = $value >= $target;

            return $mission;
        });
    }

    public function addProgress(User $user, string $missionType, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        if (! Schema::hasTable('dashboard_daily_missions') || ! Schema::hasTable('user_daily_mission_progress')) {
            return;
        }

        $missions = DashboardDailyMission::query()
            ->active()
            ->where('mission_type', $missionType)
            ->get();

        if ($missions->isEmpty() && $missionType === 'questions_answered') {
            $missions = DashboardDailyMission::query()
                ->active()
                ->where('unit_label', 'soal')
                ->get();
        }

        if ($missions->isEmpty() && $missionType === 'study_minutes') {
            $missions = DashboardDailyMission::query()
                ->active()
                ->where('unit_label', 'menit')
                ->get();
        }

        $today = now()->toDateString();

        foreach ($missions as $mission) {
            $target = max((int) $mission->target, 1);

            $progress = UserDailyMissionProgress::firstOrCreate([
                'user_id' => $user->id,
                'dashboard_daily_mission_id' => $mission->id,
                'mission_date' => $today,
            ], [
                'progress_value' => 0,
            ]);

            if ($progress->progress_value >= $target) {
                if (! $progress->completed_at) {
                    $progress->forceFill([
                        'completed_at' => now(),
                    ])->save();
                }

                continue;
            }

            $newValue = min($target, (int) $progress->progress_value + $amount);

            $progress->forceFill([
                'progress_value' => $newValue,
                'completed_at' => $newValue >= $target ? ($progress->completed_at ?? now()) : null,
            ])->save();
        }
    }

    private function fallbackMissions(): Collection
    {
        return collect([
            (object) [
                'title' => 'Kerjakan 5 soal',
                'mission_type' => 'questions_answered',
                'target' => 5,
                'progress_value' => 0,
                'unit_label' => 'soal',
                'is_completed' => false,
            ],
            (object) [
                'title' => 'Belajar 10 menit',
                'mission_type' => 'study_minutes',
                'target' => 10,
                'progress_value' => 0,
                'unit_label' => 'menit',
                'is_completed' => false,
            ],
            (object) [
                'title' => 'Kerjakan 20 soal',
                'mission_type' => 'questions_answered',
                'target' => 20,
                'progress_value' => 0,
                'unit_label' => 'soal',
                'is_completed' => false,
            ],
        ]);
    }
}
