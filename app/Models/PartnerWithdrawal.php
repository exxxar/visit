<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerWithdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'payment_details',
        'admin_comment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'  => 'Ожидает',
            'approved' => 'Подтвержден',
            'rejected' => 'Отклонен',
            default    => $this->status,
        };
    }
}
