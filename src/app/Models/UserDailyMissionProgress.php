<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDailyMissionProgress extends Model
{
    use HasFactory;

    protected $table = 'user_daily_mission_progress';

    protected $fillable = [
        'user_id',
        'dashboard_daily_mission_id',
        'mission_date',
        'progress_value',
        'completed_at',
    ];

    protected $casts = [
        'mission_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(DashboardDailyMission::class, 'dashboard_daily_mission_id');
    }
}
