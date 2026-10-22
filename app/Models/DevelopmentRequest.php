<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevelopmentRequest extends Model
{
    protected $fillable = [
        'user_id', 'place_id', 'service', 'title', 'amount',
        'status', 'tech_partner_id', 'requirements', 'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta'   => 'array',
    ];

    public const STATUS_NEW         = 'new';
    public const STATUS_CLAIMED     = 'claimed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_CANCELLED   = 'cancelled';

    /* справочник услуг (синхронизирован с EXTRA_SERVICES в client.blade.php) */
    public const SERVICES = [
        'app_food'    => ['label' => 'Приложение для общепита',       'icon' => '🍽', 'price' => 45000],
        'app_beauty'  => ['label' => 'Приложение для бьюти-сферы',    'icon' => '💅', 'price' => 48000],
        'app_fitness' => ['label' => 'Приложение для фитнеса',        'icon' => '💪', 'price' => 52000],
        'app_auto'    => ['label' => 'Приложение для автосервисов',   'icon' => '🔧', 'price' => 42000],
        'app_blog'    => ['label' => 'Приложение для блогеров',       'icon' => '📸', 'price' => 55000],
        'landing'     => ['label' => 'Лендинг-страница',              'icon' => '🌐', 'price' => 18000],
    ];

    public function client(): BelongsTo     { return $this->belongsTo(User::class, 'user_id'); }
    public function place(): BelongsTo      { return $this->belongsTo(Place::class); }
    public function techPartner(): BelongsTo { return $this->belongsTo(User::class, 'tech_partner_id'); }

    public function serviceLabel(): string
    {
        return self::SERVICES[$this->service]['label'] ?? $this->service;
    }

    public function serviceIcon(): string
    {
        return self::SERVICES[$this->service]['icon'] ?? '⚙️';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_NEW         => 'Новая',
            self::STATUS_CLAIMED     => 'Взята в работу',
            self::STATUS_IN_PROGRESS => 'В процессе',
            self::STATUS_COMPLETED   => 'Завершена',
            self::STATUS_CANCELLED   => 'Отменена',
            default                  => $this->status,
        };
    }
}
