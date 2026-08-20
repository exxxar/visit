<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

/**
 * «Что вас интересует?» из lead-формы презентации.
 */
enum LeadInterest: string
{
    use EnumOptions;

    case Placement    = 'placement';
    case Partnership  = 'partnership';
    case Ads          = 'ads';
    case AddPlace     = 'add_place';
    case Other        = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Placement   => 'Размещение в путеводителе',
            self::Partnership => 'Партнерство',
            self::Ads         => 'Реклама',
            self::AddPlace    => 'Добавление предприятия',
            self::Other       => 'Другое',
        };
    }
}
