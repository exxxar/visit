<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

/**
 * Действие модератора (пишется в moderation_logs).
 */
enum ModerationAction: string
{
    use EnumOptions;

    case Approved = 'approved';
    case Rejected = 'rejected';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Одобрено',
            self::Rejected => 'Отклонено',
            self::Returned => 'Возвращено на правки',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Approved => 'lime',
            self::Rejected => 'rose',
            self::Returned => 'orange',
        };
    }
}
