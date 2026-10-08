<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'address',
        'bio',
        'is_member',
        'points',
        'member_tier',
        'member_joined_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_member' => 'boolean',
            'points' => 'integer',
            'member_joined_at' => 'datetime',
        ];
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function getNextTierAttribute(): string
    {
        return match ($this->member_tier) {
            'bronze' => $this->points >= 1000 ? 'silver' : 'silver',
            'silver' => $this->points >= 5000 ? 'gold' : 'gold',
            'gold' => $this->points >= 10000 ? 'platinum' : 'platinum',
            'platinum' => 'platinum',
            default => 'bronze',
        };
    }

    public function getNextTierThresholdAttribute(): int
    {
        return match ($this->member_tier) {
            'bronze' => 1000,
            'silver' => 5000,
            'gold' => 10000,
            'platinum' => 10000,
            default => 1000,
        };
    }

    public function getProgressToNextTierAttribute(): int
    {
        if ($this->member_tier === 'platinum') {
            return 100;
        }

        $threshold = $this->getNextTierThresholdAttribute();
        $prev = match ($this->member_tier) {
            'bronze' => 0,
            'silver' => 1000,
            'gold' => 5000,
            default => 0,
        };

        if ($this->points >= $threshold) {
            return 100;
        }

        $range = $threshold - $prev;
        $progress = $this->points - $prev;

        return $range > 0 ? (int) floor(($progress / $range) * 100) : 0;
    }
}
