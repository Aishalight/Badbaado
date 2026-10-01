<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'hospital_id', 'role_id', 'title', 'phone', 'avatar_path', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function referralsCreated(): HasMany
    {
        return $this->hasMany(Referral::class, 'referring_user_id');
    }

    public function referralsAssigned(): HasMany
    {
        return $this->hasMany(Referral::class, 'assigned_to_user_id');
    }

    public function referralsCoordinated(): HasMany
    {
        return $this->hasMany(Referral::class, 'coordinator_user_id');
    }

    public function messagesSent(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function referralsIntended(): HasMany
    {
        return $this->hasMany(Referral::class, 'intended_user_id');
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    /**
     * Publicly reachable URL for the stored avatar, or null when none is set.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? asset('storage/'.$this->avatar_path) : null;
    }

    /**
     * Up to two letters used when a user has no photo.
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return strtoupper(implode('', array_map(
            static fn (string $word): string => mb_substr($word, 0, 1),
            array_slice($words, 0, 2),
        ))) ?: '?';
    }
}
