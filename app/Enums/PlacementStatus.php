<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

enum PlacementStatus: string
{
    use EnumOptions;

    case Scheduled = 'scheduled';
    case Active    = 'active';
    case Finished  = 'finished';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Запланировано',
            self::Active    => 'Активно',
            self::Finished  => 'Завершено',
            self::Cancelled => 'Отменено',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'gray',
            self::Active    => 'lime',
            self::Finished  => 'blue',
            self::Cancelled => 'rose',
        };
    }
}
