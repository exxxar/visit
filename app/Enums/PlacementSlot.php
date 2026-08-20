<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

/**
 * Рекламные слоты на сайте и в печати.
 */
enum PlacementSlot: string
{
    use EnumOptions;

    case Hero     = 'hero';
    case Carousel = 'carousel';
    case MapTop   = 'map_top';
    case Print    = 'print';

    public function label(): string
    {
        return match ($this) {
            self::Hero     => 'Главный экран',
            self::Carousel => 'Карусель «Сейчас выбирают»',
            self::MapTop   => 'Топ на карте',
            self::Print    => 'Печатная версия',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Hero     => 'magenta',
            self::Carousel => 'cyan',
            self::MapTop   => 'violet',
            self::Print    => 'orange',
        };
    }
}
