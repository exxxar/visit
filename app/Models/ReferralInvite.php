<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralInvite extends Model
{
    protected $fillable = [
        'referrer_phone', 'referrer_id', 'referred_user_id',
        'role', 'place_id', 'status', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public const ROLE_CLIENT      = 'client';
    public const ROLE_TECH        = 'tech_partner';
    public const ROLE_AD          = 'ad_partner';

    public const STATUS_PENDING   = 'pending';    // реферер ещё не зарегистрирован
    public const STATUS_ACTIVE    = 'active';     // реферер есть, реферал активен
    public const STATUS_COMPLETED = 'completed';  // совершено целевое действие

    public function referrer(): BelongsTo { return $this->belongsTo(User::class, 'referrer_id'); }
    public function referred(): BelongsTo { return $this->belongsTo(User::class, 'referred_user_id'); }
    public function place(): BelongsTo    { return $this->belongsTo(Place::class); }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_CLIENT => 'Клиент',
            self::ROLE_TECH   => 'Тех. партнёр',
            self::ROLE_AD     => 'Рекламный партнёр',
            default           => $this->role,
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'Ожидает регистрации',
            self::STATUS_ACTIVE    => 'Активен',
            self::STATUS_COMPLETED => 'Завершён',
            default                => $this->status,
        };
    }
}
