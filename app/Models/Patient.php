<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    protected $fillable = [
        'reference',
        'name',
        'age',
        'gender',
        'blood_group',
    ];

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }
}
