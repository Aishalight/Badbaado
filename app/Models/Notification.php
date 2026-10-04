<?php

namespace App\Models;

use App\Enums\NotificationSeverity;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referral_id',
        'type',
        'severity',
        'title',
        'body',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'severity' => NotificationSeverity::class,
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Unread notifications that demand attention before routine ones.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeInterrupting(Builder $query): Builder
    {
        return $query->whereIn('severity', array_map(
            fn (NotificationSeverity $severity): string => $severity->value,
            NotificationSeverity::interrupting(),
        ));
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }
}
