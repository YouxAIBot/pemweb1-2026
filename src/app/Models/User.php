<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'avatar_url',
        'name',
        'email',
        'bio',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar_url) {
            return asset('storage/' . $this->avatar_url);
        }

        $hash = md5(strtolower(trim($this->email)));

        return 'https://www.gravatar.com/avatar/' . $hash . '?d=mp&r=g&s=250';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('super_admin') || $this->email === 'admin@admin.com';
    }

    public function learningProfile()
    {
        return $this->hasOne(UserLearningProfile::class);
    }

    public function levelProgress()
    {
        return $this->hasMany(UserLevelProgress::class);
    }

    public function questionProgress()
    {
        return $this->hasMany(UserQuestionProgress::class);
    }

    public function dailyMissionProgress()
    {
        return $this->hasMany(UserDailyMissionProgress::class);
    }

    public function premiumPayments()
    {
        return $this->hasMany(PremiumPayment::class);
    }

    public function premiums()
    {
        return $this->hasMany(UserPremium::class);
    }

    public function activePremium()
    {
        return $this->hasOne(UserPremium::class)->active()->latestOfMany('ends_at');
    }

    public function isPremium(): bool
    {
        if ($this->relationLoaded('activePremium')) {
            return (bool) $this->activePremium;
        }

        return $this->activePremium()->exists();
    }


    public function tournamentAttempts()
    {
        return $this->hasMany(TournamentAttempt::class);
    }

    public function duelStats()
    {
        return $this->hasMany(DuelPlayerStat::class);
    }

    public function duelSessionsAsPlayerOne()
    {
        return $this->hasMany(DuelSession::class, 'player_one_id');
    }

    public function duelSessionsAsPlayerTwo()
    {
        return $this->hasMany(DuelSession::class, 'player_two_id');
    }


}
