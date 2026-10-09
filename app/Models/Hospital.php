<?php

namespace App\Models;

use App\Enums\UserStatus;
use Database\Factories\HospitalFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospital extends Model
{
    /** @use HasFactory<HospitalFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'code',
        'kind',
        'location',
        'logo_path',
        'phone',
        'email',
        'level',
        'is_active',
    ];

    /**
     * Hospitals eligible as a referral destination in the hospital picker.
     *
     * @param  Builder<Hospital>  $query
     * @return Builder<Hospital>
     */
    public function scopeHospitals(Builder $query): Builder
    {
        return $query->where('kind', 'hospital');
    }

    /**
     * Private practices backing verified independent doctors.
     *
     * @param  Builder<Hospital>  $query
     * @return Builder<Hospital>
     */
    public function scopePractices(Builder $query): Builder
    {
        return $query->where('kind', 'practice');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function outgoingReferrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referring_hospital_id');
    }

    public function incomingReferrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'receiving_hospital_id');
    }

    /**
     * Active clinical staff a referral may be addressed to at this hospital.
     */
    public function referralStaff(): HasMany
    {
        return $this->users()
            ->where('status', UserStatus::ACTIVE)
            ->whereHas('role', fn (Builder $query) => $query->whereIn('slug', ['healthcare_worker', 'referral_coordinator']))
            ->with('role:id,slug,name')
            ->orderBy('name');
    }

    /**
     * Publicly reachable URL for the stored logo, or null when none is set.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }

    /**
     * Up to two letters used when a hospital has no logo.
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->short_name ?: $this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return strtoupper(implode('', array_map(
            static fn (string $word): string => mb_substr($word, 0, 1),
            array_slice($words, 0, 2),
        ))) ?: '?';
    }
}
