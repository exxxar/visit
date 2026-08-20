<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

enum SubscriptionStatus: string
{
    use EnumOptions;

    case Active       = 'active';
    case Unsubscribed = 'unsubscribed';

    public function label(): string
    {
        return match ($this) {
            self::Active       => 'Подписан',
            self::Unsubscribed => 'Отписан',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active       => 'lime',
            self::Unsubscribed => 'gray',
        };
    }
}
