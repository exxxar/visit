<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

enum EventType: string
{
    use EnumOptions;

    case Concert    = 'concert';
    case Theater    = 'theater';
    case Party      = 'party';
    case Exhibition = 'exhibition';
    case Sport      = 'sport';
    case Kids       = 'kids';

    public function label(): string
    {
        return match ($this) {
            self::Concert    => 'Концерт',
            self::Theater    => 'Спектакль',
            self::Party      => 'Вечеринка',
            self::Exhibition => 'Выставка',
            self::Sport      => 'Спорт',
            self::Kids       => 'Детям',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Concert    => '🎵',
            self::Theater    => '🎭',
            self::Party      => '🎉',
            self::Exhibition => '🖼',
            self::Sport      => '🏋',
            self::Kids       => '👨‍👩‍👧',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Concert    => 'magenta',
            self::Theater    => 'violet',
            self::Party      => 'rose',
            self::Exhibition => 'cyan',
            self::Sport      => 'lime',
            self::Kids       => 'orange',
        };
    }
}
