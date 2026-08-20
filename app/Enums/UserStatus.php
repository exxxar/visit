<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

enum UserStatus: string
{
    use EnumOptions;

    case Active  = 'active';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Active  => 'Активен',
            self::Blocked => 'Заблокирован',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active  => 'lime',
            self::Blocked => 'rose',
        };
    }
}
