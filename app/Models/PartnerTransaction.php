<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'description',
        'request_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PromotionRequest::class, 'request_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'earning'    => 'Начисление',
            'withdrawal' => 'Заявка на вывод',
            'bonus'      => 'Бонус',
            'mlm'        => 'Сетевой бонус',
            'adjustment' => 'Корректировка',
            default      => $this->type,
        };
    }
}
