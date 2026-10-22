<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionRequest extends Model
{
    protected $fillable = [
        'user_id','place_id','service','title','amount',
        'status','partner_id','requirements','meta',
    ];

    protected $casts = ['amount' => 'decimal:2', 'meta' => 'array'];

    public const SERVICES = [
        'smm'        => ['label' => 'Ведение соцсетей',        'icon' => '📱', 'price' => 15000, 'desc' => 'Посты, сторис, работа с аудиторией'],
        'targeting'  => ['label' => 'Таргетированная реклама', 'icon' => '🎯', 'price' => 12000, 'desc' => 'Настройка и ведение рекламных кампаний'],
        'content'    => ['label' => 'Фотосъёмка и контент',    'icon' => '📸', 'price' => 9000,  'desc' => 'Профессиональная съёмка заведения и блюд'],
        'video'      => ['label' => 'Reels и видеоролики',     'icon' => '🎬', 'price' => 14000, 'desc' => 'Создание вирусных видео для соцсетей'],
        'influencer' => ['label' => 'Пост у блогера',          'icon' => '⭐', 'price' => 5000,  'desc' => 'Реклама у популярного блогера города'],
        'complex'    => ['label' => 'Продвижение под ключ',    'icon' => '🚀', 'price' => 30000, 'desc' => 'Комплексное продвижение на 3 месяца'],
    ];

    public function client(): BelongsTo  { return $this->belongsTo(User::class, 'user_id'); }
    public function place(): BelongsTo   { return $this->belongsTo(Place::class); }
    public function partner(): BelongsTo { return $this->belongsTo(User::class, 'partner_id'); }

    public function getServiceIconAttribute(): string
    {
        return self::SERVICES[$this->service]['icon'] ?? '📣';
    }
}
