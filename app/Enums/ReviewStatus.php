<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

enum ReviewStatus: string
{
    use EnumOptions;

    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'На проверке',
            self::Approved => 'Опубликован',
            self::Rejected => 'Отклонён',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending  => 'yellow',
            self::Approved => 'lime',
            self::Rejected => 'rose',
        };
    }
}
