<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use App\Enums\Urgency;
use Database\Factories\ReferralFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referral extends Model
{
    /** @use HasFactory<ReferralFactory> */
    use HasFactory;

    protected $fillable = [
        'referral_number',
        'referring_hospital_id',
        'receiving_hospital_id',
        'referring_user_id',
        'coordinator_user_id',
        'patient_id',
        'status',
        'urgency',
        'is_emergency',
        'department',
        'referral_reason',
        'symptoms',
        'vitals',
        'consciousness',
        'trauma_indicator',
        'existing_conditions',
        'current_interventions',
        'notes',
        'ai_suggestion',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
            'urgency' => Urgency::class,
            'is_emergency' => 'boolean',
            'trauma_indicator' => 'boolean',
            'vitals' => 'array',
            'ai_suggestion' => 'array',
        ];
    }

    public function referringHospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'referring_hospital_id');
    }

    public function receivingHospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'receiving_hospital_id');
    }

    public function referringUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referring_user_id');
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_user_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReferralAttachment::class);
    }
}
