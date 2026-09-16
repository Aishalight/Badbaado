<?php

namespace App\Models;

use Database\Factories\HospitalFactory;
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
        'location',
        'phone',
        'email',
        'level',
        'is_active',
    ];

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
}
