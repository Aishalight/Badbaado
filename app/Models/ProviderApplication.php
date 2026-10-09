<?php

namespace App\Models;

use App\Enums\ProviderApplicationStatus;
use App\Enums\ProviderApplicationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'payload',
        'user_id',
        'hospital_id',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ProviderApplicationType::class,
            'status' => ProviderApplicationStatus::class,
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @param  Builder<ProviderApplication>  $query
     * @return Builder<ProviderApplication>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ProviderApplicationStatus::PENDING->value);
    }
}
